<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Frontend\RobotsController;
use App\Models\SiteSetting;
use App\Notifications\MemberResetPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::all()->pluck('value', 'key');

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string|max:10000',
        ]);

        foreach ($request->settings as $key => $value) {
            SiteSetting::set($key, $value);
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Ayarlar başarıyla güncellendi.');
    }

    // ─── Business / LocalBusiness structured data ─────────────────────────────

    public function business()
    {
        $settings = SiteSetting::all()->pluck('value', 'key');

        $businessTypes = [
            'Organization'       => 'Organization (Genel)',
            'LocalBusiness'      => 'LocalBusiness (Yerel İşletme)',
            'Store'              => 'Store (Mağaza)',
            'Restaurant'         => 'Restaurant (Restoran)',
            'Hotel'              => 'Hotel (Otel)',
            'MedicalBusiness'    => 'MedicalBusiness (Sağlık)',
            'LegalService'       => 'LegalService (Hukuk)',
            'FinancialService'   => 'FinancialService (Finans)',
            'EducationalOrganization' => 'EducationalOrganization (Eğitim)',
            'AutoDealer'         => 'AutoDealer (Oto Galeri)',
        ];

        return view('admin.settings.business', compact('settings', 'businessTypes'));
    }

    public function updateBusiness(Request $request)
    {
        $request->validate([
            'business.name'                => 'nullable|string|max:255',
            'business.type'                => 'nullable|string|max:100',
            'business.address_street'      => 'nullable|string|max:500',
            'business.address_city'        => 'nullable|string|max:100',
            'business.address_postal_code' => 'nullable|string|max:20',
            'business.address_country'     => 'nullable|string|max:10',
            'business.telephone'           => 'nullable|string|max:50',
            'business.email'               => 'nullable|email|max:255',
            'business.geo_lat'             => 'nullable|numeric|between:-90,90',
            'business.geo_lng'             => 'nullable|numeric|between:-180,180',
            'business.opening_hours'       => 'nullable|string|max:2000',
            'business.organization_json_ld' => ['nullable', 'string', 'max:10000', function ($attr, $val, $fail) {
                if ($val && ! json_validate($val)) {
                    $fail('Özel JSON-LD alanı geçerli JSON formatında olmalıdır.');
                }
            }],
        ]);

        $fields = [
            'name', 'type', 'address_street', 'address_city',
            'address_postal_code', 'address_country', 'telephone', 'email',
            'geo_lat', 'geo_lng', 'opening_hours', 'organization_json_ld',
        ];

        foreach ($fields as $field) {
            SiteSetting::set("business.{$field}", $request->input("business.{$field}", ''));
        }

        // Bust API cache
        Cache::forget('api_settings');

        return redirect()
            ->route('admin.settings.business')
            ->with('success', 'İşletme bilgileri güncellendi. Yapısal veri (JSON-LD) sitede güncellenecek.');
    }

    // ─── Crawl / robots / llms ─────────────────────────────────────────────────

    public function crawl()
    {
        $settings = SiteSetting::all()->pluck('value', 'key');
        $aiBots   = RobotsController::AI_BOTS;

        return view('admin.settings.crawl', compact('settings', 'aiBots'));
    }

    public function updateCrawl(Request $request)
    {
        $request->validate([
            'crawl.allow_ai_bots'     => 'nullable|boolean',
            'crawl.crawl_delay'       => 'nullable|integer|min:0|max:60',
            'crawl.robots_custom'     => 'nullable|string|max:5000',
            'crawl.llms_description'  => 'nullable|string|max:500',
        ]);

        // General AI toggle
        SiteSetting::set('crawl.allow_ai_bots', $request->boolean('crawl.allow_ai_bots') ? '1' : '0', 'crawl');

        // Per-bot overrides
        foreach (array_keys(RobotsController::AI_BOTS) as $bot) {
            $key = 'crawl.bot_' . strtolower(str_replace(['-', ' '], '_', $bot));
            $val = $request->has("bot.{$bot}") ? '1' : '0';
            SiteSetting::set($key, $val, 'crawl');
        }

        SiteSetting::set('crawl.crawl_delay',    $request->input('crawl.crawl_delay', 0), 'crawl');
        SiteSetting::set('crawl.robots_custom',  $request->input('crawl.robots_custom', ''), 'crawl');
        SiteSetting::set('crawl.llms_description', $request->input('crawl.llms_description', ''), 'crawl');

        // Bust caches
        Cache::forget('robots_txt');
        Cache::forget('llms_txt');
        Cache::forget('llms_full_txt');

        return redirect()
            ->route('admin.settings.crawl')
            ->with('success', 'Tarama ayarları güncellendi. robots.txt ve llms.txt önbelleği temizlendi.');
    }

    // ─── Mail preview / test ──────────────────────────────────────────────────

    /**
     * POST /admin/settings/test-mail
     * Sends a sample password-reset email to the given address so the admin
     * can verify the branded template before any real member triggers it.
     */
    public function sendTestMail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email|max:255',
        ]);

        // Build a dummy notification using a fake token so the link is inert.
        $notification = new MemberResetPasswordNotification('test-preview-token-0000');

        // Use an anonymous notifiable so we don't need a real Member record.
        $fakeNotifiable = new class($request->test_email) {
            public function __construct(public string $email) {}

            public function getEmailForPasswordReset(): string
            {
                return $this->email;
            }

            public function routeNotificationFor(string $driver, mixed $notification = null): mixed
            {
                return $this->email;
            }
        };

        try {
            Notification::sendNow($fakeNotifiable, $notification);

            return redirect()
                ->route('admin.settings.index')
                ->with('success', "Test maili {$request->test_email} adresine gönderildi.");
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.settings.index')
                ->with('error', 'Mail gönderilemedi: ' . $e->getMessage());
        }
    }
}
