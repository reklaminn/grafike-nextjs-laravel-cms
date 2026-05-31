@extends('admin.layouts.app')

@section('title', 'Formlar')
@section('page-title', 'Formlar')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-gray-500">Formları ve gelen mesajları yönetin.</p>
        <a href="{{ route('admin.forms.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
            <i class="fas fa-plus"></i> Yeni Form
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($forms as $form)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow
                        {{ $form->is_system ? 'border-l-4 border-l-indigo-400' : '' }}">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-semibold text-gray-800 truncate">{{ $form->name }}</h3>
                            @if($form->is_system)
                                <span class="flex-shrink-0 inline-flex items-center gap-1 px-1.5 py-0.5 bg-indigo-50 text-indigo-600 text-[10px] font-semibold rounded border border-indigo-200">
                                    <i class="fas fa-lock text-[9px]"></i> Sistem
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-400 mt-1">{{ $form->description ?? 'Açıklama yok' }}</p>
                    </div>
                    @if($form->unread_count > 0)
                        <span class="flex-shrink-0 px-2 py-1 bg-red-500 text-white text-xs font-bold rounded-full">{{ $form->unread_count }}</span>
                    @endif
                </div>

                <div class="mt-4 flex items-center gap-4 text-sm text-gray-500">
                    <span><i class="fas fa-inbox mr-1"></i> {{ $form->submissions_count }} mesaj</span>
                    <span><i class="fas fa-list mr-1"></i> {{ $form->fields_count }} alan</span>
                </div>

                <div class="mt-4 flex items-center gap-2">
                    <a href="{{ route('admin.forms.submissions', $form) }}"
                       class="flex-1 text-center px-3 py-2 bg-orange-50 text-orange-700 text-xs font-medium rounded-lg hover:bg-orange-100 transition-colors">
                        <i class="fas fa-inbox mr-1"></i> Mesajlar
                    </a>
                    <a href="{{ route('admin.forms.edit', $form) }}"
                       class="flex-1 text-center px-3 py-2 bg-indigo-50 text-indigo-700 text-xs font-medium rounded-lg hover:bg-indigo-100 transition-colors">
                        <i class="fas fa-edit mr-1"></i> Düzenle
                    </a>
                    @if(!$form->is_system)
                    <form method="POST" action="{{ route('admin.forms.destroy', $form) }}"
                          onsubmit="return confirm('Bu formu silmek istediğinizden emin misiniz?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="px-3 py-2 bg-red-50 text-red-600 text-xs font-medium rounded-lg hover:bg-red-100 transition-colors">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-gray-400">
                <i class="fas fa-clipboard-list text-4xl mb-3"></i>
                <p>Henüz form bulunmuyor.</p>
            </div>
        @endforelse
    </div>
@endsection
