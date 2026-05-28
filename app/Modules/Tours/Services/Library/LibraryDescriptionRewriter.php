<?php

declare(strict_types=1);

namespace App\Modules\Tours\Services\Library;

use App\Models\Language;
use App\Models\Tenant;
use App\Services\Ai\AiModelRouter;
use Throwable;

/**
 * Cruise kütüphanesi import'unda açıklamaları SEO için ÖZGÜNLEŞTİRİR.
 *
 * Neden: aynı global katalog (MSC EURIBIA, Pire limanı…) onlarca acentaya
 * import edilirse her sitede AYNI metin çıkar → duplicate content cezası.
 * Bu servis import edilen açıklamaları "aynı anlam, farklı kelimeler"
 * mantığıyla yeniden yazar.
 *
 * Maliyet kontrolü:
 *   - Tek tek değil, BATCH (tek API call'da onlarca alan) — ~50× ucuz.
 *   - Ucuz tier (config ai.features['library.rewrite'] → simple = Haiku/4o-mini).
 *   - Best-effort: AI başarısız olursa orijinal metin korunur (import patlamaz).
 *   - Boş metinler atlanır (token yakmaz).
 */
class LibraryDescriptionRewriter
{
    /** Prompt başına yaklaşık karakter bütçesi (batch böler). */
    private const MAX_CHARS_PER_BATCH = 8000;

    public function __construct(private readonly AiModelRouter $router)
    {
    }

    /**
     * @param  array<string, string>  $texts  anahtar => orijinal metin
     * @return array<string, string>          anahtar => özgünleştirilmiş (hata → orijinal)
     */
    public function rewrite(array $texts, Language $language, ?Tenant $tenant = null): array
    {
        $payload = array_filter(
            $texts,
            static fn ($t) => is_string($t) && trim($t) !== ''
        );

        if ($payload === []) {
            return $texts;
        }

        $out = $texts; // varsayılan: orijinaller
        foreach ($this->chunk($payload) as $batch) {
            foreach ($this->rewriteBatch($batch, $language, $tenant) as $key => $value) {
                $out[$key] = $value;
            }
        }

        return $out;
    }

    /**
     * @param  array<string, string>  $batch
     * @return array<string, string>
     */
    private function rewriteBatch(array $batch, Language $language, ?Tenant $tenant): array
    {
        $langName = $language->name ?: 'Türkçe';
        $system = <<<SYS
            Sen deneyimli bir Türk turizm/SEO editörüsün. Sana JSON içinde
            {anahtar: metin} çiftleri verilecek (gemi, liman, destinasyon
            açıklamaları). Her metni AYNI BİLGİYİ ve anlamı koruyarak, FARKLI
            kelime ve cümle yapılarıyla, özgün ve SEO-uyumlu biçimde {$langName}
            dilinde YENİDEN YAZ. Kurallar:
            - Bilgi ekleme/çıkarma; uydurma yapma.
            - HTML etiketleri varsa aynen koru, sadece aralarındaki metni değiştir.
            - Anahtarları değiştirme; her gelen anahtar için bir değer döndür.
            - SADECE geçerli JSON döndür; açıklama/markdown ekleme.
            SYS;

        $user = json_encode($batch, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        try {
            $response = $this->router->generate(
                feature:  'library.rewrite',
                prompt:   (string) $user,
                system:   $system,
                tenant:   $tenant,
                metadata: ['source' => 'library.import', 'lang' => $language->code],
            );

            $decoded = $this->parseJson($response->content);
        } catch (Throwable) {
            return $batch; // sessizce orijinale düş
        }

        $result = [];
        foreach ($batch as $key => $original) {
            $result[$key] = (isset($decoded[$key]) && is_string($decoded[$key]) && trim($decoded[$key]) !== '')
                ? $decoded[$key]
                : $original;
        }

        return $result;
    }

    /**
     * Karakter bütçesine göre [key=>text] map'ini batch'lere böler.
     *
     * @param  array<string, string>  $items
     * @return array<int, array<string, string>>
     */
    private function chunk(array $items): array
    {
        $batches = [];
        $current = [];
        $size = 0;

        foreach ($items as $key => $value) {
            $len = mb_strlen($value);
            if ($current !== [] && ($size + $len) > self::MAX_CHARS_PER_BATCH) {
                $batches[] = $current;
                $current = [];
                $size = 0;
            }
            $current[$key] = $value;
            $size += $len;
        }

        if ($current !== []) {
            $batches[] = $current;
        }

        return $batches;
    }

    /**
     * AI cevabını JSON'a çevirir; ```json fence'lerini temizler.
     *
     * @return array<string, mixed>
     */
    private function parseJson(string $raw): array
    {
        $text = trim($raw);
        $text = preg_replace('/^```(?:json)?\s*/i', '', $text) ?? $text;
        $text = preg_replace('/\s*```$/', '', $text) ?? $text;

        // İlk { ... son } arası — olası önek/sonek metni at.
        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $text = substr($text, $start, $end - $start + 1);
        }

        $decoded = json_decode($text, true);

        return is_array($decoded) ? $decoded : [];
    }
}
