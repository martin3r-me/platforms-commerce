<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="{{ $catalog->name }}" />
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Commerce', 'href' => route('commerce.index'), 'icon' => 'shopping-bag'],
            ['label' => 'Kataloge', 'href' => route('commerce.catalogs.index')],
            ['label' => $catalog->name],
        ]" />
    </x-slot>

    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Übersicht" width="w-80" :defaultOpen="true" storeKey="sidebarOpen" side="left">
            <div class="p-6 space-y-6">
                <div>
                    <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-4">Navigation</h3>
                    <div class="space-y-1">
                        <a href="{{ route('commerce.catalogs.index') }}" wire:navigate
                           class="flex items-center gap-2 px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 text-[13px] font-medium hover:bg-gray-50 transition-colors w-full">
                            @svg('heroicon-o-arrow-left', 'w-4 h-4')
                            Zur Katalogübersicht
                        </a>
                    </div>
                </div>

                <hr class="border-gray-200">

                <div>
                    <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-4">Katalog Info</h3>
                    <div class="space-y-2 text-[13px]">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Status:</span>
                            <span class="font-medium text-gray-900">
                                @php
                                    $statusLabels = ['draft' => 'Entwurf', 'active' => 'Aktiv', 'archived' => 'Archiviert'];
                                @endphp
                                {{ $statusLabels[$catalog->status?->value ?? 'draft'] ?? $catalog->status?->value }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Sektionen:</span>
                            <span class="font-medium text-gray-900">{{ $catalog->sections->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Erstellt:</span>
                            <span class="font-medium text-gray-900">{{ $catalog->created_at->format('d.m.Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Geändert:</span>
                            <span class="font-medium text-gray-900">{{ $catalog->updated_at->format('d.m.Y H:i') }}</span>
                        </div>
                        @if($catalog->creator)
                            <div class="flex justify-between">
                                <span class="text-gray-500">Erstellt von:</span>
                                <span class="font-medium text-gray-900">{{ $catalog->creator->name ?? 'Unbekannt' }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    <x-slot name="activity">
        <x-ui-page-sidebar title="Aktivitäten" width="w-80" :defaultOpen="false" storeKey="activityOpen" side="right">
            <div class="p-4">
                <livewire:activity-log.index
                    :model="$catalog"
                    :key="get_class($catalog) . '_' . $catalog->id"
                />
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    <x-ui-page-container spacing="space-y-8">
        <div x-data="{
            selectedTab: 'general',
            scrollToSection(sectionId) {
                const section = document.getElementById(sectionId);
                if (section) {
                    section.scrollIntoView({ behavior: 'smooth' });
                }
                this.selectedTab = sectionId;
            }
        }">
            {{-- Tabs Navigation --}}
            <div class="border-b border-gray-200 mb-6">
                <nav class="flex gap-1">
                    <button x-on:click="scrollToSection('general')"
                            :class="{ 'border-[#166EE1] text-[#166EE1]': selectedTab === 'general', 'border-transparent text-gray-400 hover:text-gray-700': selectedTab !== 'general' }"
                            class="px-4 py-2 text-[13px] font-medium border-b-2 -mb-px transition-all duration-200">
                        Allgemein
                    </button>
                    <button x-on:click="scrollToSection('sections')"
                            :class="{ 'border-[#166EE1] text-[#166EE1]': selectedTab === 'sections', 'border-transparent text-gray-400 hover:text-gray-700': selectedTab !== 'sections' }"
                            class="px-4 py-2 text-[13px] font-medium border-b-2 -mb-px transition-all duration-200">
                        Sektionen
                    </button>
                </nav>
            </div>

            {{-- General Section --}}
            <section id="general" class="scroll-mt-4 mb-8">
                <section class="bg-white rounded-lg border border-gray-200">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Allgemeine Informationen</h3>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[11px] font-medium text-gray-500 mb-1">Name *</label>
                                    <input type="text" wire:model.live="catalog.name" required
                                           class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                                    @error('catalog.name') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-[11px] font-medium text-gray-500 mb-1">Slug *</label>
                                    <input type="text" wire:model.live="catalog.slug" required
                                           class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                                    @error('catalog.slug') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-[11px] font-medium text-gray-500 mb-1">Beschreibung</label>
                                    <textarea wire:model.live="catalog.description" rows="4"
                                              class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]"></textarea>
                                    @error('catalog.description') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[11px] font-medium text-gray-500 mb-1">Status</label>
                                    <select wire:model.live="catalog.status"
                                            class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                                        @foreach($statuses as $status)
                                            <option value="{{ $status->value }}">
                                                @php $labels = ['draft' => 'Entwurf', 'active' => 'Aktiv', 'archived' => 'Archiviert']; @endphp
                                                {{ $labels[$status->value] ?? $status->value }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-medium text-gray-500 mb-1">Gültig ab</label>
                                    <input type="date" wire:model.live="catalog.valid_from"
                                           class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                                    @error('catalog.valid_from') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-[11px] font-medium text-gray-500 mb-1">Gültig bis</label>
                                    <input type="date" wire:model.live="catalog.valid_until"
                                           class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                                    @error('catalog.valid_until') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </section>

            {{-- Sections --}}
            <section id="sections" class="scroll-mt-4">
                <section class="bg-white rounded-lg border border-gray-200">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Sektionen</h3>
                        <p class="text-[11px] text-gray-500">{{ $catalog->sections->count() }} Sektionen</p>
                    </div>
                    <div class="p-4">
                        {{-- Create Section --}}
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                            <h4 class="text-[13px] font-medium text-gray-700 mb-3">Neue Sektion</h4>
                            <form wire:submit="createSection" class="flex items-end gap-4">
                                <div class="flex-1">
                                    <label class="block text-[11px] font-medium text-gray-500 mb-1">Name</label>
                                    <input type="text" wire:model="sectionName" placeholder="z.B. Vorspeisen" required
                                           class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                                </div>
                                <div class="w-32">
                                    <label class="block text-[11px] font-medium text-gray-500 mb-1">Reihenfolge</label>
                                    <input type="number" wire:model="sectionSortOrder" min="0"
                                           class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                                </div>
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-[#166EE1] text-white text-[13px] font-medium hover:bg-blue-700 transition-colors">
                                    Hinzufügen
                                </button>
                            </form>
                        </div>

                        {{-- Sections List --}}
                        <div class="space-y-4">
                            @forelse($catalog->sections as $section)
                                <div class="p-4 rounded-lg border border-gray-200 bg-white" wire:key="section-{{ $section->id }}">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-3">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-700">{{ $section->sort_order }}</span>
                                            <h4 class="text-[13px] font-semibold text-gray-900">{{ $section->name }}</h4>
                                        </div>
                                        <button wire:click="deleteSection({{ $section->id }})"
                                                wire:confirm="Sektion '{{ $section->name }}' wirklich löschen?"
                                                class="p-1.5 rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                            @svg('heroicon-o-trash', 'w-4 h-4')
                                        </button>
                                    </div>

                                    {{-- Attached Boards --}}
                                    <div class="ml-8">
                                        @if($section->productBoards->count() > 0)
                                            <div class="space-y-2 mb-3">
                                                @foreach($section->productBoards as $board)
                                                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded text-[13px]">
                                                        <div class="flex items-center gap-2">
                                                            @if($board->color)
                                                                <span class="w-3 h-3 rounded-full" style="background-color: {{ $board->color }}"></span>
                                                            @endif
                                                            <span>{{ $board->name }}</span>
                                                        </div>
                                                        <button wire:click="detachBoard({{ $section->id }}, {{ $board->id }})"
                                                                class="p-1.5 rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                                            @svg('heroicon-o-x-mark', 'w-4 h-4')
                                                        </button>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        {{-- Attach Board --}}
                                        @php
                                            $attachedIds = $section->productBoards->pluck('id')->toArray();
                                            $unattached = collect($availableBoards)->filter(fn($b) => !in_array($b->id, $attachedIds));
                                        @endphp
                                        @if($unattached->count() > 0)
                                            <div class="flex items-center gap-2">
                                                <select id="attach-board-{{ $section->id }}"
                                                        class="flex-1 px-3 py-1.5 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                                                    <option value="">Board auswählen...</option>
                                                    @foreach($unattached as $board)
                                                        <option value="{{ $board->id }}">{{ $board->name }}</option>
                                                    @endforeach
                                                </select>
                                                <button
                                                    x-on:click="
                                                        const sel = document.getElementById('attach-board-{{ $section->id }}');
                                                        if (sel.value) {
                                                            $wire.attachBoard({{ $section->id }}, parseInt(sel.value));
                                                            sel.value = '';
                                                        }
                                                    "
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-[#166EE1] text-white text-[13px] font-medium hover:bg-blue-700 transition-colors">
                                                    @svg('heroicon-o-plus', 'w-4 h-4')
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="p-6 text-center text-gray-500 bg-white rounded-md border border-gray-200">
                                    <p class="text-[13px]">Noch keine Sektionen vorhanden.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>
            </section>
        </div>
    </x-ui-page-container>
</x-ui-page>
