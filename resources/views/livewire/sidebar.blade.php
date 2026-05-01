{{--
    Commerce Sidebar View
    Modul-spezifische Sidebar

    WICHTIG FÜR LLMs:
    - Wird automatisch in der Haupt-Sidebar eingebunden
    - Verwendet custom Tailwind statt x-ui-sidebar-* Komponenten
    - Unterstützt collapsed/expanded Zustand
--}}

<div>
    {{-- Modul Header --}}
    <div x-show="!collapsed" class="p-3 text-[11px] font-medium text-gray-500 uppercase tracking-wide border-b border-gray-200 mb-2">
        Commerce
    </div>

    {{-- Expanded: Navigation Links --}}
    <div x-show="!collapsed" class="px-2 py-2 border-b border-gray-200">
        <div class="flex flex-col gap-0.5">
            <a href="{{ route('commerce.index') }}" wire:navigate
               class="flex items-center gap-2 px-3 py-2 rounded-md text-[13px] font-medium text-gray-700 hover:bg-gray-100 transition-colors">
                @svg('heroicon-o-home', 'w-4 h-4 text-gray-400')
                Dashboard
            </a>
            <a href="{{ route('commerce.articles.index') }}" wire:navigate
               class="flex items-center gap-2 px-3 py-2 rounded-md text-[13px] font-medium text-gray-700 hover:bg-gray-100 transition-colors">
                @svg('heroicon-o-rectangle-stack', 'w-4 h-4 text-gray-400')
                Artikel
            </a>
            <a href="{{ route('commerce.products.index') }}" wire:navigate
               class="flex items-center gap-2 px-3 py-2 rounded-md text-[13px] font-medium text-gray-700 hover:bg-gray-100 transition-colors">
                @svg('heroicon-o-cube', 'w-4 h-4 text-gray-400')
                Produkte
            </a>
            <a href="{{ route('commerce.products.boards.index') }}" wire:navigate
               class="flex items-center gap-2 px-3 py-2 rounded-md text-[13px] font-medium text-gray-700 hover:bg-gray-100 transition-colors">
                @svg('heroicon-o-view-columns', 'w-4 h-4 text-gray-400')
                Boards
            </a>
            <a href="{{ route('commerce.catalogs.index') }}" wire:navigate
               class="flex items-center gap-2 px-3 py-2 rounded-md text-[13px] font-medium text-gray-700 hover:bg-gray-100 transition-colors">
                @svg('heroicon-o-book-open', 'w-4 h-4 text-gray-400')
                Kataloge
            </a>
            <a href="{{ route('commerce.attributes.index') }}" wire:navigate
               class="flex items-center gap-2 px-3 py-2 rounded-md text-[13px] font-medium text-gray-700 hover:bg-gray-100 transition-colors">
                @svg('heroicon-o-tag', 'w-4 h-4 text-gray-400')
                Attribute
            </a>
            <a href="{{ route('commerce.settings.index') }}" wire:navigate
               class="flex items-center gap-2 px-3 py-2 rounded-md text-[13px] font-medium text-gray-700 hover:bg-gray-100 transition-colors">
                @svg('heroicon-o-cog-6-tooth', 'w-4 h-4 text-gray-400')
                Einstellungen
            </a>
        </div>
    </div>

    {{-- Collapsed: Icons-only --}}
    <div x-show="collapsed" class="px-2 py-2 border-b border-gray-200">
        <div class="flex flex-col gap-2">
            <a href="{{ route('commerce.index') }}" wire:navigate class="flex items-center justify-center p-2 rounded-md text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition-colors">
                @svg('heroicon-o-home', 'w-5 h-5')
            </a>
            <a href="{{ route('commerce.articles.index') }}" wire:navigate class="flex items-center justify-center p-2 rounded-md text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition-colors">
                @svg('heroicon-o-rectangle-stack', 'w-5 h-5')
            </a>
            <a href="{{ route('commerce.products.index') }}" wire:navigate class="flex items-center justify-center p-2 rounded-md text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition-colors">
                @svg('heroicon-o-cube', 'w-5 h-5')
            </a>
            <a href="{{ route('commerce.products.boards.index') }}" wire:navigate class="flex items-center justify-center p-2 rounded-md text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition-colors">
                @svg('heroicon-o-view-columns', 'w-5 h-5')
            </a>
            <a href="{{ route('commerce.catalogs.index') }}" wire:navigate class="flex items-center justify-center p-2 rounded-md text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition-colors">
                @svg('heroicon-o-book-open', 'w-5 h-5')
            </a>
            <a href="{{ route('commerce.attributes.index') }}" wire:navigate class="flex items-center justify-center p-2 rounded-md text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition-colors">
                @svg('heroicon-o-tag', 'w-5 h-5')
            </a>
            <a href="{{ route('commerce.settings.index') }}" wire:navigate class="flex items-center justify-center p-2 rounded-md text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition-colors">
                @svg('heroicon-o-cog-6-tooth', 'w-5 h-5')
            </a>
        </div>
    </div>
</div>
