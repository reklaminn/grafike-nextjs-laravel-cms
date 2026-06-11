<?php

namespace App\Services\Media;

use enshrined\svgSanitize\Sanitizer;
use Illuminate\Http\UploadedFile;

/**
 * SVG upload güvenliği.
 *
 * SVG, XML tabanlı olduğu için <script>, onload= gibi JavaScript
 * taşıyabilir; sanitize edilmeden public diske yazılırsa siteyi ziyaret
 * eden herkese karşı stored-XSS olur. Bu sınıf upload edilen SVG'nin
 * içeriğini enshrined/svg-sanitize ile temizler ve dosyanın üzerine
 * geri yazar. SVG olmayan dosyalara dokunmaz.
 */
final class SvgGuard
{
    /**
     * Dosya SVG ise içeriğini sanitize edip üzerine yazar.
     *
     * @return bool false → dosya SVG ama temizlenemedi (reddedilmeli)
     */
    public static function sanitizeIfSvg(UploadedFile $file): bool
    {
        if (! self::isSvg($file)) {
            return true;
        }

        $raw = @file_get_contents($file->getRealPath());
        if ($raw === false || trim($raw) === '') {
            return false;
        }

        $sanitizer = new Sanitizer();
        $sanitizer->removeRemoteReferences(true);

        $clean = $sanitizer->sanitize($raw);

        if (! is_string($clean) || trim($clean) === '') {
            return false;
        }

        return @file_put_contents($file->getRealPath(), $clean) !== false;
    }

    private static function isSvg(UploadedFile $file): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mime      = strtolower((string) $file->getMimeType());

        return $extension === 'svg' || str_contains($mime, 'svg');
    }
}
