<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Language;
use App\Models\Page;
use App\Models\SiteSetting;
use App\Services\Ai\AiModelRouter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

/**
 * Site Kurulum Sihirbazı — AI destekli 3 adımlı sayfa üretim akışı.
 *
 *  Adım 1: Firma bilgileri → SiteSetting'e kaydeder
 *  Adım 2: AI sayfa önerisi → tenant seçer
 *  Adım 3: Her seçilen sayfa için generate-page çağrılır (frontend döngüsü)
 *
 * Onboarding flag: site.setup_completed = 1  (complete endpoint'inden set edilir)
 */
class SiteWizardController extends Controller
{
    /** GET /admin/site-wizard */
    public function index(): View
    {
        $setupCompleted = (bool) SiteSetting::get('site.setup_completed');

        return view('admin.wizard.index', [
            'setupCompleted' => $setupCompleted,
            'companyName'    => SiteSetting::get('site.company_name') ?? SiteSetting::get('business.name') ?? '',
            'sector'         => SiteSetting::get('site.sector') ?? SiteSetting::get('business.type') ?? '',
            'description'    => SiteSetting::get('site.description') ?? '',
            'targetAudience' => SiteSetting::get('site.target_audience') ?? '',
            'city'           => SiteSetting::get('business.address_city') ?? '',
            'phone'          => SiteSetting::get('contact.phone') ?? '',
            'email'          => SiteSetting::get('contact.email') ?? '',
            'cssFramework'   => SiteSetting::get('site.css_framework') ?? 'Bootstrap 5',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────

    /**
     * POST /admin/site-wizard/save-company
     * Adım 1: firma bilgilerini SiteSetting'e kaydeder.
     */
    public function saveCompany(Request $request): JsonResponse
    {
        $v = $request->validate([
            'company_name'    => 'required|string|max:200',
            'sector'          => 'required|string|max:100',
            'description'     => 'nullable|string|max:1000',
            'target_audience' => 'nullable|string|max:500',
            'city'            => 'nullable|string|max:100',
            'phone'           => 'nullable|string|max:30',
            'email'           => 'nullable|email|max:200',
            'css_framework'   => 'nullable|string|max:50',
        ]);

        SiteSetting::set('site.company_name',    $v['company_name'],               'general');
        SiteSetting::set('business.name',        $v['company_name'],               'business');
        SiteSetting::set('site.sector',          $v['sector'],                     'general');
        SiteSetting::set('business.type',        $v['sector'],                     'business');
        SiteSetting::set('site.description',     $v['description']     ?? '',      'general');
        SiteSetting::set('site.target_audience', $v['target_audience'] ?? '',      'general');
        SiteSetting::set('business.address_city',$v['city']            ?? '',      'business');
        SiteSetting::set('contact.phone',        $v['phone']           ?? '',      'contact');
        SiteSetting::set('contact.email',        $v['email']           ?? '',      'contact');
        SiteSetting::set('site.css_framework',   $v['css_framework']   ?? 'Bootstrap 5', 'general');

        return response()->json(['ok' => true]);
    }

    // ─────────────────────────────────────────────────────────────────────

    /**
     * POST /admin/site-wizard/suggest-pages
     * Adım 2: AI, sektör + firma bilgisine göre 5-8 sayfa önerir.
     */
    public function suggestPages(Request $request, AiModelRouter $router): JsonResponse
    {
        $v = $request->validate([
            'company_name'    => 'required|string|max:200',
            'sector'          => 'required|string|max:100',
            'description'     => 'nullable|string|max:500',
            'target_audience' => 'nullable|string|max:300',
        ]);

        $tenant = tenancy()->initialized ? tenant() : null;

        $system = <<<'PROMPT'
Sen bir web sitesi mimarısın. Verilen firma bilgilerine göre o firmaya uygun web sayfalarını öneriyorsun.

KURALLAR:
- Sektöre özgü, gerçekçi ve standart sayfalar öner.
  Örnekler: klinik → "Hizmetler", "Doktorlarımız", "Randevu Al" | restoran → "Menü", "Rezervasyon" | hukuk → "Uzmanlık Alanları", "Avukat Ekibi"
- 5 ile 8 arasında sayfa öner; fazla veya az olmasın.
- Ana Sayfa (slug: "" boş string) her zaman ilk sıraya gelsin.
- Slug: küçük harf, tire ayraçlı, Türkçe karakterleri ASCII'ye çevir, max 60 karakter.
- Her sayfanın "purpose" alanı kısa ve somut olsun (max 2 cümle, içeriği net tarif etsin).
- ÇIKTI: Sadece geçerli JSON array. Kod bloğu YOK, yorum YOK, başka metin YOK.
- Format: [{"title":"...", "slug":"...", "purpose":"..."}]
PROMPT;

        $parts = [
            "Firma Adı: {$v['company_name']}",
            "Sektör: {$v['sector']}",
        ];
        if (!empty($v['description']))    $parts[] = "Firma Açıklaması: {$v['description']}";
        if (!empty($v['target_audience'])) $parts[] = "Hedef Kitle: {$v['target_audience']}";
        $parts[] = "\nBu firmaya uygun 5-8 web sayfasını JSON array olarak öner.";

        try {
            $response = $router->generate(
                feature:  'page.create',
                prompt:   implode("\n", $parts),
                system:   $system,
                tenant:   $tenant,
                metadata: ['source' => 'wizard.suggest'],
            );
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }

        $pages = $this->parseJsonArray($response->content);
        if ($pages === null) {
            return response()->json(['ok' => false, 'message' => 'AI geçerli sayfa listesi üretemedi. Tekrar deneyin.'], 500);
        }

        // Normalize: ensure slug/title/purpose keys exist
        $pages = array_values(array_filter(array_map(function ($p) {
            if (!is_array($p) || empty($p['title'])) return null;
            return [
                'title'   => (string) ($p['title'] ?? ''),
                'slug'    => (string) ($p['slug']  ?? ''),
                'purpose' => (string) ($p['purpose'] ?? ''),
            ];
        }, $pages)));

        return response()->json(['ok' => true, 'pages' => $pages]);
    }

    // ─────────────────────────────────────────────────────────────────────

    /**
     * POST /admin/site-wizard/generate-page
     * Adım 3: Boş taslak sayfa oluşturur (AI içerik üretimi YOK).
     *
     * Sayfa içeriği (bloklar) sayfa düzenleme ekranındaki
     * "AI ile Sayfayı Oluştur" butonu ile sonradan doldurulur.
     */
    public function generatePage(Request $request): JsonResponse
    {
        $v = $request->validate([
            'title'       => 'required|string|max:200',
            'slug'        => 'nullable|string|max:100',
            'language_id' => 'nullable|integer',
            'sort_order'  => 'nullable|integer',
            'parent_id'   => 'nullable|integer',
        ]);

        // slug='' → Ana Sayfa anlamına gelir. Zaten varsa yeniden oluşturma.
        $requestedSlug = $v['slug'] ?? '';
        if ($requestedSlug === '') {
            $existing = Page::where('slug', '')->first();
            if ($existing) {
                // Sadece sort_order'ı güncelle, yeni sayfa açma
                if (isset($v['sort_order'])) {
                    $existing->update(['sort_order' => (int) $v['sort_order']]);
                }
                return response()->json([
                    'ok'       => true,
                    'page_id'  => $existing->id,
                    'title'    => $existing->title,
                    'slug'     => $existing->slug,
                    'skipped'  => true,
                    'edit_url' => route('admin.pages.edit', $existing, false),
                ]);
            }
        }

        // language_id verilmemişse varsayılan aktif dili kullan
        $languageId = $v['language_id'] ?? null;
        if (! $languageId) {
            $languageId = Language::where('is_active', true)
                ->orderBy('sort_order')
                ->value('id');
        }

        $finalSlug = $requestedSlug !== ''
            ? $this->uniqueSlug($requestedSlug, $languageId)
            : $this->uniqueSlug($v['title'], $languageId);

        try {
            $page = Page::create([
                'title'         => $v['title'],
                'slug'          => $finalSlug,
                'language_id'   => $languageId,
                'parent_id'     => $v['parent_id'] ?? null,
                'status'        => 'draft',
                'sections_json' => [],
                'show_in_menu'  => false,
                'sort_order'    => $v['sort_order'] ?? 0,
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            // Race condition veya daha önce oluşturulmuş sayfa — mevcut kaydı bul.
            // Sırasıyla dene: finalSlug+langId → baseSlug+langId → title+langId → title (herhangi dil)
            $baseSlug = Str::slug($requestedSlug ?: $v['title'], '-', 'tr');

            $existing = Page::where('language_id', $languageId)
                ->where(function ($q) use ($finalSlug, $baseSlug, $v) {
                    $q->where('slug', $finalSlug)
                      ->orWhere('slug', $baseSlug)
                      ->orWhere('title', $v['title']);
                })
                ->orderByRaw("CASE WHEN slug = ? THEN 0 WHEN slug = ? THEN 1 ELSE 2 END", [$finalSlug, $baseSlug])
                ->first();

            // language_id fark etmeksizin slug veya başlıkla bul
            $existing ??= Page::where('slug', $finalSlug)->first();
            $existing ??= Page::where('slug', $baseSlug)->first();
            $existing ??= Page::where('title', $v['title'])->first();

            if ($existing) {
                return response()->json([
                    'ok'      => true,
                    'page_id' => $existing->id,
                    'title'   => $existing->title,
                    'slug'    => $existing->slug,
                    'skipped' => true,
                    'edit_url'=> route('admin.pages.edit', $existing, false),
                ]);
            }

            // Gerçekten bulunamadı → hata döndür, rastgele slug üretme
            return response()->json(['ok' => false, 'message' => 'Sayfa oluşturulamadı: başka bir istek aynı anda tamamladı.'], 500);
        }

        return response()->json([
            'ok'      => true,
            'page_id' => $page->id,
            'title'   => $page->title,
            'slug'    => $page->slug,
            'edit_url'=> route('admin.pages.edit', $page, false),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────

    /**
     * POST /admin/site-wizard/generate-article
     * Adım 3: Boş taslak yazı oluşturur (3 boşluk indent = yazı tipi).
     * parent_id burada page_id olarak kullanılır (yazının ait olduğu sayfa).
     */
    public function generateArticle(Request $request): JsonResponse
    {
        $v = $request->validate([
            'title'       => 'required|string|max:200',
            'slug'        => 'nullable|string|max:100',
            'language_id' => 'nullable|integer',
            'sort_order'  => 'nullable|integer',
            'parent_id'   => 'nullable|integer',  // yazının bağlı olduğu sayfa
        ]);

        // Yazının bağlı olacağı sayfa zorunlu (articles.page_id NOT NULL)
        if (empty($v['parent_id'])) {
            return response()->json([
                'ok'      => false,
                'message' => 'Üst sayfa bulunamadı — önce bağlı olduğu sayfayı oluşturun.',
            ], 422);
        }

        $languageId = $v['language_id'] ?? null;
        if (! $languageId) {
            $languageId = Language::where('is_active', true)
                ->orderBy('sort_order')
                ->value('id');
        }

        $slug = $this->uniqueArticleSlug($v['slug'] ?: $v['title']);

        try {
            $article = Article::create([
                'title'       => $v['title'],
                'slug'        => $slug,
                'language_id' => $languageId,
                'page_id'     => $v['parent_id'],
                'status'      => 'draft',
                'sort_order'  => $v['sort_order'] ?? 0,
                'body'        => '',
                'content_json'=> null,
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            // Aynı slug ile yazı zaten var → mevcut kaydı döndür
            $existing = Article::where('slug', $slug)->where('language_id', $languageId)->first()
                ?? Article::where('title', $v['title'])->where('page_id', $v['parent_id'])->first();
            if ($existing) {
                return response()->json([
                    'ok'      => true,
                    'page_id' => null,
                    'title'   => $existing->title,
                    'slug'    => $existing->slug,
                    'skipped' => true,
                    'edit_url'=> route('admin.articles.edit', $existing, false),
                ]);
            }
            $article = Article::create([
                'title'       => $v['title'],
                'slug'        => $this->uniqueArticleSlug($slug . '-' . substr(uniqid(), -4)),
                'language_id' => $languageId,
                'page_id'     => $v['parent_id'],
                'status'      => 'draft',
                'sort_order'  => $v['sort_order'] ?? 0,
                'body'        => '',
                'content_json'=> null,
            ]);
        }

        return response()->json([
            'ok'      => true,
            'page_id' => null,   // yazılar parent tracker'ı etkilemesin
            'title'   => $article->title,
            'slug'    => $article->slug,
            'edit_url'=> route('admin.articles.edit', $article, false),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * POST /admin/site-wizard/complete
     * Onboarding flag'ini set eder; banner + sidebar linki kaybolur.
     */
    public function complete(): JsonResponse
    {
        SiteSetting::set('site.setup_completed', '1', 'general');

        return response()->json(['ok' => true]);
    }

    /**
     * POST /admin/site-wizard/reset
     * setup_completed flag'ini temizler; sihirbazı sıfırdan başlatır.
     * Superadmin istediği zaman çağırabilir.
     */
    public function reset(): JsonResponse
    {
        SiteSetting::set('site.setup_completed', '', 'general');

        return response()->json(['ok' => true, 'redirect' => route('admin.wizard.index', [], false)]);
    }

    // ─────────────────────────────────────────────────────────────────────

    private function parseJsonArray(string $raw): ?array
    {
        $json = trim($raw);

        if (preg_match('/```(?:json)?\s*(\[.*\])\s*```/s', $json, $m)) {
            $json = $m[1];
        }
        if (!str_starts_with($json, '[') && preg_match('/(\[.*\])/s', $json, $m)) {
            $json = $m[1];
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function uniqueArticleSlug(string $base): string
    {
        $base = Str::slug($base ?: 'yazi', '-', 'tr');
        if ($base === '') $base = 'yazi';

        if (! Article::query()->where('slug', $base)->exists()) {
            return $base;
        }
        for ($i = 2; $i < 100; $i++) {
            $candidate = $base . '-' . $i;
            if (! Article::query()->where('slug', $candidate)->exists()) {
                return $candidate;
            }
        }
        return $base . '-' . substr(uniqid(), -4);
    }

    private function uniqueSlug(string $base, ?int $languageId = null): string
    {
        $base = Str::slug($base ?: 'sayfa', '-', 'tr');
        if ($base === '') {
            $base = 'sayfa';
        }

        // DB constraint: (slug, language_id) composite unique — aynı şekilde kontrol et
        $exists = function (string $slug) use ($languageId): bool {
            $q = Page::query()->where('slug', $slug);
            if ($languageId) {
                $q->where('language_id', $languageId);
            }
            return $q->exists();
        };

        if (! $exists($base)) {
            return $base;
        }

        for ($i = 2; $i < 100; $i++) {
            $candidate = $base . '-' . $i;
            if (! $exists($candidate)) {
                return $candidate;
            }
        }

        return $base . '-' . substr(uniqid(), -4);
    }
}
