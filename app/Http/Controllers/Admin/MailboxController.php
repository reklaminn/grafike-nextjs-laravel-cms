<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\Mailcow\MailcowClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

/**
 * Mailcow mailbox yönetimi.
 *
 * Agency admin: tüm tenant'ların mailbox'larını yönetebilir
 *               (active_tenant session'ı üzerinden).
 * Tenant admin: sadece kendi tenant'ının mailbox'larını görebilir.
 */
class MailboxController extends Controller
{
    public function __construct(private readonly MailcowClient $mailcow) {}

    // ─── Index ────────────────────────────────────────────────────────────

    public function index(Request $request): \Illuminate\View\View
    {
        $admin        = Auth::guard('admin')->user();
        $isAgency     = $admin?->isAgencyAdmin();

        // Agency admin: URL ?tenant= param ile seçim; yoksa session'dan
        if ($isAgency && $request->filled('tenant')) {
            $selectedTenant = Tenant::query()->find($request->input('tenant'));
        } else {
            [$selectedTenant] = $this->resolveTenantAndDomain();
        }

        $domain    = $selectedTenant?->mailcowDomain();
        $mailboxes = [];
        $aliases   = [];
        $error     = null;
        $quota     = null;   // domain kota özeti (F1)
        $dns       = null;   // DKIM/SPF/MX durumu (F2)

        if ($domain) {
            try {
                // Domain Mailcow'da var mı kontrol et
                $domainInfo = $this->mailcow->getDomain($domain);
                if (empty($domainInfo) || (isset($domainInfo[0]['type']) && $domainInfo[0]['type'] === 'error')) {
                    $error = "'{$domain}' Mailcow'da bulunamadı. Önce Mailcow panelinden bu domain'i ekleyin.";
                } else {
                    $all       = $this->mailcow->getMailboxes($domain);
                    // Sadece aktif mailbox'ları göster
                    $mailboxes = array_values(array_filter($all, fn ($mb) => (int) ($mb['active'] ?? 1) === 1));
                    $aliases   = $this->mailcow->getAliases($domain);

                    // ── Kota özeti — Mailcow domain objesi alan adları
                    //    sürüme göre değişebiliyor, savunmacı oku ──────────
                    $info  = array_is_list($domainInfo) ? ($domainInfo[0] ?? []) : $domainInfo;
                    $quota = [
                        'mboxes_used'  => (int) ($info['mboxes_in_domain'] ?? count($mailboxes)),
                        'mboxes_max'   => (int) ($info['max_num_mboxes_for_domain'] ?? 0),
                        'aliases_used' => (int) ($info['aliases_in_domain'] ?? count($aliases)),
                        'aliases_max'  => (int) ($info['max_num_aliases_for_domain'] ?? 0),
                        'bytes_used'   => (int) ($info['bytes_total'] ?? 0),
                        'bytes_max'    => (int) ($info['max_quota_for_domain'] ?? 0),
                    ];

                    // ── DKIM + DNS sağlığı (5 dk cache'li) ────────────────
                    try {
                        $dkim = $this->mailcow->getDkim($domain);
                    } catch (Throwable) {
                        $dkim = [];
                    }
                    $dns = app(\App\Services\Mailcow\DnsHealthChecker::class)->check($domain, $dkim);
                }
            } catch (Throwable $e) {
                $error = $e->getMessage();
            }
        }

        // Agency admin için mailcow_domain tanımlı tüm tenant'lar
        $tenantList = [];
        if ($isAgency) {
            $tenantList = Tenant::query()
                ->get()
                ->filter(fn ($t) => filled($t->mailcowDomain()))
                ->map(fn ($t) => ['id' => $t->id, 'name' => $t->name ?? $t->id, 'domain' => $t->mailcowDomain()])
                ->values()
                ->all();
        }

        return view('admin.mail.index', [
            'tenant'      => $selectedTenant,
            'domain'      => $domain,
            'mailboxes'   => $mailboxes,
            'aliases'     => $aliases,
            'error'       => $error,
            'isAgency'    => $isAgency,
            'tenantList'  => $tenantList,
            'selectedId'  => $selectedTenant?->id,
            'quota'       => $quota,
            'dns'         => $dns,
        ]);
    }

    // ─── Mailbox CRUD ─────────────────────────────────────────────────────

    public function storeMailbox(Request $request): RedirectResponse
    {
        [, $domain] = $this->resolveTenantAndDomain();
        $this->requireDomain($domain);

        $v = $request->validate([
            'local_part'          => 'required|string|max:64|regex:/^[a-zA-Z0-9._+-]+$/',
            'name'                => 'required|string|max:255',
            'password'            => 'required|string|min:8|confirmed',
            'quota'               => 'required|integer|min:100|max:102400',
            'create_smtp_profile' => 'nullable|boolean',
        ]);

        try {
            $result = $this->mailcow->createMailbox([
                'local_part' => $v['local_part'],
                'domain'     => $domain,
                'name'       => $v['name'],
                'password'   => $v['password'],
                'password2'  => $v['password'],
                'quota'      => (int) $v['quota'],
                'active'     => 1,
            ]);

            $msg = $this->extractMessage($result);
            if ($this->isError($result)) {
                return back()->with('error', 'Mailbox oluşturulamadı: ' . $msg);
            }
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        $address = "{$v['local_part']}@{$domain}";
        $notice  = "{$address} başarıyla oluşturuldu.";

        // ── SMTP profili otomatik oluştur (F3) ────────────────────────────
        // Form bildirimleri/şifre sıfırlama mailleri bu hesaptan gitsin diye
        // tenant DB'sine hazır bir SmtpProfile kaydı açılır.
        if ($request->boolean('create_smtp_profile')) {
            try {
                $mailHost = parse_url((string) \App\Models\CentralSetting::get('mailcow.url'), PHP_URL_HOST) ?: '';

                \App\Models\SmtpProfile::create([
                    'name'       => "Mailcow — {$address}",
                    'host'       => $mailHost,
                    'port'       => 587,
                    'encryption' => 'tls',
                    'username'   => $address,
                    'password'   => $v['password'],
                    'from_email' => $address,
                    'from_name'  => $v['name'],
                    'is_default' => ! \App\Models\SmtpProfile::query()->exists(),
                ]);

                $notice .= ' SMTP profili de oluşturuldu.';
            } catch (Throwable $e) {
                $notice .= ' (SMTP profili oluşturulamadı: '.$e->getMessage().')';
            }
        }

        return back()->with('success', $notice);
    }

    public function updateMailbox(Request $request): RedirectResponse
    {
        [, $domain] = $this->resolveTenantAndDomain();
        $this->requireDomain($domain);

        $v = $request->validate([
            'address' => 'required|email',
            'name'    => 'required|string|max:255',
            'quota'   => 'required|integer|min:100|max:102400',
            'active'  => 'nullable|boolean',
        ]);

        try {
            $this->mailcow->updateMailbox($v['address'], [
                'name'   => $v['name'],
                'quota'  => (int) $v['quota'],
                'active' => $request->boolean('active') ? 1 : 0,
            ]);
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "{$v['address']} güncellendi.");
    }

    public function destroyMailbox(Request $request): RedirectResponse
    {
        [, $domain] = $this->resolveTenantAndDomain();
        $this->requireDomain($domain);

        $v = $request->validate(['address' => 'required|email']);

        try {
            $this->mailcow->deleteMailbox($v['address']);
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "{$v['address']} silindi.");
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        [, $domain] = $this->resolveTenantAndDomain();
        $this->requireDomain($domain);

        $v = $request->validate([
            'address'  => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $this->mailcow->resetPassword($v['address'], $v['password']);
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "{$v['address']} şifresi güncellendi.");
    }

    // ─── Alias CRUD ───────────────────────────────────────────────────────

    public function storeAlias(Request $request): RedirectResponse
    {
        [, $domain] = $this->resolveTenantAndDomain();
        $this->requireDomain($domain);

        // Catch-all: adres "@domain" formatındadır (local part yok) —
        // domain'e gelen, hiçbir mailbox/alias'a uymayan TÜM mailleri yakalar.
        $isCatchAll = $request->boolean('catch_all');

        $v = $request->validate([
            'address' => $isCatchAll ? 'nullable' : 'required|email',
            'goto'    => 'required|string|max:1000',
        ]);

        $address = $isCatchAll ? '@'.$domain : $v['address'];

        try {
            $result = $this->mailcow->createAlias([
                'address' => $address,
                'goto'    => $v['goto'],
                'active'  => 1,
            ]);

            if ($this->isError($result)) {
                return back()->with('error', 'Alias oluşturulamadı: ' . $this->extractMessage($result));
            }
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $isCatchAll
            ? "Catch-all kuruldu: {$domain} adresine gelen eşleşmeyen tüm mailler → {$v['goto']}"
            : "Alias {$address} → {$v['goto']} oluşturuldu.");
    }

    public function destroyAlias(Request $request): RedirectResponse
    {
        [, $domain] = $this->resolveTenantAndDomain();
        $this->requireDomain($domain);

        $v = $request->validate(['id' => 'required|integer']);

        try {
            $this->mailcow->deleteAlias((int) $v['id']);
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Alias silindi.');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────

    /**
     * Aktif tenant ve Mailcow domain'ini çöz.
     *
     * Öncelik sırası:
     *   1. Request body/query'deki `selected_tenant` parametresi (POST formlarında hidden field)
     *   2. Request query'deki `tenant` parametresi (GET dropdown seçimi)
     *   3. session('active_tenant')
     *
     * @return array{0: ?Tenant, 1: ?string}
     */
    private function resolveTenantAndDomain(): array
    {
        $admin = Auth::guard('admin')->user();

        // Tenant ID çözümleme: POST hidden field → GET param → session
        $tenantId = request()->input('selected_tenant')
            ?? request()->query('tenant')
            ?? session('active_tenant');

        if (! $tenantId) {
            return [null, null];
        }

        // Tenant admin ise sadece kendi tenant'ına erişebilir
        if (! $admin?->isAgencyAdmin()) {
            $ownId = session('active_tenant');
            if ($tenantId !== $ownId) {
                abort(403, 'Başka bir tenant\'ın mail hesaplarına erişemezsiniz.');
            }
        }

        $tenant = Tenant::query()->find($tenantId);

        if (! $tenant) {
            return [null, null];
        }

        $domain = $tenant->mailcowDomain();

        return [$tenant, $domain ?: null];
    }

    private function requireDomain(?string $domain): void
    {
        if (! $domain) {
            abort(422, 'Bu tenant için Mailcow domain tanımlanmamış.');
        }
    }

    /**
     * Mailcow API yanıtından hata var mı kontrol et.
     * Mailcow hata yanıtı: [['type'=>'error', 'msg'=>'...']]
     */
    private function isError(mixed $result): bool
    {
        if (is_array($result) && isset($result[0]['type'])) {
            return $result[0]['type'] === 'error';
        }
        return false;
    }

    private function extractMessage(mixed $result): string
    {
        if (is_array($result) && isset($result[0]['msg'])) {
            $msg = $result[0]['msg'];
            return is_array($msg) ? implode(', ', $msg) : (string) $msg;
        }
        return 'Bilinmeyen yanıt';
    }
}
