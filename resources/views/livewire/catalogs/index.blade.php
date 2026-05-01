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
            <section class="bg-white rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Neuer Katalog</h3>
                </div>
                <div class="p-4">
                    <form wire:submit="createCatalog" class="flex items-end gap-4">
                        <div class="flex-1">
                            <label class="block text-[11px] font-medium text-gray-500 mb-1">Name</label>
                            <input type="text"
                                   wire:model="name"
                                   placeholder="z.B. FoodBook 2026 DE"
                                   required
                                   class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                            @error('name') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-[#166EE1] text-white text-[13px] font-medium hover:bg-blue-700 transition-colors">
                            Katalog erstellen
                        </button>
                    </form>
                </div>
            </section>

            {{-- Katalog Liste --}}
            <section class="bg-white rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Kataloge</h3>
                    <p class="text-[11px] text-gray-500">{{ count($catalogs) }} Kataloge</p>
                </div>
                <div class="p-4 space-y-3">
                    @forelse($catalogs as $catalog)
                        <a href="{{ route('commerce.catalogs.show', $catalog) }}" class="block p-4 rounded-md border border-gray-200 bg-white hover:bg-blue-50/50 transition-colors" wire:navigate>
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-[13px] font-semibold text-gray-900">{{ $catalog->name }}</h3>
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
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[11px] font-medium {{ $statusColors[$statusValue] ?? 'bg-slate-100 text-slate-700' }}">
                                            {{ $statusLabels[$statusValue] ?? $statusValue }}
                                        </span>
                                    </div>
                                    <div class="mt-2 flex items-center gap-4 text-[11px] text-gray-400">
                                        <span>{{ $catalog->slug }}</span>
                                        <span>{{ $catalog->sections_count }} {{ $catalog->sections_count === 1 ? 'Sektion' : 'Sektionen' }}</span>
                                        <span>{{ $catalog->created_at->format('d.m.Y') }}</span>
                                    </div>
                                </div>
                                @svg('heroicon-o-chevron-right', 'w-5 h-5 text-gray-400')
                            </div>
                        </a>
                    @empty
                        <div class="p-6 text-center text-gray-500 bg-white rounded-md border border-gray-200">
                            <p class="text-[13px]">Noch keine Kataloge vorhanden.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </x-ui-page-container>

    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Info" width="w-80" :defaultOpen="true" storeKey="sidebarOpen" side="left">
            <div class="p-6 space-y-6">
                <div>
                    <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-4">Navigation</h3>
                    <div class="space-y-1">
                        <a href="{{ route('commerce.index') }}" wire:navigate
                           class="flex items-center gap-2 px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 text-[13px] font-medium hover:bg-gray-50 transition-colors w-full">
                            @svg('heroicon-o-arrow-left', 'w-4 h-4')
                            Zum Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    <x-slot name="activity">
        <x-ui-page-sidebar title="Aktivitäten" width="w-80" :defaultOpen="false" storeKey="activityOpen" side="right">
            <div class="p-4 space-y-4">
                <div class="text-[13px] text-gray-500">Letzte Aktivitäten</div>
                <div class="space-y-2">
                    <div class="p-2 rounded-md border border-gray-200 bg-gray-50">
                        <div class="font-medium text-gray-900 text-[13px] truncate">Katalog-Übersicht geladen</div>
                        <div class="text-[11px] text-gray-500">Gerade eben</div>
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>
</x-ui-page>
