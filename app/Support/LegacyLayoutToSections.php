<?php

namespace App\Support;

use App\Models\Page;
use App\Models\SectionTemplate;
use App\Models\Theme;
use Illuminate\Support\Collection;

class LegacyLayoutToSections
{
    public static function convert(Page $page, ?Theme $theme = null): array
    {
        $layout = $page->layout_json;

        if (empty($layout) || ! is_array($layout)) {
            return FrontendSections::normalize([]);
        }

        $templates = self::resolveTemplates($page, $theme);

        $regions = [
            'header' => [],
            'body' => [],
            'footer' => [],
        ];

        foreach (array_values($layout) as $rowIndex => $row) {
            $region = self::mapRegion($row['type'] ?? 'body');
            $regions[$region][] = self::convertRow(
                $row,
                $region,
                count($regions[$region]),
                $templates
            );
        }

        return [
            'version' => 2,
            'regions' => $regions,
        ];
    }

    private static function resolveTemplates(Page $page, ?Theme $theme = null): Collection
    {
        $query = SectionTemplate::query()
            ->whereNotNull('legacy_module_key');

        $themeId = $theme?->id ?? tenancy()->tenant?->theme_id;

        if ($themeId) {
            $themedTemplates = (clone $query)
                ->where('theme_id', $themeId)
                ->orderByDesc('is_active')
                ->get()
                ->keyBy('legacy_module_key');

            if ($themedTemplates->isNotEmpty()) {
                return $themedTemplates;
            }
        }

        return $query
            ->orderByDesc('is_active')
            ->get()
            ->keyBy('legacy_module_key');
    }

    private static function mapRegion(string $type): string
    {
        return match ($type) {
            'header' => 'header',
            'footer' => 'footer',
            default => 'body',
        };
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private static function convertRow(array $row, string $region, int $rowIndex, Collection $templates): array
    {
        $columns = [];

        foreach (($row['children'] ?? []) as $columnGroup) {
            if (! is_array($columnGroup)) {
                $columnGroup = [$columnGroup];
            }

            foreach ($columnGroup as $column) {
                if (is_array($column)) {
                    $columns[] = self::convertColumn($column, $region, $rowIndex, count($columns), $templates);
                }
            }
        }

        if ($columns === []) {
            $columns[] = self::emptyColumn($region, $rowIndex, 0);
        }

        return [
            'id' => $row['id'] ?? sprintf('row_%s_%d', $region, $rowIndex + 1),
            'type' => 'row',
            'is_active' => ($row['active'] ?? true) !== false,
            'container' => $row['cont'] ?? 'container',
            'wrapper_tag' => 'section',
            'css_class' => $row['elcss'] ?? null,
            'element_id' => $row['elid'] ?? null,
            'inline_style' => $row['elstyle'] ?? null,
            'custom_attributes' => $row['elother'] ?? null,
            'columns' => $columns,
        ];
    }

    /**
     * @param  array<string, mixed>  $column
     */
    private static function convertColumn(array $column, string $region, int $rowIndex, int $columnIndex, Collection $templates): array
    {
        $responsive = [
            'xs' => self::extractWidth($column['coltype'] ?? 'col-12') ?? 12,
            'sm' => self::extractWidth($column['colsmtype'] ?? ''),
            'md' => self::extractWidth($column['colmdtype'] ?? ''),
            'lg' => self::extractWidth($column['collgtype'] ?? ''),
            'xl' => self::extractWidth($column['colxltype'] ?? ''),
        ];

        $blocks = [];

        foreach (($column['children'] ?? []) as $moduleGroup) {
            if (! is_array($moduleGroup)) {
                $moduleGroup = [$moduleGroup];
            }

            foreach ($moduleGroup as $module) {
                if (is_array($module)) {
                    $blocks[] = self::convertModule($module, $region, $rowIndex, $columnIndex, count($blocks), $templates);
                }
            }
        }

        return [
            'id' => $column['id'] ?? sprintf('col_%s_%d_%d', $region, $rowIndex + 1, $columnIndex + 1),
            'width' => $responsive['xs'] ?? 12,
            'is_active' => ($column['active'] ?? true) !== false,
            'responsive' => $responsive,
            'css_class' => $column['celcss'] ?? null,
            'element_id' => $column['celid'] ?? null,
            'inline_style' => $column['celstyle'] ?? null,
            'custom_attributes' => $column['celother'] ?? null,
            'blocks' => $blocks,
        ];
    }

    /**
     * @param  array<string, mixed>  $module
     */
    private static function convertModule(array $module, string $region, int $rowIndex, int $columnIndex, int $blockIndex, Collection $templates): array
    {
        $legacyModuleId = (string) ($module['modulid'] ?? '');
        $template = $templates->get($legacyModuleId);
        $legacyParams = self::legacyParamsToMap($module['json'] ?? []);

        if ($template instanceof SectionTemplate) {
            $content = array_replace(
                $template->default_content_json ?? [],
                self::mapLegacyParamsToContent($legacyParams, $template)
            );

            return [
                'id' => sprintf('block_%s_%d', $template->type, $blockIndex + 1),
                'type' => $template->type,
                'variation' => $template->variation,
                'render_mode' => $template->render_mode,
                'section_template_id' => $template->id,
                'component_key' => $template->component_key,
                'is_active' => ($module['active'] ?? true) !== false,
                'sort_order' => $blockIndex + 1,
                'content' => $content,
                'wrapper_tag' => 'section',
                'css_class' => null,
                'element_id' => null,
                'inline_style' => null,
                'custom_attributes' => null,
                'html_override' => null,
            ];
        }

        $moduleName = data_get(config('modules'), $legacyModuleId . '.name', 'Legacy Modül #' . $legacyModuleId);

        return [
            'id' => sprintf('legacy_%s_%d', $legacyModuleId, $blockIndex + 1),
            'type' => 'legacy-module',
            'variation' => 'module-' . $legacyModuleId,
            'render_mode' => 'html',
            'section_template_id' => null,
            'component_key' => null,
            'is_active' => ($module['active'] ?? true) !== false,
            'sort_order' => $blockIndex + 1,
            'content' => [
                'title' => $moduleName,
                'legacy_module_id' => $legacyModuleId,
            ],
            'wrapper_tag' => 'section',
            'css_class' => 'legacy-module-placeholder',
            'element_id' => null,
            'inline_style' => null,
            'custom_attributes' => null,
            'html_override' => sprintf(
                '<div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">Legacy modül eşleşmedi: <strong>%s</strong> (#%s)</div>',
                e($moduleName),
                e($legacyModuleId)
            ),
        ];
    }

    private static function emptyColumn(string $region, int $rowIndex, int $columnIndex): array
    {
        return [
            'id' => sprintf('col_%s_%d_%d', $region, $rowIndex + 1, $columnIndex + 1),
            'width' => 12,
            'is_active' => true,
            'responsive' => ['xs' => 12, 'sm' => null, 'md' => null, 'lg' => null, 'xl' => null],
            'css_class' => null,
            'element_id' => null,
            'inline_style' => null,
            'custom_attributes' => null,
            'blocks' => [],
        ];
    }

    private static function extractWidth(string $columnClass): ?int
    {
        if ($columnClass === '') {
            return null;
        }

        if (preg_match('/col(?:-[a-z]+)?-(\d{1,2})/', $columnClass, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * @param  array<int, array{name?: mixed, value?: mixed}>  $params
     * @return array<string, mixed>
     */
    private static function legacyParamsToMap(array $params): array
    {
        $output = [];

        foreach ($params as $param) {
            if (! is_array($param) || ! array_key_exists('name', $param)) {
                continue;
            }

            $output[(string) $param['name']] = $param['value'] ?? null;
        }

        return $output;
    }

    /**
     * @param  array<string, mixed>  $legacyParams
     * @return array<string, mixed>
     */
    private static function mapLegacyParamsToContent(array $legacyParams, SectionTemplate $template): array
    {
        $schema = $template->schema_json ?? [];
        $configMap = $template->legacy_config_map_json ?? [];
        $content = [];

        foreach ($legacyParams as $legacyName => $value) {
            $targetKey = $configMap[$legacyName] ?? $legacyName;
            $field = $schema[$targetKey] ?? null;
            $content[$targetKey] = self::castFieldValue($value, $field['type'] ?? null);
        }

        return $content;
    }

    private static function castFieldValue(mixed $value, ?string $type): mixed
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            'number' => is_numeric($value) ? $value + 0 : $value,
            default => $value,
        };
    }
}
