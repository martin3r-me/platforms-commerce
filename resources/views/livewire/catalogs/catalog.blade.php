<x-ui-page x-data="{
    selectedTab: 'general',
    scrollToSection(sectionId) {
        const section = document.getElementById(sectionId);
        if (section) {
            section.scrollIntoView({ behavior: 'smooth' });
        }
        this.selectedTab = sectionId;
    }
}">
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
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-4">Navigation</h3>
                    <div class="space-y-2">
                        <x-ui-button
                            variant="secondary-outline"
                            size="sm"
                            :href="route('commerce.catalogs.index')"
                            wire:navigate
                            class="w-full"
                        >
                            <span class="flex items-center gap-2">
                                @svg('heroicon-o-arrow-left', 'w-4 h-4')
                                Zur Katalogübersicht
                            </span>
                        </x-ui-button>
                    </div>
                </div>

                <hr>

                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-4">Katalog Info</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-[var(--ui-muted)]">Status:</span>
                            <span class="font-medium text-[var(--ui-secondary)]">
                                @php
                                    $statusLabels = ['draft' => 'Entwurf', 'active' => 'Aktiv', 'archived' => 'Archiviert'];
                                @endphp
                                {{ $statusLabels[$catalog->status?->value ?? 'draft'] ?? $catalog->status?->value }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[var(--ui-muted)]">Sektionen:</span>
                            <span class="font-medium text-[var(--ui-secondary)]">{{ $catalog->sections->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[var(--ui-muted)]">Erstellt:</span>
                            <span class="font-medium text-[var(--ui-secondary)]">{{ $catalog->created_at->format('d.m.Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[var(--ui-muted)]">Geändert:</span>
                            <span class="font-medium text-[var(--ui-secondary)]">{{ $catalog->updated_at->format('d.m.Y H:i') }}</span>
                        </div>
                        @if($catalog->creator)
                            <div class="flex justify-between">
                                <span class="text-[var(--ui-muted)]">Erstellt von:</span>
                                <span class="font-medium text-[var(--ui-secondary)]">{{ $catalog->creator->name ?? 'Unbekannt' }}</span>
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
        {{-- Tabs Navigation --}}
        <div class="border-b border-[var(--ui-border)]/60 mb-6">
            <nav class="flex gap-1">
                <button @click="scrollToSection('general')"
                        :class="{ 'border-b-2 border-[var(--ui-primary)] text-[var(--ui-primary)]': selectedTab === 'general', 'text-[var(--ui-muted)] hover:text-[var(--ui-primary)]': selectedTab !== 'general' }"
                        class="px-3 py-2 text-sm font-medium transition-all duration-200">
                    Allgemein
                </button>
                <button @click="scrollToSection('sections')"
                        :class="{ 'border-b-2 border-[var(--ui-primary)] text-[var(--ui-primary)]': selectedTab === 'sections', 'text-[var(--ui-muted)] hover:text-[var(--ui-primary)]': selectedTab !== 'sections' }"
                        class="px-3 py-2 text-sm font-medium transition-all duration-200">
                    Sektionen
                </button>
            </nav>
        </div>

        {{-- General Section --}}
        <section id="general" class="scroll-mt-4">
            <x-ui-panel title="Allgemeine Informationen">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <x-ui-input-text
                            name="catalog.name"
                            label="Name *"
                            wire:model.live="catalog.name"
                            required
                            :errorKey="'catalog.name'"
                        />
                        <x-ui-input-text
                            name="catalog.slug"
                            label="Slug *"
                            wire:model.live="catalog.slug"
                            required
                            :errorKey="'catalog.slug'"
                        />
                        <x-ui-input-textarea
                            name="catalog.description"
                            label="Beschreibung"
                            wire:model.live="catalog.description"
                            rows="4"
                            :errorKey="'catalog.description'"
                        />
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                            <select wire:model.live="catalog.status"
                                    class="w-full bg-slate-50 border-0 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                                @foreach($statuses as $status)
                                    <option value="{{ $status->value }}">
                                        @php
                                            $labels = ['draft' => 'Entwurf', 'active' => 'Aktiv', 'archived' => 'Archiviert'];
                                        @endphp
                                        {{ $labels[$status->value] ?? $status->value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <x-ui-input-date
                            name="catalog.valid_from"
                            label="Gültig ab"
                            wire:model.live="catalog.valid_from"
                            :nullable="true"
                            :errorKey="'catalog.valid_from'"
                        />
                        <x-ui-input-date
                            name="catalog.valid_until"
                            label="Gültig bis"
                            wire:model.live="catalog.valid_until"
                            :nullable="true"
                            :errorKey="'catalog.valid_until'"
                        />
                    </div>
                </div>
            </x-ui-panel>
        </section>

        {{-- Sections --}}
        <section id="sections" class="scroll-mt-4">
            <x-ui-panel title="Sektionen" :subtitle="$catalog->sections->count() . ' Sektionen'">
                {{-- Create Section --}}
                <div class="mb-6 p-4 bg-slate-50 rounded-lg">
                    <h4 class="text-sm font-medium text-slate-700 mb-3">Neue Sektion</h4>
                    <form wire:submit="createSection" class="flex items-end gap-4">
                        <div class="flex-1">
                            <x-ui-input-text
                                name="sectionName"
                                label="Name"
                                wire:model="sectionName"
                                placeholder="z.B. Vorspeisen"
                                required
                            />
                        </div>
                        <div class="w-32">
                            <x-ui-input-number
                                name="sectionSortOrder"
                                label="Reihenfolge"
                                wire:model="sectionSortOrder"
                                min="0"
                            />
                        </div>
                        <x-ui-button type="submit" variant="primary" size="sm">
                            Hinzufügen
                        </x-ui-button>
                    </form>
                </div>

                {{-- Sections List --}}
                <div class="space-y-4">
                    @forelse($catalog->sections as $section)
                        <div class="p-4 rounded-lg border border-[var(--ui-border)] bg-white" wire:key="section-{{ $section->id }}">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <span class="text-xs text-[var(--ui-muted)] bg-slate-100 px-2 py-0.5 rounded">{{ $section->sort_order }}</span>
                                    <h4 class="font-semibold text-[var(--ui-secondary)]">{{ $section->name }}</h4>
                                </div>
                                <button wire:click="deleteSection({{ $section->id }})"
                                        wire:confirm="Sektion '{{ $section->name }}' wirklich löschen?"
                                        class="text-red-500 hover:text-red-700 text-sm">
                                    @svg('heroicon-o-trash', 'w-4 h-4')
                                </button>
                            </div>

                            {{-- Attached Boards --}}
                            <div class="ml-8">
                                @if($section->productBoards->count() > 0)
                                    <div class="space-y-2 mb-3">
                                        @foreach($section->productBoards as $board)
                                            <div class="flex items-center justify-between p-2 bg-slate-50 rounded text-sm">
                                                <div class="flex items-center gap-2">
                                                    @if($board->color)
                                                        <span class="w-3 h-3 rounded-full" style="background-color: {{ $board->color }}"></span>
                                                    @endif
                                                    <span>{{ $board->name }}</span>
                                                </div>
                                                <button wire:click="detachBoard({{ $section->id }}, {{ $board->id }})"
                                                        class="text-slate-400 hover:text-red-500 transition">
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
                                                class="flex-1 bg-slate-50 border-0 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-purple-500">
                                            <option value="">Board auswählen...</option>
                                            @foreach($unattached as $board)
                                                <option value="{{ $board->id }}">{{ $board->name }}</option>
                                            @endforeach
                                        </select>
                                        <button
                                            @click="
                                                const sel = document.getElementById('attach-board-{{ $section->id }}');
                                                if (sel.value) {
                                                    $wire.attachBoard({{ $section->id }}, parseInt(sel.value));
                                                    sel.value = '';
                                                }
                                            "
                                            class="px-3 py-1.5 bg-purple-600 text-white rounded-lg text-sm hover:bg-purple-700 transition-colors">
                                            @svg('heroicon-o-plus', 'w-4 h-4')
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-[var(--ui-muted)] bg-white rounded-md border border-[var(--ui-border)]">
                            <p>Noch keine Sektionen vorhanden.</p>
                        </div>
                    @endforelse
                </div>
            </x-ui-panel>
        </section>
    </x-ui-page-container>
</x-ui-page>
