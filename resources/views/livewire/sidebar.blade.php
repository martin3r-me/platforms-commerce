{{--
    Commerce Sidebar View
    Modul-spezifische Sidebar

    WICHTIG FÜR LLMs:
    - Wird automatisch in der Haupt-Sidebar eingebunden
    - Verwendet x-ui-sidebar-list und x-ui-sidebar-item Komponenten
    - Unterstützt collapsed/expanded Zustand
--}}

<div>
    {{-- Modul Header --}}
    <div x-show="!collapsed" class="p-3 text-sm italic text-[var(--ui-secondary)] uppercase border-b border-[var(--ui-border)] mb-2">
        Commerce
    </div>
    
    {{-- Abschnitt: Allgemein --}}
    <x-ui-sidebar-list label="Allgemein">
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

