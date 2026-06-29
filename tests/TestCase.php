<?php

namespace Tests;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Disable Vite manifest requirement in tests
        $this->withoutVite();

        // CSRF tokens are not available in the test environment; bypass the
        // middleware so POST / PUT / DELETE requests don't return 419.
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $this->shareCentralPdoWhenNotTransacted();
    }

    /**
     * "database is locked" tuzağı: testte default ('sqlite') ve 'central'
     * bağlantıları AYNI shared in-memory DB'yi (file::memory:?cache=shared) iki
     * AYRI PDO ile görür. SQLite shared-cache tek yazıcıya izin verir → bir
     * bağlantı transaction tutarken (RefreshDatabase) diğeri yazmaya çalışınca
     * kilitlenir. Özellikle DEFAULT transaction'lı ama central'a yazan testler
     * (Admin/Tenant/Spatie oluşturanlar) aralıklı "database is locked" verirdi.
     *
     * Çözüm: central'ı transact ETMEYEN testlerde central'a default ile AYNI
     * PDO'yu ver → tek yazıcı, tek transaction, kilit yok. central'ı transact
     * EDEN testlere ($connectionsToTransact=['central']) dokunmayız; onların
     * kendi central transaction'ı vardır ve yalnızca central'a yazarlar.
     */
    private function shareCentralPdoWhenNotTransacted(): void
    {
        $default = config('database.default');
        if ($default === 'central') {
            return;
        }
        if (! array_key_exists('central', (array) config('database.connections', []))) {
            return;
        }

        // Bu testin transact ettiği bağlantılar (RefreshDatabase'le aynı mantık).
        $transacted = property_exists($this, 'connectionsToTransact')
            ? (array) $this->connectionsToTransact
            : [$default];

        if (in_array('central', $transacted, true)) {
            return; // central'ın kendi transaction'ı var — PDO'yu değiştirme.
        }

        try {
            $pdo = DB::connection($default)->getPdo();
            $central = DB::connection('central');
            $central->setPdo($pdo);
            $central->setReadPdo($pdo);
        } catch (\Throwable) {
            // PDO paylaşılamadıysa sessizce geç (ör. gerçek MySQL ortamı).
        }
    }
}
