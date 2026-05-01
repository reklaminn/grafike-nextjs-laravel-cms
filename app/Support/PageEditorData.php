<?php

namespace App\Support;

use App\Models\Page;
use Illuminate\Support\Collection;

final class PageEditorData
{
    public function __construct(
        public readonly ?Page $page,
        public readonly Collection $availableTemplates,
    ) {}

    public static function for(?Page $page, Collection $availableTemplates): self
    {
        return new self($page, $availableTemplates);
    }

    public function initialRegions(): array
    {
        $oldSectionsJson = old('sections_json');

        if ($oldSectionsJson) {
            $decoded = json_decode((string) $oldSectionsJson, true);

            if (is_array($decoded)) {
                return FrontendSections::normalize($decoded);
            }
        }

        return FrontendSections::normalize($this->page?->sections_json ?? []);
    }

    public function availableTemplatesPayload(): array
    {
        return $this->availableTemplates
            ->map(fn ($template) => [
                'id' => $template->id,
                'name' => $template->name,
                'type' => $template->type,
                'variation' => $template->variation,
                'render_mode' => $template->render_mode,
                'component_key' => $template->component_key,
                'html_template' => $template->html_template,
                'schema' => $template->schema_json ?? [],
                'default_content' => $template->default_content_json ?? [],
                'preview_image_url' => $template->getFirstMediaUrl('preview_image') ?: null,
            ])
            ->values()
            ->all();
    }

    public function frontendSectionEditorPayload(): array
    {
        return [
            'initialRegions' => $this->initialRegions(),
            'availableTemplates' => $this->availableTemplatesPayload(),
        ];
    }

    public function hasSectionsJson(): bool
    {
        return ! empty($this->page?->sections_json);
    }

    public function hasLayoutJson(): bool
    {
        return ! empty($this->page?->layout_json);
    }

    public function activeBuilder(): string
    {
        if (old('sections_json')) {
            return 'frontend';
        }

        if (old('layout_json')) {
            return 'legacy';
        }

        // Create mode: no page yet → always default to frontend builder
        if ($this->page === null) {
            return 'frontend';
        }

        if ($this->hasSectionsJson()) {
            return 'frontend';
        }

        if ($this->hasLayoutJson()) {
            return 'legacy';
        }

        // Pages always belong to the current tenant — default to frontend builder.
        return 'frontend';
    }

    public function showBuilderToggle(): bool
    {
        if ($this->page === null) {
            return false;
        }

        if ($this->hasLayoutJson() && ! $this->hasSectionsJson()) {
            return true;
        }

        if ($this->hasLayoutJson() && $this->hasSectionsJson()) {
            return true;
        }

        // In tenant context every page belongs to the current tenant,
        // so the "orphan page" case no longer applies → no toggle needed.
        return false;
    }

    public function shouldRenderFrontendEditor(): bool
    {
        // Create mode: always render the frontend editor so users can add blocks immediately
        if ($this->page === null) {
            return true;
        }

        // Pages are always edited within tenant context — always render the frontend editor.
        return true;
    }
}
