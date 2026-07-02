<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;

/**
 * Yüklenen raster görselleri site medya ayarlarına göre yeniden boyutlandırıp
 * yeniden kodlar (sıkıştırır). SVG (vektör — SvgGuard ayrı), animasyonlu GIF ve
 * görsel olmayan dosyalar dokunulmadan bırakılır.
 *
 * Spatie addMedia() işlenmiş geçici dosyayı okuyup kütüphaneye taşır; mime/boyut
 * yeniden tespit edilir, dolayısıyla burada yalnızca dosya yolu + uzantı yeterli.
 */
class MediaImageProcessor
{
    /** Yeniden kodlanabilen (kayıplı/yeniden boyutlanabilir) raster türleri. */
    private const PROCESSABLE = ['jpg', 'jpeg', 'png', 'webp'];

    /**
     * @param  array{enabled?:bool,max_width?:int,max_height?:int,quality?:int,to_webp?:bool}  $opts
     * @return array{path:string,extension:string}|null  null → orijinali kullan
     */
    public function process(UploadedFile $file, array $opts): ?array
    {
        if (empty($opts['enabled'])) {
            return null;
        }

        $ext = strtolower($file->getClientOriginalExtension());
        if (! in_array($ext, self::PROCESSABLE, true)) {
            return null;
        }

        $maxW    = max(0, (int) ($opts['max_width'] ?? 0));
        $maxH    = max(0, (int) ($opts['max_height'] ?? 0));
        $quality = max(10, min(100, (int) ($opts['quality'] ?? 82)));
        $toWebp  = ! empty($opts['to_webp']);

        try {
            $image = ImageManager::gd()->read($file->getRealPath());

            // Yalnızca sınırlardan BÜYÜKSE küçült (oran korunur; büyütme yapmaz).
            if (($maxW > 0 && $image->width() > $maxW) || ($maxH > 0 && $image->height() > $maxH)) {
                $image->scaleDown($maxW ?: null, $maxH ?: null);
            }

            $targetExt = $toWebp ? 'webp' : ($ext === 'jpeg' ? 'jpg' : $ext);
            $encoded = match ($targetExt) {
                'webp'  => $image->toWebp($quality),
                'png'   => $image->toPng(),          // PNG kayıpsız — kalite uygulanmaz
                default => $image->toJpeg($quality), // jpg
            };

            $tmp = tempnam(sys_get_temp_dir(), 'media_');
            if ($tmp === false) {
                return null;
            }
            $tmp .= '.' . $targetExt;
            $encoded->save($tmp);

            // Güvenlik: işlenmiş dosya orijinalden büyük çıktıysa (örn. zaten
            // optimize küçük PNG) orijinali kullan — boşuna büyütme.
            if (@filesize($tmp) >= $file->getSize() && $targetExt === $ext) {
                @unlink($tmp);
                return null;
            }

            return ['path' => $tmp, 'extension' => $targetExt];
        } catch (\Throwable $e) {
            report($e);

            return null; // herhangi bir hata → orijinali bozmadan kullan
        }
    }
}
