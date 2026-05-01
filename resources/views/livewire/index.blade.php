{{--
    Commerce Dashboard View
    Hauptübersicht des Commerce-Moduls
    
    WICHTIG FÜR LLMs:
    - Verwendet x-ui-page Layout (Standard für alle Modul-Views)
    - Hat beide Sidebars (links & rechts)
    - Zeigt Übersicht/Statistiken
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
            <x-ui-panel title="Willkommen im Commerce" subtitle="Verwalte deine Artikel, Produkte und Attribute">
                <div class="p-6 text-center">
                    <div class="mb-4">
                        @svg('heroicon-o-shopping-bag', 'w-16 h-16 text-[var(--ui-primary)] mx-auto')
                    </div>
                    <h2 class="text-xl font-semibold text-[var(--ui-secondary)] mb-2">
                        Commerce Management
                    </h2>
                    <p class="text-[var(--ui-muted)]">
                        Verwalte deine Artikel, Produkte, Attribute und Einstellungen.
                    </p>
                </div>
            </x-ui-panel>

            {{-- Commerce Statistiken --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <x-ui-dashboard-tile
                    title="Artikel"
                    :count="$stats['total_articles']"
                    subtitle="Gesamt"
                    icon="rectangle-stack"
                    variant="primary"
                    size="lg"
                />
                <x-ui-dashboard-tile
                    title="Produkte"
                    :count="$stats['total_products']"
                    subtitle="Gesamt"
                    icon="cube"
                    variant="info"
                    size="lg"
                />
                <x-ui-dashboard-tile
                    title="Attribute"
                    :count="$stats['total_attributes']"
                    subtitle="Gesamt"
                    icon="tag"
                    variant="neutral"
                    size="lg"
                />
                <x-ui-dashboard-tile
                    title="Kategorien"
                    :count="$stats['total_categories']"
                    subtitle="Gesamt"
                    icon="squares-2x2"
                    variant="success"
                    size="lg"
                />
            </div>

            {{-- Quick Actions --}}
            <x-ui-panel title="Schnellzugriff" subtitle="Häufig verwendete Funktionen">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-ui-button variant="secondary-outline" size="sm" :href="route('commerce.articles.index')" wire:navigate class="w-full">
                        <span class="flex items-center gap-2">
                            @svg('heroicon-o-rectangle-stack', 'w-4 h-4')
                            <span>Artikel verwalten</span>
                        </span>
                    </x-ui-button>
                    <x-ui-button variant="secondary-outline" size="sm" :href="route('commerce.products.index')" wire:navigate class="w-full">
                        <span class="flex items-center gap-2">
                            @svg('heroicon-o-cube', 'w-4 h-4')
                            <span>Produkte verwalten</span>
                        </span>
                    </x-ui-button>
                    <x-ui-button variant="secondary-outline" size="sm" :href="route('commerce.attributes.index')" wire:navigate class="w-full">
                        <span class="flex items-center gap-2">
                            @svg('heroicon-o-tag', 'w-4 h-4')
                            <span>Attribute verwalten</span>
                        </span>
                    </x-ui-button>
                </div>
            </x-ui-panel>
        </div>
    </x-ui-page-container>

    {{-- Linke Sidebar (Schnellzugriff) --}}
    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Schnellzugriff" width="w-80" :defaultOpen="true" storeKey="sidebarOpen" side="left">
            <div class="p-6 space-y-6">
                {{-- Quick Actions --}}
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-3">Aktionen</h3>
                    <div class="space-y-2">
                        <x-ui-button variant="secondary-outline" size="sm" :href="route('commerce.articles.index')" wire:navigate class="w-full">
                            <span class="flex items-center gap-2">
                                @svg('heroicon-o-rectangle-stack', 'w-4 h-4')
                                Artikel
                            </span>
                        </x-ui-button>
                        <x-ui-button variant="secondary-outline" size="sm" :href="route('commerce.products.index')" wire:navigate class="w-full">
                            <span class="flex items-center gap-2">
                                @svg('heroicon-o-cube', 'w-4 h-4')
                                Produkte
                            </span>
                        </x-ui-button>
                        <x-ui-button variant="secondary-outline" size="sm" :href="route('commerce.attributes.index')" wire:navigate class="w-full">
                            <span class="flex items-center gap-2">
                                @svg('heroicon-o-tag', 'w-4 h-4')
                                Attribute
                            </span>
                        </x-ui-button>
                        <x-ui-button variant="secondary-outline" size="sm" :href="route('commerce.settings.index')" wire:navigate class="w-full">
                            <span class="flex items-center gap-2">
                                @svg('heroicon-o-cog-6-tooth', 'w-4 h-4')
                                Einstellungen
                            </span>
                        </x-ui-button>
                    </div>
                </div>

                {{-- Quick Stats --}}
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-3">Schnellstatistiken</h3>
                    <div class="space-y-3">
                        <div class="p-3 bg-[var(--ui-muted-5)] rounded-lg border border-[var(--ui-border)]/40">
                            <div class="text-xs text-[var(--ui-muted)]">Artikel</div>
                            <div class="text-lg font-bold text-[var(--ui-secondary)]">{{ $stats['total_articles'] }}</div>
                        </div>
                        <div class="p-3 bg-[var(--ui-muted-5)] rounded-lg border border-[var(--ui-border)]/40">
                            <div class="text-xs text-[var(--ui-muted)]">Produkte</div>
                            <div class="text-lg font-bold text-[var(--ui-secondary)]">{{ $stats['total_products'] }}</div>
                        </div>
                    </div>
                </div>

                {{-- Recent Activity --}}
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-3">Letzte Aktivitäten</h3>
                    <div class="space-y-2 text-sm">
                        <div class="p-2 rounded border border-[var(--ui-border)]/60 bg-[var(--ui-muted-5)]">
                            <div class="font-medium text-[var(--ui-secondary)] truncate">Dashboard geladen</div>
                            <div class="text-[var(--ui-muted)] text-xs">vor 1 Minute</div>
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
                <div class="text-sm text-[var(--ui-muted)]">Letzte Aktivitäten</div>
                <div class="space-y-3 text-sm">
                    <div class="p-2 rounded border border-[var(--ui-border)]/60 bg-[var(--ui-muted-5)]">
                        <div class="font-medium text-[var(--ui-secondary)] truncate">Dashboard geladen</div>
                        <div class="text-[var(--ui-muted)]">vor 1 Minute</div>
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>
</x-ui-page>
