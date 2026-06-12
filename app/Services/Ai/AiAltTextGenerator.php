<?php

namespace App\Services\Ai;

use App\Models\Tenant;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Medya kütüphanesindeki bir görsel için AI ile alt yazısı üretir.
 *
 * Vision destekli ucuz tier (media.alt → Haiku / 4o-mini) kullanılır.
 * Üretilen metin SEO + erişilebilirlik için kısa ve betimleyicidir;
 * media.custom_properties.alt_text alanına yazılması çağırana aittir.
 */
class AiAltTextGenerator
{
    private const SYSTEM = <<<PROMPT
Sen bir web erişilebilirlik ve SEO uzmanısın. Sana verilen görsel için
Türkçe, kısa ve betimleyici bir alt yazısı (alt text) üret.

Kurallar:
- En fazla 125 karakter
- Görselde NE göründüğünü betimle; "resim", "fotoğraf", "görsel" gibi
  kelimelerle BAŞLAMA
- Dekoratif süsleme yapma, anahtar kelime doldurma (keyword stuffing) yapma
- SADECE alt yazısını döndür — tırnak, açıklama, ek metin yok
PROMPT;

    /** Vision API'ye gönderilecek en büyük dosya (büyükler atlanır) */
    private const MAX_BYTES = 4 * 1024 * 1024;

    private const SUPPORTED_MIMES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    public function __construct(private readonly AiModelRouter $router) {}

    /**
     * @throws \RuntimeException görsel okunamaz/desteklenmez ise
     */
    public function generate(Media $media, ?Tenant $tenant = null): string
    {
        $mime = strtolower((string) $media->mime_type);

        if (! in_array($mime, self::SUPPORTED_MIMES, true)) {
            throw new \RuntimeException("Desteklenmeyen görsel türü: {$mime}");
        }

        if ((int) $media->size > self::MAX_BYTES) {
            throw new \RuntimeException('Görsel 4MB üzerinde — alt yazısı için küçültülmüş kopya gerekir.');
        }

        $path = $media->getPath();
        $raw  = @file_get_contents($path);

        if ($raw === false) {
            throw new \RuntimeException('Görsel dosyası okunamadı.');
        }

        $context = array_filter([
            $media->name ? "Dosya adı: {$media->name}" : null,
            $media->collection_name ? "Koleksiyon: {$media->collection_name}" : null,
        ]);

        $prompt = "Bu görsel için alt yazısı üret."
            .($context ? "\nBağlam: ".implode(' · ', $context) : '');

        $response = $this->router->generate(
            feature:       'media.alt',
            prompt:        $prompt,
            system:        self::SYSTEM,
            tenant:        $tenant,
            metadata:      ['media_id' => $media->id],
            imageBase64:   base64_encode($raw),
            imageMimeType: $mime,
        );

        $alt = trim(trim((string) $response->content), '"\'');

        return mb_strimwidth($alt, 0, 255, '');
    }
}
