<?php

namespace App\Http\Resources\Api;

use App\Models\Page;
use App\Models\SectionTemplate;
use App\Models\Theme;
use App\Services\Seo\StructuredDataGenerator;
use App\Support\FrontendSections;
use App\Support\LegacyLayoutToSections;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Page $page */
        $page = $this->resource;
        // Theme is resolved from the current tenant's metadata (stancl/tenancy).
        $tenant = tenancy()->tenant ?? null;
        $theme  = $tenant?->theme_id ? Theme::find($tenant->theme_id) : null;

        $rawSections  = $this->resolveRenderableSections($page, $theme);
        $sections     = $this->enrichSections(FrontendSections::flattenBlocks($rawSections));
        $regionLayout = $this->enrichRegionBlocks($rawSections);
        $themeSlug    = $theme?->slug ?: 'porto-furniture';
        $breadcrumbs  = $this->buildBreadcrumbs($page);

        return [
            'page' => [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'excerpt' => null,
                'featured_image' => $page->getFirstMediaUrl('cover'),
                'template' => $page->template ?: $page->page_template,
                'layout' => $page->layout_json ?? [],
                'sections' => $sections,
                'region_version' => $regionLayout['version'] ?? 2,
                'regions' => $regionLayout['regions'] ?? [],
                'language' => $page->language?->code,
                'breadcrumbs' => $breadcrumbs,
            ],
            'seo' => [
                'title'           => $page->seo?->meta_title       ?: $page->title,
                'description'     => $page->seo?->meta_description ?: '',
                'keywords'        => $page->seo?->meta_keywords     ?: '',
                'canonical'       => $page->seo?->canonical_url     ?: url($page->slug ?: '/'),
                'noindex'         => (bool) ($page->seo?->is_noindex ?? false),
                'og_image'        => $page->seo?->og_image          ?: $page->getFirstMediaUrl('cover') ?: null,
                'og_type'         => $page->seo?->og_type           ?: 'website',
                'hreflang_tags'   => $page->seo?->hreflang_tags     ?: [],
                'structured_data' => $this->resolveStructuredData($page),
                'schema_type'     => $page->seo?->schema_type       ?: null,
            ],
            'breadcrumbs' => $breadcrumbs,
            'theme' => [
                'slug' => $themeSlug,
            ],
        ];
    }

    /**
     * Return the stored structured_data, or auto-generate a FAQPage schema
     * if the page has FAQ-type sections and no structured_data is stored.
     */
    protected function resolveStructuredData(Page $page): ?array
    {
        // Admin has already set explicit structured data — use it
        if (! empty($page->seo?->structured_data)) {
            return $page->seo->structured_data;
        }

        // Auto-detect FAQ blocks from sections_json
        $sectionsJson = $page->sections_json ?? [];
        if (! empty($sectionsJson)) {
            $faqSchema = app(StructuredDataGenerator::class)->detectFaqBlocks($sectionsJson);
            if ($faqSchema !== null) {
                return $faqSchema;
            }
        }

        return null;
    }

    protected function resolveRenderableSections(Page $page, ?Theme $theme): array
    {
        $sections = $page->sections_json;

        if (! empty($sections)) {
            $flattened = FrontendSections::flattenBlocks($sections);

            if (! empty($flattened)) {
                return $sections;
            }
        }

        if (! empty($page->layout_json)) {
            return LegacyLayoutToSections::convert($page, $theme);
        }

        return [];
    }

    protected function buildBreadcrumbs(Page $page): array
    {
        $breadcrumbs = [];
        $current = $page;

        while ($current) {
            array_unshift($breadcrumbs, [
                'title' => $current->title,
                'slug' => $current->slug,
                'url' => '/'.$current->slug,
            ]);

            $current = $current->parent;
        }

        return $breadcrumbs;
    }

    protected function enrichSections(array $sections): array
    {
        $templateIds = FrontendSections::collectTemplateIds($sections);

        if ($templateIds->isEmpty()) {
            return $sections;
        }

        $templates = SectionTemplate::query()
            ->whereIn('id', $templateIds)
            ->get()
            ->keyBy('id');

        return collect($sections)
            ->map(function (array $section) use ($templates) {
                $template = $templates->get($section['section_template_id'] ?? null);

                if (! $template) {
                    return $section;
                }

                $section['template_name'] = $template->name;
                $section['type'] = $template->type;
                $section['variation'] = $template->variation;
                $section['render_mode'] = $template->render_mode;
                $section['html_template'] = $template->html_template;
                $section['component_key'] = $template->component_key;
                $section['schema'] = $template->schema_json ?? [];

                return $section;
            })
            ->all();
    }

    protected function enrichRegionBlocks(array $sections): array
    {
        $templateIds = FrontendSections::collectTemplateIds($sections);

        if ($templateIds->isEmpty()) {
            return FrontendSections::normalize($sections);
        }

        $templates = SectionTemplate::query()
            ->whereIn('id', $templateIds)
            ->get()
            ->keyBy('id');

        return FrontendSections::mapBlocks($sections, function (array $block) use ($templates) {
            $template = $templates->get($block['section_template_id'] ?? null);

            if (! $template) {
                return $block;
            }

            $block['template_name'] = $template->name;
            $block['type'] = $template->type;
            $block['variation'] = $template->variation;
            $block['render_mode'] = $template->render_mode;
            $block['html_template'] = $template->html_template;
            $block['component_key'] = $template->component_key;
            $block['schema'] = $template->schema_json ?? [];

            return $block;
        });
    }
}
