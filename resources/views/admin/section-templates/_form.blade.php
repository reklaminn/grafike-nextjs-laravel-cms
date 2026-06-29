@php
    $defaultContentValue = old('default_content_json', json_encode($sectionTemplate->default_content_json ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    $legacyConfigMapValue = old('legacy_config_map_json', json_encode($sectionTemplate->legacy_config_map_json ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    $selectedThemeId      = (string) old('theme_id', $sectionTemplate->theme_id);
    $selectedType         = old('type', $sectionTemplate->type);
    $selectedVariation    = old('variation', $sectionTemplate->variation);
    $selectedTypeIsKnown  = $selectedType === null || $selectedType === '' || array_key_exists($selectedType, $typeOptions);
@endphp

@if($errors->any())
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4">
        <ul class="list-disc list-inside space-y-1 text-sm text-red-700">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
    <div class="space-y-6">
        @include('admin.section-templates._form.basic-info')
        @include('admin.section-templates._form.template')
        @include('admin.section-templates._form.schema')
        @include('admin.section-templates._form.legacy')
    </div>
    <div class="space-y-6">
        @if(isset($sectionTemplate) && $sectionTemplate->exists)
            @include('admin.section-templates._form.sidebar.preview')
            @include('admin.section-templates._form.sidebar.versions')
            @include('admin.section-templates._form.sidebar.usage')
        @endif
        @include('admin.section-templates._form.sidebar.docs')
        @include('admin.section-templates._form.sidebar.actions')
    </div>
</div>

@include('admin.section-templates._form.convert-wizard')
@include('admin.section-templates._form.script')
