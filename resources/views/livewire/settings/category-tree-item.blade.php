{{-- Recursive category tree item for hierarchical display --}}
<div style="padding-left: {{ $depth * 1.25 }}rem;">
    <div class="py-1.5 group">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                @if($depth > 0)
                    <span class="text-gray-300">&#x2514;</span>
                @endif
                @if($category->color)
                    <span class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $category->color }}"></span>
                @else
                    <span class="w-3 h-3 rounded-full flex-shrink-0 bg-gray-300"></span>
                @endif
                <span class="text-[13px] font-medium text-gray-900">{{ $category->name }}</span>
                @if(($category->articles_count ?? 0) > 0)
                    <span class="text-[11px] text-gray-500">({{ $category->articles_count }})</span>
                @endif
            </div>
            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                <button wire:click="editArticleCategory({{ $category->id }})"
                        class="p-1 rounded-md text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition-colors">
                    <x-heroicon-s-pencil-square class="w-3.5 h-3.5"/>
                </button>
                <button x-on:click="confirmDeleteArticleCategory = {{ $category->id }}"
                        class="p-1 rounded-md text-gray-400 hover:bg-red-50 hover:text-red-600 transition-colors">
                    <x-heroicon-s-trash class="w-3.5 h-3.5"/>
                </button>
            </div>
        </div>
        @if($category->description)
            <p class="text-[11px] text-gray-500 mt-0.5" style="padding-left: {{ $depth > 0 ? '1.75rem' : '1.25rem' }}">{{ $category->description }}</p>
        @endif
    </div>

    {{-- Inline Delete Confirmation --}}
    <div x-show="confirmDeleteArticleCategory === {{ $category->id }}" x-cloak
         class="py-1.5 px-3 mb-1 bg-red-50 rounded-lg flex items-center justify-between">
        <span class="text-[11px] text-red-700">Wirklich l&ouml;schen? Unterkategorien werden nach oben verschoben.</span>
        <div class="flex items-center gap-2">
            <button x-on:click="confirmDeleteArticleCategory = null"
                    class="inline-flex items-center px-2 py-1 rounded-md border border-gray-300 bg-white text-gray-700 text-[11px] font-medium hover:bg-gray-50 transition-colors">
                Abbrechen
            </button>
            <button wire:click="deleteArticleCategory({{ $category->id }})"
                    x-on:click="confirmDeleteArticleCategory = null"
                    class="inline-flex items-center px-2 py-1 rounded-md bg-red-600 text-white text-[11px] font-medium hover:bg-red-700 transition-colors">
                L&ouml;schen
            </button>
        </div>
    </div>

    {{-- Recursive children --}}
    @if($category->descendants && $category->descendants->count() > 0)
        @foreach($category->descendants as $child)
            @include('commerce::livewire.settings.category-tree-item', ['category' => $child, 'depth' => $depth + 1])
        @endforeach
    @endif
</div>
