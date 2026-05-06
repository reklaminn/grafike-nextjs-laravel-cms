<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('page_revisions')) {
            return;
        }

        Schema::create('page_revisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('page_id')->index();
            $table->unsignedBigInteger('admin_id')->nullable(); // central DB ref — no FK
            $table->json('snapshot');                           // sections_json + layout_json
            $table->string('reason', 255)->nullable();
            $table->timestamp('created_at')->nullable()->index();

            $table->foreign('page_id')
                ->references('id')
                ->on('pages')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_revisions');
    }
};
