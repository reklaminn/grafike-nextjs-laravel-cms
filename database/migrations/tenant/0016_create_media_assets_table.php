<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB: media_assets — standalone media library owner.
     *
     * Spatie MediaLibrary media MUST belong to a model (morphs('model') is
     * NOT NULL). The media library's "Dosya Yükle" / picker "Yükle" creates
     * library-owned uploads (logo vb.) that aren't tied to a page/article.
     * Each upload attaches its file to one MediaAsset row so it becomes a
     * proper Media record (appears in the grid + getUrl() works in tenancy).
     */
    public function up(): void
    {
        Schema::create('media_assets', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_assets');
    }
};
