<?php

namespace App\Services\Mailcow;

use App\Models\CentralSetting;
use Illuminate\Support\Facades\Cache;

/**
 * Mail domain'inin DNS sağlığını kontrol eder: MX, SPF, DKIM.
 *
 * Sonuçlar 5 dk cache'lenir — sayfa her açılışında DNS sorgusu yapılmaz.
 * DNS hatası "missing" değil "unknown" olarak işaretlenir (yanlış alarm
 * vermemek için).
 */
class DnsHealthChecker
{
    private const CACHE_TTL = 300;

    /**
     * @param array $dkimInfo MailcowClient::getDkim() çıktısı
     * @return array{mx: array, spf: array, dkim: array}
     */
    public function check(string $domain, array $dkimInfo = []): array
    {
        return Cache::remember("mail_dns_health:{$domain}", self::CACHE_TTL, function () use ($domain, $dkimInfo) {
            return [
                'mx'   => $this->checkMx($domain),
                'spf'  => $this->checkSpf($domain),
                'dkim' => $this->checkDkim($domain, $dkimInfo),
            ];
        });
    }

    public static function forget(string $domain): void
    {
        Cache::forget("mail_dns_health:{$domain}");
    }

    private function checkMx(string $domain): array
    {
        $mailHost = parse_url((string) CentralSetting::get('mailcow.url'), PHP_URL_HOST) ?: '';

        $records = $this->dns($domain, DNS_MX);
        if ($records === null) {
            return ['status' => 'unknown', 'found' => null, 'expected' => $mailHost];
        }

        $targets = array_map(fn ($r) => strtolower(rtrim((string) ($r['target'] ?? ''), '.')), $records);
        $targets = array_values(array_filter($targets));

        if ($targets === []) {
            return ['status' => 'missing', 'found' => null, 'expected' => $mailHost];
        }

        // MX hedefi mail sunucusuna işaret ediyor mu? (tam eşleşme şart değil —
        // mail.domain.com gibi CNAME'li kurulumlar olabilir; bilgi olarak göster)
        $ok = $mailHost === '' || in_array(strtolower($mailHost), $targets, true);

        return [
            'status'   => $ok ? 'ok' : 'partial',
            'found'    => implode(', ', $targets),
            'expected' => $mailHost,
        ];
    }

    private function checkSpf(string $domain): array
    {
        $expected = 'v=spf1 mx ~all';

        $records = $this->dns($domain, DNS_TXT);
        if ($records === null) {
            return ['status' => 'unknown', 'found' => null, 'expected' => $expected];
        }

        foreach ($records as $record) {
            $txt = (string) ($record['txt'] ?? '');
            if (str_starts_with(strtolower(trim($txt)), 'v=spf1')) {
                return ['status' => 'ok', 'found' => $txt, 'expected' => $expected];
            }
        }

        return ['status' => 'missing', 'found' => null, 'expected' => $expected];
    }

    private function checkDkim(string $domain, array $dkimInfo): array
    {
        $selector = (string) ($dkimInfo['dkim_selector'] ?? 'dkim');
        $expected = (string) ($dkimInfo['dkim_txt'] ?? '');
        $pubkey   = (string) ($dkimInfo['pubkey'] ?? '');

        // Mailcow'da DKIM anahtarı üretilmemiş
        if ($expected === '' && $pubkey === '') {
            return ['status' => 'not_generated', 'found' => null, 'expected' => null, 'selector' => $selector];
        }

        $host    = "{$selector}._domainkey.{$domain}";
        $records = $this->dns($host, DNS_TXT);

        if ($records === null) {
            return ['status' => 'unknown', 'found' => null, 'expected' => $expected, 'selector' => $selector];
        }

        foreach ($records as $record) {
            $txt = (string) ($record['txt'] ?? '');
            // DNS TXT parçalara bölünmüş olabilir; pubkey'in ilk 40 karakteri yeterli kanıt
            $needle = $pubkey !== '' ? substr(preg_replace('/\s+/', '', $pubkey), 0, 40) : null;
            if ($needle && str_contains(preg_replace('/\s+/', '', $txt), $needle)) {
                return ['status' => 'ok', 'found' => $txt, 'expected' => $expected, 'selector' => $selector];
            }
        }

        return ['status' => 'missing', 'found' => null, 'expected' => $expected, 'selector' => $selector];
    }

    /** @return array<int, array>|null null → DNS sorgusu başarısız (unknown) */
    private function dns(string $host, int $type): ?array
    {
        try {
            $records = @dns_get_record($host, $type);

            return $records === false ? null : $records;
        } catch (\Throwable) {
            return null;
        }
    }
}
