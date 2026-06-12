<?php

namespace App\Services\Mailcow;

use App\Models\CentralSetting;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Mailcow API istemcisi.
 *
 * Mailcow Admin API v1 — https://demo.mailcow.email/api/
 *
 * Kimlik doğrulama: X-API-Key başlığı (global Mailcow API anahtarı).
 * Tüm JSON yanıtlar doğrudan array olarak döner; hata durumunda
 * RuntimeException fırlatılır.
 */
class MailcowClient
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct(?string $baseUrl = null, ?string $apiKey = null)
    {
        $this->baseUrl = rtrim(
            $baseUrl ?? CentralSetting::get('mailcow.url') ?? '',
            '/'
        );
        $this->apiKey = $apiKey ?? CentralSetting::get('mailcow.api_key') ?? '';
    }

    // ─── Bağlantı ─────────────────────────────────────────────────────────

    /**
     * API erişilebilirliğini test et; başarılıysa true döner.
     */
    public function ping(): bool
    {
        try {
            $r = $this->get('api/v1/get/status/containers');
            return $r->successful();
        } catch (\Throwable) {
            return false;
        }
    }

    // ─── Domain ───────────────────────────────────────────────────────────

    public function getDomain(string $domain): array
    {
        return $this->get("api/v1/get/domain/{$domain}")->json() ?? [];
    }

    /**
     * @param array{
     *   domain: string,
     *   description?: string,
     *   aliases?: int,
     *   mailboxes?: int,
     *   quota?: int,
     *   active?: int,
     * } $data
     */
    public function createDomain(array $data): array
    {
        return $this->post('api/v1/add/domain', array_merge([
            'aliases'    => 5,
            'mailboxes'  => 10,
            'quota'      => 10240,
            'active'     => 1,
            'rl_value'   => 10,
            'rl_frame'   => 's',
        ], $data))->json() ?? [];
    }

    public function deleteDomain(string $domain): array
    {
        return $this->post('api/v1/delete/domain', [$domain])->json() ?? [];
    }

    // ─── Mailbox ──────────────────────────────────────────────────────────

    /**
     * Domain'e ait mailbox'ları getir.
     * Mailcow API domain filtresini güvenilir uygulamayabiliyor;
     * PHP tarafında username'e göre de filtreliyoruz.
     */
    public function getMailboxes(string $domain): array
    {
        $result = $this->get("api/v1/get/mailbox/all/{$domain}")->json();
        if (! is_array($result)) {
            return [];
        }

        // API bazen tüm domain'leri döndürüyor — client-side filtre
        return array_values(array_filter($result, function (array $mb) use ($domain): bool {
            // username: kullanici@domain.com formatında
            $username = (string) ($mb['username'] ?? '');
            return str_ends_with($username, '@' . $domain)
                || ($mb['domain'] ?? '') === $domain;
        }));
    }

    /**
     * @param array{
     *   local_part: string,
     *   domain: string,
     *   name: string,
     *   password: string,
     *   password2: string,
     *   quota: int,
     *   active?: int,
     * } $data
     */
    public function createMailbox(array $data): array
    {
        return $this->post('api/v1/add/mailbox', array_merge([
            'active'    => 1,
            'quota'     => 1024,
        ], $data))->json() ?? [];
    }

    /**
     * @param string|string[] $addresses  e-posta adresi veya dizi
     * @param array{
     *   name?: string,
     *   quota?: int,
     *   active?: int,
     * } $attrs
     */
    public function updateMailbox(string|array $addresses, array $attrs): array
    {
        return $this->post('api/v1/edit/mailbox', [
            'items' => (array) $addresses,
            'attr'  => $attrs,
        ])->json() ?? [];
    }

    /**
     * @param string|string[] $addresses
     */
    public function deleteMailbox(string|array $addresses): array
    {
        return $this->post('api/v1/delete/mailbox', (array) $addresses)->json() ?? [];
    }

    /**
     * Mailbox şifresini değiştir.
     */
    public function resetPassword(string $address, string $password): array
    {
        return $this->updateMailbox($address, [
            'password'  => $password,
            'password2' => $password,
        ]);
    }

    // ─── Alias ────────────────────────────────────────────────────────────

    /**
     * Domain'e ait alias'ları getir.
     * Mailcow API domain filtresini güvenilir uygulamayabiliyor;
     * PHP tarafında address'e göre de filtreliyoruz.
     */
    public function getAliases(string $domain): array
    {
        $result = $this->get("api/v1/get/alias/all/{$domain}")->json();
        if (! is_array($result)) {
            return [];
        }

        return array_values(array_filter($result, function (array $alias) use ($domain): bool {
            $address = (string) ($alias['address'] ?? '');
            return str_ends_with($address, '@' . $domain)
                || str_ends_with($address, '.' . $domain)
                || ($alias['domain'] ?? '') === $domain;
        }));
    }

    /**
     * @param array{
     *   address: string,   "from" adresi — ör. info@domain.com
     *   goto: string,      "to" adresi — ör. mehmet@domain.com
     *   active?: int,
     * } $data
     */
    public function createAlias(array $data): array
    {
        return $this->post('api/v1/add/alias', array_merge(['active' => 1], $data))->json() ?? [];
    }

    /**
     * @param int|int[] $ids  Mailcow alias ID (GET alias/all'dan gelir)
     */
    public function deleteAlias(int|array $ids): array
    {
        return $this->post('api/v1/delete/alias', (array) $ids)->json() ?? [];
    }

    // ─── HTTP helpers ─────────────────────────────────────────────────────

    private function get(string $path): Response
    {
        $this->assertConfigured();

        return Http::withHeaders($this->headers())
            ->timeout(10)
            ->get("{$this->baseUrl}/{$path}");
    }

    private function post(string $path, array $body = []): Response
    {
        $this->assertConfigured();

        $response = Http::withHeaders($this->headers())
            ->timeout(15)
            ->post("{$this->baseUrl}/{$path}", $body);

        return $response;
    }

    private function headers(): array
    {
        return [
            'X-API-Key' => $this->apiKey,
            'Accept'    => 'application/json',
        ];
    }

    private function assertConfigured(): void
    {
        if ($this->baseUrl === '' || $this->apiKey === '') {
            throw new RuntimeException('Mailcow URL veya API anahtarı yapılandırılmamış. Ayarlar → Mail (Mailcow) bölümünden ekleyin.');
        }
    }
}
