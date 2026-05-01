{{-- Recursive category tree item for hierarchical display --}}
<div style="padding-left: {{ $depth * 1.25 }}rem;">
    <div class="py-1.5 group">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                @if($depth > 0)
                    <span class="text-[var(--ui-muted-20)]">&#x2514;</span>
                @endif
                @if($category->color)
                    <span class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $category->color }}"></span>
                @else
                    <span class="w-3 h-3 rounded-full flex-shrink-0 bg-[var(--ui-muted-20)]"></span>
                @endif
                <span class="text-sm font-medium text-[var(--ui-secondary)]">{{ $category->name }}</span>
                @if(($category->articles_count ?? 0) > 0)
                    <span class="text-xs text-[var(--ui-muted)]">({{ $category->articles_count }})</span>
                @endif
            </div>
            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                <button wire:click="editArticleCategory({{ $category->id }})"
                        class="p-1 rounded-lg text-[var(--ui-muted)] hover:bg-[var(--ui-muted-10)] hover:text-[var(--ui-secondary)] transition-all">
                    <x-heroicon-s-pencil-square class="w-3.5 h-3.5"/>
                </button>
                <button @click="confirmDeleteArticleCategory = {{ $category->id }}"
                        class="p-1 rounded-lg text-[var(--ui-muted)] hover:bg-red-50 hover:text-red-600 transition-all">
                    <x-heroicon-s-trash class="w-3.5 h-3.5"/>
                </button>
            </div>
        </div>
        @if($category->description)
            <p class="text-xs text-[var(--ui-muted)] mt-0.5" style="padding-left: {{ $depth > 0 ? '1.75rem' : '1.25rem' }}">{{ $category->description }}</p>
        @endif
    </div>

    {{-- Inline Delete Confirmation --}}
    <div x-show="confirmDeleteArticleCategory === {{ $category->id }}" x-cloak
         class="py-1.5 px-3 mb-1 bg-red-50 rounded-lg flex items-center justify-between">
        <span class="text-xs text-red-700">Wirklich l&ouml;schen? Unterkategorien werden nach oben verschoben.</span>
        <div class="flex items-center gap-2">
            <button @click="confirmDeleteArticleCategory = null"
                    class="text-xs px-2 py-1 rounded bg-white text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)]">
                Abbrechen
            </button>
            <button wire:click="deleteArticleCategory({{ $category->id }})"
                    @click="confirmDeleteArticleCategory = null"
                    class="text-xs px-2 py-1 rounded bg-red-600 text-white hover:bg-red-700">
                L&ouml;schen
            </button>
        </div>
    </div>

    {{-- Recursive children (loaded via descendants relationship) --}}
    @if($category->descendants && $category->descendants->count() > 0)
        @foreach($category->descendants as $child)
            @include('commerce::livewire.settings.category-tree-item', ['category' => $child, 'depth' => $depth + 1])
        @endforeach
    @endif
</div>
