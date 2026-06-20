<?php

namespace App\Console\Commands;

use App\Support\AdminPermissions;
use Illuminate\Console\Command;

/**
 * Admin izin kayıtlarını (central permissions tablosu) AdminPermissions::groups()
 * ile senkronlar. Enforcement'ın çalışması için izinlerin var olması gerekir.
 * Deploy'da çalıştırılmalı (idempotent).
 */
class SyncAdminPermissions extends Command
{
    protected $signature = 'admin:sync-permissions';

    protected $description = 'AdminPermissions tanımlarındaki tüm izinleri central DB\'de oluşturur (idempotent)';

    public function handle(): int
    {
        AdminPermissions::ensure();
        $this->info('Admin izinleri senkronlandı ('.count(AdminPermissions::all()).' izin).');

        return self::SUCCESS;
    }
}
