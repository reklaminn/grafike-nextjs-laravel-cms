<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Standalone media-library asset (tenant DB).
 *
 * Spatie media must belong to a model; this is the owner for library uploads
 * that aren't tied to a page/article (logo, serbest görseller). One file per
 * asset via the 'library' collection. Lives on the default (tenant) connection
 * alongside the Spatie `media` table.
 */
class MediaAsset extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = ['name'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('library')->singleFile();
    }
}
