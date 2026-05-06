<?php

namespace App\Http\Controllers\Admin;

use App\Console\Commands\BackupTenant as BackupTenantCommand;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TenantBackupController extends Controller
{
    // ── List backups for a tenant (JSON for the partial view) ─────────────────

    /**
     * Return backup metadata for the given tenant as an array.
     * Called by the show view via a sub-route.
     */
    public function index(Tenant $tenant): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->backupList($tenant));
    }

    // ── Trigger a new backup ──────────────────────────────────────────────────

    public function store(Tenant $tenant): RedirectResponse
    {
        $this->authorizeAgencyAdmin();

        set_time_limit(300); // backups can take a while

        try {
            $command = app(BackupTenantCommand::class);
            $path    = $command->backupTenant($tenant);

            return redirect()
                ->route('admin.tenants.show', $tenant)
                ->with('success', "Yedek oluşturuldu: ".basename($path));
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.tenants.show', $tenant)
                ->with('error', 'Yedekleme başarısız: '.$e->getMessage());
        }
    }

    // ── Download a backup file ─────────────────────────────────────────────────

    public function download(Tenant $tenant, string $filename): StreamedResponse|RedirectResponse
    {
        $this->authorizeAgencyAdmin();

        // Sanitise filename — allow only safe characters
        if (! preg_match('/^backup_[\w\-]+\.zip$/', $filename)) {
            abort(400, 'Invalid backup filename.');
        }

        $path = "backups/{$tenant->id}/{$filename}";

        if (! Storage::disk('local')->exists($path)) {
            return redirect()
                ->route('admin.tenants.show', $tenant)
                ->with('error', 'Yedek dosyası bulunamadı.');
        }

        return Storage::disk('local')->download($path, $filename);
    }

    // ── Delete a backup file ──────────────────────────────────────────────────

    public function destroy(Tenant $tenant, string $filename): RedirectResponse
    {
        $this->authorizeAgencyAdmin();

        if (! preg_match('/^backup_[\w\-]+\.zip$/', $filename)) {
            abort(400, 'Invalid backup filename.');
        }

        $path = "backups/{$tenant->id}/{$filename}";
        Storage::disk('local')->delete($path);

        return redirect()
            ->route('admin.tenants.show', $tenant)
            ->with('success', 'Yedek silindi.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Return a structured list of backups for the tenant.
     *
     * @return array<int, array{filename: string, size: string, created_at: string, download_url: string, delete_url: string}>
     */
    public function backupList(Tenant $tenant): array
    {
        $dir   = "backups/{$tenant->id}";
        $files = Storage::disk('local')->files($dir);
        rsort($files); // newest first

        return collect($files)
            ->filter(fn ($f) => str_ends_with($f, '.zip'))
            ->map(function (string $file) use ($tenant) {
                $filename = basename($file);

                // Parse timestamp from filename: backup_{id}_{Y-m-d_H-i-s}.zip
                $createdAt = '';
                if (preg_match('/(\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2})/', $filename, $m)) {
                    $createdAt = \Carbon\Carbon::createFromFormat('Y-m-d_H-i-s', $m[1])
                        ->format('d.m.Y H:i');
                }

                $bytes = Storage::disk('local')->size($file);

                return [
                    'filename'     => $filename,
                    'size'         => $this->formatBytes($bytes),
                    'created_at'   => $createdAt,
                    'download_url' => route('admin.tenants.backups.download', [$tenant, $filename]),
                    'delete_url'   => route('admin.tenants.backups.destroy',  [$tenant, $filename]),
                ];
            })
            ->values()
            ->all();
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1_073_741_824) {
            return round($bytes / 1_073_741_824, 2).' GB';
        }
        if ($bytes >= 1_048_576) {
            return round($bytes / 1_048_576, 2).' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1).' KB';
        }
        return $bytes.' B';
    }

    private function authorizeAgencyAdmin(): void
    {
        $admin = Auth::guard('admin')->user();
        if (! $admin?->isAgencyAdmin()) {
            abort(403);
        }
    }
}
