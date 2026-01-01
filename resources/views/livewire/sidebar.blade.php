{{-- Commerce Sidebar --}}
<div>
    {{-- Modul Header --}}
    <x-sidebar-module-header module-name="Commerce" />

    {{-- Abschnitt: Allgemein --}}
    <div>
        <h4 x-show="!collapsed" class="p-3 text-sm italic text-secondary uppercase">Allgemein</h4>

        {{-- Dashboard --}}
        <a href="{{ route('commerce.index') }}"
           class="relative d-flex items-center p-2 my-1 rounded-md font-medium transition"
           :class="[
               window.location.pathname === '/commerce' || 
               window.location.pathname.endsWith('/commerce') || 
               window.location.pathname.endsWith('/commerce/')
                   ? 'bg-primary text-on-primary shadow-md'
                   : 'text-black hover:bg-primary-10 hover:text-primary hover:shadow-md',
               collapsed ? 'justify-center' : 'gap-3'
           ]"
           wire:navigate>
            <x-heroicon-o-home class="w-6 h-6 flex-shrink-0"/>
            <span x-show="!collapsed" class="truncate">Dashboard</span>
        </a>

        {{-- Artikel --}}
        <a href="{{ route('commerce.articles.index') }}"
           class="relative d-flex items-center p-2 my-1 rounded-md font-medium transition"
           :class="[
               window.location.pathname.includes('/commerce/articles')
                   ? 'bg-primary text-on-primary shadow-md'
                   : 'text-black hover:bg-primary-10 hover:text-primary hover:shadow-md',
               collapsed ? 'justify-center' : 'gap-3'
           ]"
           wire:navigate>
            <x-heroicon-o-rectangle-stack class="w-6 h-6 flex-shrink-0"/>
            <span x-show="!collapsed" class="truncate">Artikel</span>
        </a>

        {{-- Produkte --}}
        <a href="{{ route('commerce.products.index') }}"
           class="relative d-flex items-center p-2 my-1 rounded-md font-medium transition"
           :class="[
               window.location.pathname.includes('/commerce/products')
                   ? 'bg-primary text-on-primary shadow-md'
                   : 'text-black hover:bg-primary-10 hover:text-primary hover:shadow-md',
               collapsed ? 'justify-center' : 'gap-3'
           ]"
           wire:navigate>
            <x-heroicon-o-cube class="w-6 h-6 flex-shrink-0"/>
            <span x-show="!collapsed" class="truncate">Produkte</span>
        </a>

        {{-- Attribute --}}
        <a href="{{ route('commerce.attributes.index') }}"
           class="relative d-flex items-center p-2 my-1 rounded-md font-medium transition"
           :class="[
               window.location.pathname.includes('/commerce/attributes')
                   ? 'bg-primary text-on-primary shadow-md'
                   : 'text-black hover:bg-primary-10 hover:text-primary hover:shadow-md',
               collapsed ? 'justify-center' : 'gap-3'
           ]"
           wire:navigate>
            <x-heroicon-o-tag class="w-6 h-6 flex-shrink-0"/>
            <span x-show="!collapsed" class="truncate">Attribute</span>
        </a>

        {{-- Einstellungen --}}
        <a href="{{ route('commerce.settings.index') }}"
           class="relative d-flex items-center p-2 my-1 rounded-md font-medium transition"
           :class="[
               window.location.pathname.includes('/commerce/settings')
                   ? 'bg-primary text-on-primary shadow-md'
                   : 'text-black hover:bg-primary-10 hover:text-primary hover:shadow-md',
               collapsed ? 'justify-center' : 'gap-3'
           ]"
           wire:navigate>
            <x-heroicon-o-cog-6-tooth class="w-6 h-6 flex-shrink-0"/>
            <span x-show="!collapsed" class="truncate">Einstellungen</span>
        </a>
    </div>

    {{-- Abschnitt: Produkt-Boards (dynamisch) --}}
    @if($productBoards->isNotEmpty())
        <div x-show="!collapsed" class="mt-2">
            <h4 class="p-3 text-sm italic text-secondary uppercase">Produkt-Boards</h4>
            @foreach($productBoards as $board)
                <a href="{{ route('commerce.products.boards.show', $board) }}"
                   class="relative d-flex items-center p-2 my-1 rounded-md font-medium transition gap-3"
                   :class="[
                       window.location.pathname.includes('/commerce/products/boards/{{ $board->id }}')
                           ? 'bg-primary text-on-primary shadow-md'
                           : 'text-black hover:bg-primary-10 hover:text-primary hover:shadow-md'
                   ]"
                   wire:navigate>
                    <x-heroicon-o-folder class="w-6 h-6 flex-shrink-0"/>
                    <span class="truncate">{{ $board->name }}</span>
                </a>
            @endforeach
        </div>
    @endif
</div>

