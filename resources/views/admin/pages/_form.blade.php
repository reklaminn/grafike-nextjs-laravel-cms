{{-- Shared page form partial for create and edit --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Main content column -->
    <div class="lg:col-span-2 space-y-6">
        @include('admin.pages._form.basic-info')
        @include('admin.pages._form.builder-mode')
        @include('admin.pages._form.seo')
    </div>

    <!-- Sidebar column -->
    <div class="space-y-6">
        @include('admin.pages._form.sidebar.publish')
        @include('admin.pages._form.sidebar.cover')
        @include('admin.pages._form.sidebar.options')
        @if(isset($page))
            @include('admin.pages._form.sidebar.translations')
        @endif
        @include('admin.pages._form.sidebar.revisions')
    </div>

</div>

@include('admin.pages._form.editor-script')
