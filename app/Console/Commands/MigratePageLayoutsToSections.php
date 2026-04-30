<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Models\Theme;
use App\Support\LegacyLayoutToSections;
use Illuminate\Console\Command;

class MigratePageLayoutsToSections extends Command
{
    protected $signature = 'cms:migrate-page-layouts
        {--page= : Sadece belirli page ID}
        {--theme= : Eşleme için tema slug}
        {--force : sections_json dolu olsa da tekrar yaz}
        {--dry-run : Yazmadan sadece özet göster}';

    protected $description = 'Convert legacy layout_json pages into sections_json v2 for the Next.js builder';

    public function handle(): int
    {
        $query = Page::query()->whereNotNull('layout_json');

        if ($pageId = $this->option('page')) {
            $query->whereKey($pageId);
        }

        if (! $this->option('force')) {
            $query->where(function ($builder) {
                $builder->whereNull('sections_json')->orWhere('sections_json', '[]');
            });
        }

        $pages = $query->orderBy('id')->get();

        if ($pages->isEmpty()) {
            $this->warn('Dönüştürülecek sayfa bulunamadı.');

            return self::SUCCESS;
        }

        $theme = $this->resolveTheme();
        $rows = [];

        foreach ($pages as $page) {
            $sections = LegacyLayoutToSections::convert($page, $theme);
            $blockCount = count(\App\Support\FrontendSections::flattenBlocks($sections));

            $rows[] = [
                $page->id,
                $page->title,
                count($sections['regions']['header'] ?? []),
                count($sections['regions']['body'] ?? []),
                count($sections['regions']['footer'] ?? []),
                $blockCount,
            ];

            if (! $this->option('dry-run')) {
                $page->forceFill([
                    'sections_json' => $sections,
                ])->save();
            }
        }

        $this->table(
            ['Page ID', 'Başlık', 'Header', 'Body', 'Footer', 'Block'],
            $rows
        );

        if ($this->option('dry-run')) {
            $this->comment('Dry run tamamlandı. sections_json yazılmadı.');
        } else {
            $this->info(sprintf('%d sayfa dönüştürüldü.', count($rows)));
        }

        return self::SUCCESS;
    }

    private function resolveTheme(): ?Theme
    {
        $themeSlug = $this->option('theme');

        if (is_string($themeSlug) && $themeSlug !== '') {
            return Theme::query()->where('slug', $themeSlug)->first();
        }

        return Theme::query()->active()->orderBy('id')->first()
            ?? Theme::query()->orderBy('id')->first();
    }
}
