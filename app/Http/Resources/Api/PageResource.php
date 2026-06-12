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
use Illuminate\Support\Facades\Auth;

class PageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Page $page */
        $page = $this->resource;
        // Theme is resolved from the current tenant's metadata (stancl/tenancy).
        $tenant = tenancy()->tenant ?? null;
        $theme  = $tenant?->theme_id ? Theme::find($tenant->theme_id) : null;

        $rawSections    = $this->resolveRenderableSections($page, $theme);
        $member         = Auth::guard('member')->user();
        $memberLoggedIn = $member !== null;
        $memberGroupId  = $member?->group_id;

        // ── Page-level group restriction ──────────────────────────────────────
        $allowedGroupIds   = array_filter((array) ($page->allowed_group_ids ?? []));
        $isGroupRestricted = false;
        $requiredGroupNames = [];

        if (! empty($allowedGroupIds)) {
            if (! $memberLoggedIn || ! in_array($memberGroupId, $allowedGroupIds, false)) {
                $isGroupRestricted = true;
                // Resolve group names for the frontend banner
                $requiredGroupNames = \App\Models\MemberGroup::whereIn('id', $allowedGroupIds)
                    ->pluck('name')
                    ->all();
            }
        }

        // ── Block-level filtering ──────────────────────────────────────────────
        // Remove blocks that are member-only (visitor not logged in)
        // OR restricted to groups the current member doesn't belong to.
        [$filteredSections, $memberOnlyRemoved] = FrontendSections::filterBlocks(
            $rawSections,
            function (array $block) use ($memberLoggedIn, $memberGroupId): bool {
                // Strip if member-only and visitor is not logged in
                if (! empty($block['is_member_only']) && ! $memberLoggedIn) {
                    return true;
                }
                // Strip if block has group restriction and member's group isn't in the list
                $blockGroups = array_filter((array) ($block['allowed_group_ids'] ?? []));
                if (! empty($blockGroups)) {
                    if (! $memberLoggedIn || ! in_array($memberGroupId, $blockGroups, false)) {
                        return true;
                    }
                }
                return false;
            },
        );

        $sections     = $this->enrichSections(FrontendSections::flattenBlocks($filteredSections));
        $regionLayout = $this->enrichRegionBlocks($filteredSections);
        $themeSlug    = $theme?->slug ?: 'porto-furniture';
        $breadcrumbs  = $this->buildBreadcrumbs($page);

        $isPasswordProtected = (bool) $page->is_password_protected;
        $isLocked            = $isPasswordProtected && ! in_array(
            $page->id,
            session('unlocked_pages', []),
            true,
        );

        // When group-restricted, strip all sections (same pattern as password lock)
        $hideSections = $isLocked || $isGroupRestricted;

        return [
            'page' => [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'excerpt' => null,
                'featured_image' => $page->getFirstMediaUrl('cover'),
                'template' => $page->template ?: $page->page_template,
                'layout' => $page->layout_json ?? [],
                'sections' => $hideSections ? [] : $sections,
                'region_version' => $regionLayout['version'] ?? 2,
                'regions' => $hideSections ? [] : ($regionLayout['regions'] ?? []),
                'language' => $page->language?->code,
                'breadcrumbs' => $breadcrumbs,
                'is_password_protected'   => $isPasswordProtected,
                'is_locked'               => $isLocked,
                'has_member_only_content' => $memberOnlyRemoved > 0,
                'is_group_restricted'     => $isGroupRestricted,
                'required_group_names'    => $requiredGroupNames,
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
