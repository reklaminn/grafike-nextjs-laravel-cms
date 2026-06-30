<?php

namespace App\Services\Ai;

use App\Models\Tenant;
use App\Services\Ai\Exceptions\AiProviderException;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

/**
 * Page-level AI assistant (Madde 3b).
 *
 * Takes the page's current blocks + a natural-language instruction and
 * returns a CHANGE PLAN — a list of operations the editor can preview as a
 * diff and apply only after the admin confirms. Nothing is persisted here.
 *
 * Three operation types:
 *   - edit    : rewrite a block's text fields        → {op, ref, name, changes:[{key,old,new}]}
 *   - reorder : new ordering of the body blocks      → {op, order:[ref…], order_named:[{ref,name}]}
 *   - remove  : drop a block                         → {op, ref, name}
 *
 * Blocks are referenced by an opaque integer "ref" assigned by the caller
 * (the editor flattens its body blocks 1..N). The model never sees — and
 * cannot invent — real block ids; refs outside the supplied set are dropped.
 *
 * Field safety: only the text-editable subset of each block (per
 * AiBlockEditor) is exposed to and merged back from the model, so urls,
 * media ids, colors, etc. are never mutated.
 */
class AiPageAssistant
{
    /** Hard cap on blocks fed to the model — keeps the prompt bounded. */
    private const MAX_BLOCKS = 40;

    public function __construct(
        private readonly AiModelRouter $router,
        private readonly AiBlockEditor $blockEditor,
    ) {
    }

    /**
     * @param  array<int, array{ref:int,type?:string,name?:string,content?:array}>  $blocks
     * @return array{summary:string, operations:array<int,array<string,mixed>>, warnings:array<int,string>}
     */
    public function plan(string $instruction, array $blocks, ?Tenant $tenant = null): array
    {
        $instruction = trim($instruction);
        if ($instruction === '') {
            throw new InvalidArgumentException('Talimat boş olamaz.');
        }

        // Snapshot each block's original + editable text subset, keyed by ref.
        $byRef        = [];
        $catalogLines = [];
        foreach (array_slice($blocks, 0, self::MAX_BLOCKS) as $block) {
            $ref = (int) ($block['ref'] ?? 0);
            if ($ref <= 0 || isset($byRef[$ref])) {
                continue;
            }
            $content  = is_array($block['content'] ?? null) ? $block['content'] : [];
            $editable = $this->blockEditor->editableSubset($content, null);
            $name     = trim((string) ($block['name'] ?? '')) ?: (string) ($block['type'] ?? 'blok');

            $byRef[$ref] = ['content' => $content, 'editable' => $editable, 'name' => $name];

            $fields = $editable === []
                ? '(metin alanı yok)'
                : json_encode($editable, JSON_UNESCAPED_UNICODE);
            $catalogLines[] = "#{$ref} \"".str_replace('"', "'", $name)."\" (".($block['type'] ?? '?').") alanlar={$fields}";
        }

        if ($byRef === []) {
            throw new RuntimeException('Düzenlenecek blok bulunamadı.');
        }

        $system = $this->systemPrompt();
        $user   = $this->userPrompt($instruction, implode("\n", $catalogLines));

        try {
            $response = $this->router->generate(
                feature:  'page.assist',
                prompt:   $user,
                system:   $system,
                tenant:   $tenant,
                metadata: ['source' => 'page.assistant'],
            );
        } catch (AiProviderException $e) {
            throw new RuntimeException('AI sağlayıcısı yanıt veremedi: '.$e->getMessage(), 0, $e);
        }

        $parsed = $this->parseJson($response->content);

        return $this->buildPlan($parsed, $byRef);
    }

    // ────────────────────────────────────────────────────────────────────

    /**
     * Validate + resolve the model's raw plan against the known refs.
     *
     * @param  array<string,mixed>  $parsed
     * @param  array<int,array{content:array,editable:array,name:string}>  $byRef
     * @return array{summary:string, operations:array<int,array<string,mixed>>, warnings:array<int,string>}
     */
    private function buildPlan(array $parsed, array $byRef): array
    {
        $summary    = trim((string) ($parsed['summary'] ?? ''));
        $rawOps     = is_array($parsed['operations'] ?? null) ? $parsed['operations'] : [];
        $operations = [];
        $warnings   = [];
        $knownRefs  = array_keys($byRef);

        foreach ($rawOps as $op) {
            if (! is_array($op)) {
                continue;
            }
            $type = strtolower((string) ($op['op'] ?? ''));

            if ($type === 'edit') {
                $ref = (int) ($op['ref'] ?? 0);
                if (! isset($byRef[$ref])) {
                    $warnings[] = "Bilinmeyen blok (#{$ref}) için düzenleme atlandı.";
                    continue;
                }
                $fields = is_array($op['fields'] ?? null) ? $op['fields'] : [];
                $orig   = $byRef[$ref];
                $merged = $this->blockEditor->mergeFields($orig['content'], $fields, null);

                $changes = [];
                foreach ($orig['editable'] as $key => $oldVal) {
                    if (! array_key_exists($key, $merged) || ! is_string($merged[$key])) {
                        continue;
                    }
                    if ((string) $merged[$key] !== (string) $oldVal) {
                        $changes[] = [
                            'key' => (string) $key,
                            'old' => (string) $oldVal,
                            'new' => (string) $merged[$key],
                        ];
                    }
                }
                if ($changes !== []) {
                    $operations[] = ['op' => 'edit', 'ref' => $ref, 'name' => $orig['name'], 'changes' => $changes];
                }
            } elseif ($type === 'reorder') {
                $order = array_values(array_filter(
                    array_map('intval', is_array($op['order'] ?? null) ? $op['order'] : []),
                    fn ($r) => isset($byRef[$r]),
                ));
                $order = array_values(array_unique($order));
                // Append any refs the model forgot so reorder is a full permutation.
                foreach ($knownRefs as $r) {
                    if (! in_array($r, $order, true)) {
                        $order[] = $r;
                    }
                }
                if (count($order) >= 2) {
                    $operations[] = [
                        'op'          => 'reorder',
                        'order'       => $order,
                        'order_named' => array_map(fn ($r) => ['ref' => $r, 'name' => $byRef[$r]['name']], $order),
                    ];
                }
            } elseif ($type === 'remove') {
                $ref = (int) ($op['ref'] ?? 0);
                if (! isset($byRef[$ref])) {
                    $warnings[] = "Bilinmeyen blok (#{$ref}) için kaldırma atlandı.";
                    continue;
                }
                $operations[] = ['op' => 'remove', 'ref' => $ref, 'name' => $byRef[$ref]['name']];
            }
        }

        if ($operations === []) {
            $warnings[] = 'AI uygulanabilir bir değişiklik önermedi. Talimatı daha açıklayıcı yazıp tekrar deneyin.';
        }

        return [
            'summary'    => $summary !== '' ? $summary : 'Önerilen değişiklikler hazır.',
            'operations' => $operations,
            'warnings'   => array_values($warnings),
        ];
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
Sen bir web sayfası düzenleme asistanısın. Sana sayfadaki blokların listesi (#ref, ad, tip, düzenlenebilir metin alanları) ve bir kullanıcı talimatı verilir.

Görevin: talimatı yerine getirecek bir DEĞİŞİKLİK PLANI üretmek. Üç tür işlem var:
- edit    → bir blokun metin alanlarını yeniden yaz: {"op":"edit","ref":<#>,"fields":{"alan":"yeni değer", ...}}
- reorder → blokların sırasını değiştir: {"op":"reorder","order":[<ref'lerin yeni sırası, eksiksiz>]}
- remove  → bir bloğu kaldır: {"op":"remove","ref":<#>}

KATI KURALLAR:
- SADECE verilen #ref değerlerini kullan; var olmayan ref ASLA kullanma.
- edit'te yalnızca o blokun "alanlar" listesindeki anahtarları kullan; yeni anahtar ekleme.
- URL, link, renk, id, görsel alanlarına DOKUNMA (zaten "alanlar"da yer almazlar).
- Değişiklik gerektirmeyen blok için işlem ÜRETME. Yalnızca talimatın gerektirdiği kadarını yap.
- reorder kullanacaksan "order" tüm blokların yeni sırası olmalı (eksiksiz permütasyon).
- İçerikler akıcı, doğal, somut olsun; blokun mevcut diliyle aynı dilde yaz. Lorem ipsum yazma.

ÇIKTI: Sadece geçerli JSON. Kod bloğu YOK, ön söz YOK.
Şema:
{"summary":"yapılan değişikliklerin kısa özeti","operations":[ ... ]}
PROMPT;
    }

    private function userPrompt(string $instruction, string $catalog): string
    {
        return "Sayfadaki bloklar:\n{$catalog}\n\nKullanıcı talimatı:\n{$instruction}\n\nDeğişiklik planını üret.";
    }

    /**
     * @return array<string, mixed>
     */
    private function parseJson(string $raw): array
    {
        $json = trim($raw);

        if (preg_match('/```(?:json)?\s*(\{.*\})\s*```/s', $json, $m)) {
            $json = $m[1];
        }
        if (! Str::startsWith($json, '{') && preg_match('/(\{.*\})/s', $json, $m)) {
            $json = $m[1];
        }

        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            throw new RuntimeException('AI yanıtı geçerli JSON değil: '.mb_substr($raw, 0, 200));
        }

        return $decoded;
    }
}
