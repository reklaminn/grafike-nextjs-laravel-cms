<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * Sistem geneli merkezi ayarlar (central DB).
 *
 * Superadmin panelinden yönetilen, tüm tenant'lar için geçerli global
 * konfigürasyon değerlerini saklar. AI provider API anahtarları gibi
 * hassas veriler type='encrypted' ile şifreli tutulur.
 *
 * Kullanım:
 *   CentralSetting::get('ai.anthropic_api_key')          → düz/şifresiz okur
 *   CentralSetting::set('ai.anthropic_api_key', 'sk-…', 'encrypted')
 */
class CentralSetting extends Model
{
    protected $connection = 'central';
    protected $table      = 'central_settings';
    protected $primaryKey = 'key';

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = ['key', 'value', 'type', 'group'];

    // ─── Static helpers ──────────────────────────────────────────────────────

    /**
     * Ayar değerini oku. Şifreli kayıtlar otomatik çözülür.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $row = static::query()->find($key);
        if (! $row) {
            return $default;
        }

        if ($row->type === 'encrypted' && $row->value !== null && $row->value !== '') {
            try {
                return Crypt::decryptString($row->value);
            } catch (\Throwable) {
                return $default;
            }
        }

        return $row->value ?? $default;
    }

    /**
     * Ayar değerini kaydet veya güncelle.
     * type='encrypted' → değer şifrelenerek saklanır.
     * Boş string → değeri siler (null olarak kaydeder).
     */
    public static function set(string $key, string $value, string $type = 'string', string $group = 'general'): void
    {
        if ($type === 'encrypted' && $value !== '') {
            $storeValue = Crypt::encryptString($value);
        } else {
            $storeValue = $value !== '' ? $value : null;
        }

        static::query()->updateOrInsert(
            ['key' => $key],
            [
                'value'      => $storeValue,
                'type'       => $type,
                'group'      => $group,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    /**
     * Ayarı tamamen sil.
     */
    public static function remove(string $key): void
    {
        static::query()->where('key', $key)->delete();
    }

    // ─── AI Keys helpers ─────────────────────────────────────────────────────

    /**
     * Tüm AI ayarlarını config-override için hazır dizi olarak döndürür.
     * Değer boşsa null döner → config override yapılmaz, .env fallback çalışır.
     */
    public static function aiConfig(): array
    {
        return [
            'default_provider' => static::get('ai.default_provider'),
            'anthropic'        => static::get('ai.anthropic_api_key')  ?: null,
            'openrouter'       => static::get('ai.openrouter_api_key') ?: null,
            'openai'           => static::get('ai.openai_api_key')     ?: null,
        ];
    }

    /**
     * Belirtilen provider için API key'in kayıtlı olup olmadığını kontrol eder
     * (şifre çözmeden — sadece kayıt var mı diye bakar).
     */
    public static function hasAiKey(string $provider): bool
    {
        return static::query()
            ->where('key', "ai.{$provider}_api_key")
            ->whereNotNull('value')
            ->where('value', '!=', '')
            ->exists();
    }
}
