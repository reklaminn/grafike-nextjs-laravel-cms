<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ThemeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $assets = $this->decodeJsonInput('assets_json') ?? [];
        $cssPaths = $this->splitLines($this->input('css_paths'));
        $jsPaths = $this->splitLines($this->input('js_paths'));

        if ($cssPaths !== [] || $jsPaths !== []) {
            $assets['css'] = $cssPaths;
            $assets['js'] = $jsPaths;
        }

        $this->merge([
            'assets_json' => $assets,
            'tokens_json' => $this->decodeJsonInput('tokens_json'),
            'settings_schema_json' => $this->decodeJsonInput('settings_schema_json'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        $themeId = $this->route('theme')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('central.themes', 'slug')->ignore($themeId)],
            'engine' => ['required', Rule::in(['nextjs-basic-html', 'nextjs-component', 'legacy-blade'])],
            'description' => ['nullable', 'string'],
            'preview_image' => ['nullable', 'string', 'max:500'],
            'assets_json' => ['nullable', 'array'],
            'css_files' => ['nullable', 'array'],
            'css_files.*' => ['file', 'max:5120', 'extensions:css'],
            'js_files' => ['nullable', 'array'],
            'js_files.*' => ['file', 'max:5120', 'extensions:js'],
            'tokens_json' => ['nullable', 'array'],
            'settings_schema_json' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    private function decodeJsonInput(string $key): ?array
    {
        $value = $this->input($key);

        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }

    /**
     * @return array<int, string>
     */
    private function splitLines(mixed $value): array
    {
        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', $value) ?: [])
            ->map(fn (string $line) => trim($line))
            ->filter()
            ->map(fn (string $path) => $this->normalizeAssetPath($path))
            ->values()
            ->all();
    }

    private function normalizeAssetPath(string $path): string
    {
        if (str_starts_with($path, '/tenancy/assets/')) {
            return preg_replace('#^/tenancy/assets/#', '/tenant-assets/', $path) ?: $path;
        }

        return $path;
    }
}
