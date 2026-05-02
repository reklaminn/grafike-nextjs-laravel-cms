<?php

namespace App\Services\SectionTemplate;

use App\Models\Menu;
use App\Models\SectionTemplate;
use App\Models\SiteSetting;

class SectionTemplateRenderer
{
    private array $systemTokens = [];

    public function render(SectionTemplate $template, ?array $overrideContent = null): string
    {
        $html = $template->html_template ?? '';
        if ($html === '') {
            return '';
        }

        $content = array_merge(
            $template->default_content_json ?? [],
            $overrideContent ?? []
        );

        $system = $this->getSystemTokens();
        $menus  = $this->buildMenuTokens($template->schema_json ?? []);

        // {{{key}}} — raw (menu tokens, html fields)
        $html = preg_replace_callback('/\{\{\{([a-z0-9_]+)\}\}\}/', function ($m) use ($content, $system, $menus) {
            $key = $m[1];
            return $menus[$key] ?? $content[$key] ?? $system[$key] ?? '';
        }, $html);

        // {{key}} — escaped
        $html = preg_replace_callback('/\{\{([a-z0-9_]+)\}\}/', function ($m) use ($content, $system) {
            $key = $m[1];
            $val = $content[$key] ?? $system[$key] ?? '';
            return htmlspecialchars((string) $val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }, $html);

        return $html;
    }

    private function getSystemTokens(): array
    {
        if ($this->systemTokens) {
            return $this->systemTokens;
        }

        $settings = SiteSetting::query()
            ->get(['key', 'value'])
            ->pluck('value', 'key')
            ->all();

        $this->systemTokens = array_merge([
            'site_name'      => config('app.name'),
            'site_domain'    => request()->getHost(),
            'theme_slug'     => '',
            'logo_url'       => '',
            'favicon_url'    => '',
            'phone'          => '',
            'email'          => '',
            'address'        => '',
            'whatsapp_number'=> '',
            'working_hours'  => '',
            'tax_id'         => '',
            'footer_text'    => '',
        ], $settings);

        return $this->systemTokens;
    }

    private function buildMenuTokens(array $schema = []): array
    {
        $tokens = [];

        Menu::query()
            ->where('is_active', true)
            ->with([
                'items' => fn ($query) => $query
                    ->whereNull('parent_id')
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->with([
                        'children' => fn ($childQuery) => $childQuery
                            ->where('is_active', true)
                            ->orderBy('sort_order'),
                    ]),
            ])
            ->get(['id', 'name', 'slug', 'location'])
            ->each(function (Menu $menu) use (&$tokens, $schema) {
                $keys = collect([$menu->location, $menu->slug])
                    ->filter()
                    ->unique()
                    ->map(fn (string $k) => $this->normalizeKey($k));

                foreach ($keys as $key) {
                    $templateSet = $this->menuTemplateForKey($schema, $key);
                    $itemsHtml = $this->renderMenuItems($menu->items ?? collect(), $templateSet);
                    $menuHtml = $templateSet['wrapper_template'] ?? '<ul>{{{items_html}}}</ul>';
                    $menuHtml = $this->renderMenuTemplate($menuHtml, [
                        'items_html' => $itemsHtml,
                        'menu_name' => $menu->name,
                        'menu_key' => $key,
                    ]);

                    $tokens["menu_{$key}_html"]       = $menuHtml;
                    $tokens["menu_{$key}_items_html"] = $itemsHtml;
                    $tokens["menu_{$key}_name"]       = $menu->name;
                }
            });

        return $tokens;
    }

    private function renderMenuItems(iterable $items, array $templateSet, bool $isChild = false): string
    {
        return collect($items)
            ->map(function ($item) use ($templateSet, $isChild) {
                $children = $item->children ?? collect();
                $childrenHtml = $this->renderMenuItems($children, $templateSet, true);
                $hasChildren = $childrenHtml !== '';

                $template = $this->selectMenuItemTemplate($templateSet, $hasChildren, $isChild);

                return $this->renderMenuTemplate($template, [
                    'id' => (string) ($item->id ?? ''),
                    'title' => (string) ($item->title ?? ''),
                    'url' => (string) ($item->url ?: '#'),
                    'target' => (string) ($item->target ?? ''),
                    'target_attr' => $item->target ? ' target="'.htmlspecialchars((string) $item->target, ENT_QUOTES, 'UTF-8').'"' : '',
                    'children_html' => $childrenHtml,
                    'active_class' => '',
                    'has_children_class' => $hasChildren ? 'has-children' : '',
                ]);
            })
            ->implode('');
    }

    private function selectMenuItemTemplate(array $templateSet, bool $hasChildren, bool $isChild): string
    {
        if ($hasChildren) {
            return $isChild
                ? ($templateSet['child_item_with_children_template'] ?? $templateSet['item_with_children_template'] ?? $this->defaultMenuItemWithChildrenTemplate())
                : ($templateSet['item_with_children_template'] ?? $this->defaultMenuItemWithChildrenTemplate());
        }

        return $isChild
            ? ($templateSet['child_item_template'] ?? $templateSet['item_template'] ?? $this->defaultMenuItemTemplate())
            : ($templateSet['item_template'] ?? $this->defaultMenuItemTemplate());
    }

    private function menuTemplateForKey(array $schema, string $key): array
    {
        $templates = $schema['menu_templates'] ?? $schema['menuTemplates'] ?? [];

        if (! is_array($templates)) {
            return [];
        }

        $templateSet = $templates[$key] ?? [];

        return is_array($templateSet) ? $templateSet : [];
    }

    private function renderMenuTemplate(string $template, array $values): string
    {
        $html = preg_replace_callback('/\{\{\{([a-z0-9_]+)\}\}\}/', function ($m) use ($values) {
            return (string) ($values[$m[1]] ?? '');
        }, $template);

        return preg_replace_callback('/\{\{([a-z0-9_]+)\}\}/', function ($m) use ($values) {
            return htmlspecialchars((string) ($values[$m[1]] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }, $html);
    }

    private function defaultMenuItemTemplate(): string
    {
        return '<li><a href="{{url}}"{{{target_attr}}}>{{title}}</a></li>';
    }

    private function defaultMenuItemWithChildrenTemplate(): string
    {
        return '<li><a href="{{url}}"{{{target_attr}}}>{{title}}</a><ul>{{{children_html}}}</ul></li>';
    }

    private function normalizeKey(string $key): string
    {
        return trim((string) str($key)->lower()->replaceMatches('/[^a-z0-9_]+/', '_'), '_');
    }
}
