<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SectionTemplateRequest extends FormRequest
{
    /**
     * @var array<string, string>
     */
    private array $jsonDecodeErrors = [];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $type = $this->input('type') === '__custom'
            ? $this->input('type_custom')
            : $this->input('type');

        $this->merge([
            'type' => $this->normalizeKey($type),
            'variation' => $this->normalizeKey($this->input('variation')),
            'schema_json' => $this->decodeJsonInput('schema_json'),
            'legacy_config_map_json' => $this->decodeJsonInput('legacy_config_map_json'),
            'default_content_json' => $this->decodeJsonInput('default_content_json'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        $templateId = $this->route('section_template')?->id;

        return [
            'theme_id' => ['required', Rule::exists('central.themes', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9][a-z0-9_-]*$/'],
            'variation' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9][a-z0-9_-]*$/',
                Rule::unique('central.section_templates')
                    ->where(fn ($query) => $query->where('theme_id', $this->input('theme_id'))
                        ->where('type', $this->input('type')))
                    ->ignore($templateId),
            ],
            'render_mode' => ['required', Rule::in(['html', 'component'])],
            'component_key' => ['nullable', 'string', 'max:255'],
            'legacy_module_key' => ['nullable', 'string', 'max:255'],
            'html_template' => ['nullable', 'string'],
            'schema_json' => ['nullable', 'array'],
            'legacy_config_map_json' => ['nullable', 'array'],
            'default_content_json' => ['nullable', 'array'],
            'preview_image' => ['nullable', 'image', 'max:4096'],
            'remove_preview_image' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            foreach ($this->jsonDecodeErrors as $field => $message) {
                $v->errors()->add($field, $message);
            }

            $schema = $this->input('schema_json');
            if (! is_array($schema)) {
                return;
            }

            foreach ($schema as $index => $field) {
                if (in_array($index, ['menu_templates', 'menuTemplates'], true)) {
                    if (! is_array($field)) {
                        $v->errors()->add('schema_json', 'menu_templates alanı obje olmalı.');
                    }

                    continue;
                }

                if (! is_array($field)) {
                    $v->errors()->add('schema_json', 'Schema JSON içindeki her alan bir obje olmalı.');
                    continue;
                }

                if (blank($field['type'] ?? null)) {
                    $v->errors()->add('schema_json', 'Schema JSON içindeki her alan için type zorunlu. Eksik satır: '.($index + 1).'.');
                }

                $hasMapKey = is_string($index) && filled($index);

                if (! $hasMapKey && blank($field['key'] ?? $field['name'] ?? null)) {
                    $v->errors()->add('schema_json', 'Schema JSON içindeki her alan için key veya name zorunlu. Eksik satır: '.($index + 1).'.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'variation.unique' => 'Bu tema ve block tipi için aynı varyasyon zaten mevcut.',
            'type.regex' => 'Type sadece küçük harf, rakam, tire ve alt çizgi içermeli.',
            'variation.regex' => 'Variation sadece küçük harf, rakam, tire ve alt çizgi içermeli.',
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

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->jsonDecodeErrors[$key] = match ($key) {
                'schema_json' => 'Schema JSON geçerli JSON olmalı.',
                'default_content_json' => 'Default Content JSON geçerli JSON olmalı.',
                'legacy_config_map_json' => 'Legacy Config Map JSON geçerli JSON olmalı.',
                default => 'JSON alanı geçerli JSON olmalı.',
            };

            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }

    private function normalizeKey(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return Str::of($value)
            ->trim()
            ->lower()
            ->replaceMatches('/[^a-z0-9_-]+/', '-')
            ->replaceMatches('/-+/', '-')
            ->trim('-_')
            ->value();
    }
}
