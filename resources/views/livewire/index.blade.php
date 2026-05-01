{{--
    Commerce Dashboard View
    Hauptübersicht des Commerce-Moduls
--}}

<x-ui-page>
    {{-- Navbar --}}
    <x-slot name="navbar">
        <x-ui-page-navbar title="Commerce Dashboard" icon="heroicon-o-shopping-bag" />
    </x-slot>

    {{-- Actionbar mit Breadcrumbs --}}
    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Commerce', 'icon' => 'shopping-bag'],
        ]" />
    </x-slot>

    {{-- Hauptinhalt --}}
    <x-ui-page-container>
        <div class="space-y-6">
            {{-- Welcome Section --}}
            <section class="bg-white rounded-lg border border-gray-200">
                <div class="p-6 text-center">
                    <div class="mb-4">
                        @svg('heroicon-o-shopping-bag', 'w-16 h-16 text-[#166EE1] mx-auto')
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">
                        Commerce Management
                    </h2>
                    <p class="text-[13px] text-gray-500">
                        Verwalte deine Artikel, Produkte, Attribute und Einstellungen.
                    </p>
                </div>
            </section>

            {{-- Commerce Statistiken --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                            @svg('heroicon-o-rectangle-stack', 'w-5 h-5 text-[#166EE1]')
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-900">{{ $stats['total_articles'] }}</div>
                            <div class="text-[11px] font-medium text-gray-500">Artikel</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                            @svg('heroicon-o-cube', 'w-5 h-5 text-[#166EE1]')
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-900">{{ $stats['total_products'] }}</div>
                            <div class="text-[11px] font-medium text-gray-500">Produkte</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-gray-50 flex items-center justify-center">
                            @svg('heroicon-o-tag', 'w-5 h-5 text-gray-500')
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-900">{{ $stats['total_attributes'] }}</div>
                            <div class="text-[11px] font-medium text-gray-500">Attribute</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center">
                            @svg('heroicon-o-squares-2x2', 'w-5 h-5 text-green-600')
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-900">{{ $stats['total_categories'] }}</div>
                            <div class="text-[11px] font-medium text-gray-500">Kategorien</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <section class="bg-white rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Schnellzugriff</h3>
                    <p class="text-[11px] text-gray-500">Häufig verwendete Funktionen</p>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <a href="{{ route('commerce.articles.index') }}" wire:navigate
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 text-[13px] font-medium hover:bg-gray-50 transition-colors justify-center">
                            @svg('heroicon-o-rectangle-stack', 'w-4 h-4')
                            Artikel verwalten
                        </a>
                        <a href="{{ route('commerce.products.index') }}" wire:navigate
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 text-[13px] font-medium hover:bg-gray-50 transition-colors justify-center">
                            @svg('heroicon-o-cube', 'w-4 h-4')
                            Produkte verwalten
                        </a>
                        <a href="{{ route('commerce.attributes.index') }}" wire:navigate
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 text-[13px] font-medium hover:bg-gray-50 transition-colors justify-center">
                            @svg('heroicon-o-tag', 'w-4 h-4')
                            Attribute verwalten
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </x-ui-page-container>

    {{-- Linke Sidebar (Schnellzugriff) --}}
    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Schnellzugriff" width="w-80" :defaultOpen="true" storeKey="sidebarOpen" side="left">
            <div class="p-6 space-y-6">
                {{-- Quick Actions --}}
                <div>
                    <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-3">Aktionen</h3>
                    <div class="space-y-1">
                        <a href="{{ route('commerce.articles.index') }}" wire:navigate
                           class="flex items-center gap-2 px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 text-[13px] font-medium hover:bg-gray-50 transition-colors w-full">
                            @svg('heroicon-o-rectangle-stack', 'w-4 h-4')
                            Artikel
                        </a>
                        <a href="{{ route('commerce.products.index') }}" wire:navigate
                           class="flex items-center gap-2 px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 text-[13px] font-medium hover:bg-gray-50 transition-colors w-full">
                            @svg('heroicon-o-cube', 'w-4 h-4')
                            Produkte
                        </a>
                        <a href="{{ route('commerce.attributes.index') }}" wire:navigate
                           class="flex items-center gap-2 px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 text-[13px] font-medium hover:bg-gray-50 transition-colors w-full">
                            @svg('heroicon-o-tag', 'w-4 h-4')
                            Attribute
                        </a>
                        <a href="{{ route('commerce.settings.index') }}" wire:navigate
                           class="flex items-center gap-2 px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 text-[13px] font-medium hover:bg-gray-50 transition-colors w-full">
                            @svg('heroicon-o-cog-6-tooth', 'w-4 h-4')
                            Einstellungen
                        </a>
                    </div>
                </div>

                {{-- Quick Stats --}}
                <div>
                    <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-3">Schnellstatistiken</h3>
                    <div class="space-y-2">
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="text-[11px] text-gray-500">Artikel</div>
                            <div class="text-lg font-bold text-gray-900">{{ $stats['total_articles'] }}</div>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="text-[11px] text-gray-500">Produkte</div>
                            <div class="text-lg font-bold text-gray-900">{{ $stats['total_products'] }}</div>
                        </div>
                    </div>
                </div>

                {{-- Recent Activity --}}
                <div>
                    <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-3">Letzte Aktivitäten</h3>
                    <div class="space-y-2 text-sm">
                        <div class="p-2 rounded-md border border-gray-200 bg-gray-50">
                            <div class="font-medium text-gray-900 text-[13px] truncate">Dashboard geladen</div>
                            <div class="text-[11px] text-gray-500">vor 1 Minute</div>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    {{-- Rechte Sidebar (Aktivitäten) --}}
    <x-slot name="activity">
        <x-ui-page-sidebar title="Aktivitäten" width="w-80" :defaultOpen="false" storeKey="activityOpen" side="right">
            <div class="p-4 space-y-4">
                <div class="text-[13px] text-gray-500">Letzte Aktivitäten</div>
                <div class="space-y-2 text-sm">
                    <div class="p-2 rounded-md border border-gray-200 bg-gray-50">
                        <div class="font-medium text-gray-900 text-[13px] truncate">Dashboard geladen</div>
                        <div class="text-[11px] text-gray-500">vor 1 Minute</div>
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>
</x-ui-page>
