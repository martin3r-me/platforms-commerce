{{--
    Products Index View
    Übersicht aller Produkte als Liste

    WICHTIG FÜR LLMs:
    - Zeigt alle Produkte des Teams als einfache Liste
    - Keine Board/Kanban-Struktur
    - Verwendet moderne UI-Komponenten
--}}

<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Produkte" icon="heroicon-o-cube" />
    </x-slot>

    <x-ui-page-container>
        <div class="space-y-6">
            {{-- Header --}}
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-[var(--ui-secondary)]">Produkte</h1>
                    <p class="text-[var(--ui-muted)] mt-1">Verwalte deine Produkte</p>
                </div>
            </div>

            {{-- Create Product Form --}}
            <x-ui-panel title="Neues Produkt erstellen">
                <x-ui-form-grid :cols="2" :gap="4">
                    <x-ui-input-text
                        name="name"
                        label="Name"
                        wire:model="name"
                        placeholder="Produktname eingeben..."
                        required
                        :errorKey="'name'"
                    />
                    <div class="flex items-end">
                        <x-ui-button variant="primary" wire:click="createProduct">
                            Produkt erstellen
                        </x-ui-button>
                    </div>
                </x-ui-form-grid>
            </x-ui-panel>

            {{-- Produkte Liste --}}
            <x-ui-panel title="Produkte" :subtitle="count($products) . ' Produkt(e)'">
                <div class="space-y-3">
                    @forelse($products as $product)
                        <a href="{{ route('commerce.products.show', $product) }}" class="block p-4 rounded-md border border-[var(--ui-border)] bg-white hover:bg-[var(--ui-muted-5)] transition" wire:navigate>
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-[var(--ui-secondary)]">{{ $product->name }}</h3>
                                    @if($product->description)
                                        <p class="text-sm text-[var(--ui-muted)] mt-1">{{ Str::limit($product->description, 100) }}</p>
                                    @endif
                                    <div class="mt-2 flex items-center gap-4 text-xs text-[var(--ui-muted)]">
                                        @if($product->price)
                                            <span>{{ number_format($product->price, 2, ',', '.') }} EUR</span>
                                        @endif
                                        <span>{{ $product->created_at->format('d.m.Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="p-6 text-center text-[var(--ui-muted)] bg-white rounded-md border border-[var(--ui-border)]">
                            <p>Noch keine Produkte vorhanden.</p>
                        </div>
                    @endforelse
                </div>
            </x-ui-panel>
        </div>
    </x-ui-page-container>

    {{-- Linke Sidebar --}}
    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Navigation" width="w-80" :defaultOpen="true" storeKey="sidebarOpen" side="left">
            <div class="p-6 space-y-6">
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-3">Navigation</h3>
                    <div class="space-y-2">
                        <x-ui-button variant="secondary-outline" size="sm" :href="route('commerce.index')" wire:navigate class="w-full">
                            <span class="flex items-center gap-2">
                                @svg('heroicon-o-home', 'w-4 h-4')
                                Dashboard
                            </span>
                        </x-ui-button>
                        <x-ui-button variant="secondary-outline" size="sm" :href="route('commerce.articles.index')" wire:navigate class="w-full">
                            <span class="flex items-center gap-2">
                                @svg('heroicon-o-rectangle-stack', 'w-4 h-4')
                                Artikel
                            </span>
                        </x-ui-button>
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    {{-- Rechte Sidebar --}}
    <x-slot name="activity">
        <x-ui-page-sidebar title="Aktivitäten" width="w-80" :defaultOpen="false" storeKey="activityOpen" side="right">
            <div class="p-4 space-y-4">
                <div class="text-sm text-[var(--ui-muted)]">Letzte Aktivitäten</div>
                <div class="space-y-3 text-sm">
                    <div class="p-2 rounded border border-[var(--ui-border)]/60 bg-[var(--ui-muted-5)]">
                        <div class="font-medium text-[var(--ui-secondary)] truncate">Produkte-Übersicht geladen</div>
                        <div class="text-[var(--ui-muted)]">Gerade eben</div>
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>
</x-ui-page>
