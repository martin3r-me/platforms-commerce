<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Kataloge" icon="heroicon-o-book-open" />
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Commerce', 'href' => route('commerce.index'), 'icon' => 'shopping-bag'],
            ['label' => 'Kataloge'],
        ]" />
    </x-slot>

    <x-ui-page-container>
        <div class="space-y-6">
            {{-- Katalog erstellen --}}
            <x-ui-panel title="Neuer Katalog">
                <form wire:submit="createCatalog" class="flex items-end gap-4">
                    <div class="flex-1">
                        <x-ui-input-text
                            name="name"
                            label="Name"
                            wire:model="name"
                            placeholder="z.B. FoodBook 2026 DE"
                            required
                        />
                    </div>
                    <x-ui-button type="submit" variant="primary" size="sm">
                        Katalog erstellen
                    </x-ui-button>
                </form>
            </x-ui-panel>

            {{-- Katalog Liste --}}
            <x-ui-panel title="Kataloge" :subtitle="count($catalogs) . ' Kataloge'">
                <div class="space-y-3">
                    @forelse($catalogs as $catalog)
                        <a href="{{ route('commerce.catalogs.show', $catalog) }}" class="block p-4 rounded-md border border-[var(--ui-border)] bg-white hover:bg-[var(--ui-muted-5)] transition" wire:navigate>
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-semibold text-[var(--ui-secondary)]">{{ $catalog->name }}</h3>
                                        @php
                                            $statusColors = [
                                                'draft' => 'bg-slate-100 text-slate-700',
                                                'active' => 'bg-green-100 text-green-700',
                                                'archived' => 'bg-amber-100 text-amber-700',
                                            ];
                                            $statusLabels = [
                                                'draft' => 'Entwurf',
                                                'active' => 'Aktiv',
                                                'archived' => 'Archiviert',
                                            ];
                                            $statusValue = $catalog->status?->value ?? 'draft';
                                        @endphp
                                        <span class="text-xs px-2 py-0.5 rounded-full {{ $statusColors[$statusValue] ?? 'bg-slate-100 text-slate-700' }}">
                                            {{ $statusLabels[$statusValue] ?? $statusValue }}
                                        </span>
                                    </div>
                                    <div class="mt-2 flex items-center gap-4 text-xs text-[var(--ui-muted)]">
                                        <span>{{ $catalog->slug }}</span>
                                        <span>{{ $catalog->sections_count }} {{ $catalog->sections_count === 1 ? 'Sektion' : 'Sektionen' }}</span>
                                        <span>{{ $catalog->created_at->format('d.m.Y') }}</span>
                                    </div>
                                </div>
                                @svg('heroicon-o-chevron-right', 'w-5 h-5 text-[var(--ui-muted)]')
                            </div>
                        </a>
                    @empty
                        <div class="p-6 text-center text-[var(--ui-muted)] bg-white rounded-md border border-[var(--ui-border)]">
                            <p>Noch keine Kataloge vorhanden.</p>
                        </div>
                    @endforelse
                </div>
            </x-ui-panel>
        </div>
    </x-ui-page-container>

    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Info" width="w-80" :defaultOpen="true" storeKey="sidebarOpen" side="left">
            <div class="p-6 space-y-6">
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-4">Navigation</h3>
                    <div class="space-y-2">
                        <x-ui-button
                            variant="secondary-outline"
                            size="sm"
                            :href="route('commerce.index')"
                            wire:navigate
                            class="w-full"
                        >
                            <span class="flex items-center gap-2">
                                @svg('heroicon-o-arrow-left', 'w-4 h-4')
                                Zum Dashboard
                            </span>
                        </x-ui-button>
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    <x-slot name="activity">
        <x-ui-page-sidebar title="Aktivitäten" width="w-80" :defaultOpen="false" storeKey="activityOpen" side="right">
            <div class="p-4 space-y-4">
                <div class="text-sm text-[var(--ui-muted)]">Letzte Aktivitäten</div>
                <div class="space-y-3 text-sm">
                    <div class="p-2 rounded border border-[var(--ui-border)]/60 bg-[var(--ui-muted-5)]">
                        <div class="font-medium text-[var(--ui-secondary)] truncate">Katalog-Übersicht geladen</div>
                        <div class="text-[var(--ui-muted)]">Gerade eben</div>
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>
</x-ui-page>
