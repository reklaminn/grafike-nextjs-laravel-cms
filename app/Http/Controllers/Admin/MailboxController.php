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

        if ($domain) {
            try {
                $all       = $this->mailcow->getMailboxes($domain);
                // Sadece aktif mailbox'ları göster
                $mailboxes = array_values(array_filter($all, fn ($mb) => (int) ($mb['active'] ?? 1) === 1));
                $aliases   = $this->mailcow->getAliases($domain);
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
        ]);
    }

    // ─── Mailbox CRUD ─────────────────────────────────────────────────────

    public function storeMailbox(Request $request): RedirectResponse
    {
        [, $domain] = $this->resolveTenantAndDomain();
        $this->requireDomain($domain);

        $v = $request->validate([
            'local_part' => 'required|string|max:64|regex:/^[a-zA-Z0-9._+-]+$/',
            'name'       => 'required|string|max:255',
            'password'   => 'required|string|min:8|confirmed',
            'quota'      => 'required|integer|min:100|max:102400',
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

        return back()->with('success', "{$v['local_part']}@{$domain} başarıyla oluşturuldu.");
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

        $v = $request->validate([
            'address' => 'required|email',
            'goto'    => 'required|string|max:1000',
        ]);

        try {
            $result = $this->mailcow->createAlias([
                'address' => $v['address'],
                'goto'    => $v['goto'],
                'active'  => 1,
            ]);

            if ($this->isError($result)) {
                return back()->with('error', 'Alias oluşturulamadı: ' . $this->extractMessage($result));
            }
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Alias {$v['address']} → {$v['goto']} oluşturuldu.");
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
     * Agency admin → session active_tenant veya route tenant param
     * Tenant admin → kendi tenant'ı
     *
     * @return array{0: ?Tenant, 1: ?string}
     */
    private function resolveTenantAndDomain(): array
    {
        $admin = Auth::guard('admin')->user();

        if ($admin?->isAgencyAdmin()) {
            $tenantId = session('active_tenant');
            if (! $tenantId) {
                return [null, null];
            }
            $tenant = Tenant::query()->find($tenantId);
        } else {
            // Tenant admin — kendi tenant'ı
            $tenantId = session('active_tenant');
            $tenant   = $tenantId ? Tenant::query()->find($tenantId) : null;
        }

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
