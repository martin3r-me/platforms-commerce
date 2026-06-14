{{--
    Commerce Sidebar View
    Modul-spezifische Sidebar

    WICHTIG FÜR LLMs:
    - Wird automatisch in der Haupt-Sidebar eingebunden
    - Verwendet x-ui-sidebar-list / x-ui-sidebar-item Komponenten
    - Unterstützt collapsed/expanded Zustand
--}}

<div>
    {{-- Modul Header --}}
    <div x-show="!collapsed" class="p-3 text-[11px] font-medium text-[var(--ui-secondary)] uppercase tracking-wide border-b border-[var(--ui-border)] mb-2">
        Commerce
    </div>

    {{-- Expanded: Navigation Links --}}
    <x-ui-sidebar-list label="Navigation">
        <x-ui-sidebar-item :href="route('commerce.index')">
            @svg('heroicon-o-home', 'w-4 h-4 text-[var(--ui-secondary)]')
            <span class="ml-2 text-sm">Dashboard</span>
        </x-ui-sidebar-item>
        <x-ui-sidebar-item :href="route('commerce.articles.index')">
            @svg('heroicon-o-rectangle-stack', 'w-4 h-4 text-[var(--ui-secondary)]')
            <span class="ml-2 text-sm">Artikel</span>
        </x-ui-sidebar-item>
        <x-ui-sidebar-item :href="route('commerce.products.index')">
            @svg('heroicon-o-cube', 'w-4 h-4 text-[var(--ui-secondary)]')
            <span class="ml-2 text-sm">Produkte</span>
        </x-ui-sidebar-item>
        <x-ui-sidebar-item :href="route('commerce.products.boards.index')">
            @svg('heroicon-o-view-columns', 'w-4 h-4 text-[var(--ui-secondary)]')
            <span class="ml-2 text-sm">Boards</span>
        </x-ui-sidebar-item>
        <x-ui-sidebar-item :href="route('commerce.suppliers.index')">
            @svg('heroicon-o-truck', 'w-4 h-4 text-[var(--ui-secondary)]')
            <span class="ml-2 text-sm">Lieferanten</span>
        </x-ui-sidebar-item>
        <x-ui-sidebar-item :href="route('commerce.catalogs.index')">
            @svg('heroicon-o-book-open', 'w-4 h-4 text-[var(--ui-secondary)]')
            <span class="ml-2 text-sm">Kataloge</span>
        </x-ui-sidebar-item>
        <x-ui-sidebar-item :href="route('commerce.attributes.index')">
            @svg('heroicon-o-tag', 'w-4 h-4 text-[var(--ui-secondary)]')
            <span class="ml-2 text-sm">Attribute</span>
        </x-ui-sidebar-item>
        <x-ui-sidebar-item :href="route('commerce.settings.index')">
            @svg('heroicon-o-cog-6-tooth', 'w-4 h-4 text-[var(--ui-secondary)]')
            <span class="ml-2 text-sm">Einstellungen</span>
        </x-ui-sidebar-item>
    </x-ui-sidebar-list>

    {{-- Abschnitt: Kataloge (Entity-basierte Gruppierung, analog Planner) --}}
    <div x-show="!collapsed">
        @if($catalogEntityTypeGroups->isNotEmpty() || $unlinkedCatalogs->isNotEmpty())
            <div class="mt-2 mb-1 px-3 text-[10px] uppercase tracking-wider text-[var(--ui-muted)] opacity-70">Kataloge</div>
        @endif

        @foreach($catalogEntityTypeGroups as $typeGroup)
            <x-ui-sidebar-list wire:key="catalog-type-group-{{ $typeGroup['type_id'] }}" :label="$typeGroup['type_name']">
                @foreach($typeGroup['entities'] as $entityNode)
                    @include('commerce::livewire.partials.sidebar-catalog-entity-node', [
                        'node' => $entityNode,
                        'typeIcon' => $typeGroup['type_icon'] ?? null,
                    ])
                @endforeach
            </x-ui-sidebar-list>
        @endforeach

        @if($unlinkedCatalogs->isNotEmpty())
            <x-ui-sidebar-list label="Unverknüpft">
                @foreach($unlinkedCatalogs as $catalog)
                    <a wire:key="unlinked-catalog-{{ $catalog->id }}"
                       href="{{ route('commerce.catalogs.show', ['commerceCatalog' => $catalog]) }}"
                       wire:navigate
                       title="{{ $catalog->name }}"
                       class="flex items-center gap-1.5 py-0.5 pl-3 pr-2 text-[var(--ui-secondary)] hover:text-[var(--ui-primary)] transition truncate">
                        <span class="w-1 h-1 rounded-full flex-shrink-0 bg-[var(--ui-muted)] opacity-40"></span>
                        <span class="truncate text-[11px]">{{ $catalog->name }}</span>
                    </a>
                @endforeach
            </x-ui-sidebar-list>
        @endif
    </div>

    {{-- Collapsed: Icons-only --}}
    <div x-show="collapsed" class="px-2 py-2 border-b border-[var(--ui-border)]">
        <div class="flex flex-col gap-2">
            <a href="{{ route('commerce.index') }}" wire:navigate class="flex items-center justify-center p-2 rounded-md text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)]">
                @svg('heroicon-o-home', 'w-5 h-5')
            </a>
            <a href="{{ route('commerce.articles.index') }}" wire:navigate class="flex items-center justify-center p-2 rounded-md text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)]">
                @svg('heroicon-o-rectangle-stack', 'w-5 h-5')
            </a>
            <a href="{{ route('commerce.products.index') }}" wire:navigate class="flex items-center justify-center p-2 rounded-md text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)]">
                @svg('heroicon-o-cube', 'w-5 h-5')
            </a>
            <a href="{{ route('commerce.products.boards.index') }}" wire:navigate class="flex items-center justify-center p-2 rounded-md text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)]">
                @svg('heroicon-o-view-columns', 'w-5 h-5')
            </a>
            <a href="{{ route('commerce.suppliers.index') }}" wire:navigate class="flex items-center justify-center p-2 rounded-md text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)]">
                @svg('heroicon-o-truck', 'w-5 h-5')
            </a>
            <a href="{{ route('commerce.catalogs.index') }}" wire:navigate class="flex items-center justify-center p-2 rounded-md text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)]">
                @svg('heroicon-o-book-open', 'w-5 h-5')
            </a>
            <a href="{{ route('commerce.attributes.index') }}" wire:navigate class="flex items-center justify-center p-2 rounded-md text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)]">
                @svg('heroicon-o-tag', 'w-5 h-5')
            </a>
            <a href="{{ route('commerce.settings.index') }}" wire:navigate class="flex items-center justify-center p-2 rounded-md text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)]">
                @svg('heroicon-o-cog-6-tooth', 'w-5 h-5')
            </a>
        </div>
    </div>
</div>
