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

                                    {{-- Attached Boards mit Slots & Products --}}
                                    <div class="ml-8">
                                        @if($section->productBoards->count() > 0)
                                            <div class="space-y-3 mb-3">
                                                @foreach($section->productBoards as $board)
                                                    <div class="rounded-md border border-gray-200 bg-white">
                                                        {{-- Board header --}}
                                                        <div class="flex items-center justify-between px-3 py-2 border-b border-gray-100 bg-gray-50 rounded-t-md">
                                                            <div class="flex items-center gap-2">
                                                                @if($board->color)
                                                                    <span class="w-3 h-3 rounded-full" style="background-color: {{ $board->color }}"></span>
                                                                @endif
                                                                <span class="text-[13px] font-medium text-gray-900">{{ $board->name }}</span>
                                                                @if($board->description)
                                                                    <span class="text-[11px] text-gray-400 truncate max-w-md">— {{ $board->description }}</span>
                                                                @endif
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <a href="{{ route('commerce.products.boards.show', $board) }}" wire:navigate
                                                                   class="text-[11px] text-[#166EE1] hover:underline">Kanban öffnen</a>
                                                                <button wire:click="detachBoard({{ $section->id }}, {{ $board->id }})"
                                                                        wire:confirm="Board '{{ $board->name }}' von Sektion '{{ $section->name }}' trennen?"
                                                                        class="p-1 rounded text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                                                    @svg('heroicon-o-x-mark', 'w-4 h-4')
                                                                </button>
                                                            </div>
                                                        </div>

                                                        {{-- Slots & Products --}}
                                                        <div class="p-3 space-y-3">
                                                            @forelse($board->productBoardSlots as $slot)
                                                                <div>
                                                                    <div class="flex items-center gap-2 mb-1.5">
                                                                        @if($slot->color)
                                                                            <span class="w-2 h-2 rounded-full" style="background-color: {{ $slot->color }}"></span>
                                                                        @endif
                                                                        <span class="text-[11px] uppercase tracking-wide text-gray-500 font-medium">{{ $slot->name ?? 'Slot' }}</span>
                                                                        <span class="text-[11px] text-gray-300">·</span>
                                                                        <span class="text-[11px] text-gray-400">{{ $slot->products->count() }} Produkt(e)</span>
                                                                    </div>
                                                                    @if($slot->products->count() > 0)
                                                                        <table class="w-full">
                                                                            <thead>
                                                                                <tr class="border-b border-gray-100">
                                                                                    <th class="text-left text-[10px] font-medium text-gray-400 uppercase py-1.5 px-2">Produkt</th>
                                                                                    <th class="text-left text-[10px] font-medium text-gray-400 uppercase py-1.5 px-2">Artikel</th>
                                                                                    <th class="text-right text-[10px] font-medium text-gray-400 uppercase py-1.5 px-2">VK</th>
                                                                                    <th class="text-right text-[10px] font-medium text-gray-400 uppercase py-1.5 px-2">EK</th>
                                                                                    <th class="text-right text-[10px] font-medium text-gray-400 uppercase py-1.5 px-2">Marge</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @foreach($slot->products as $product)
                                                                                    @php
                                                                                        $article = $product->article;
                                                                                        $unit = $article?->base_price_unit;
                                                                                        $vk = $product->selling_price;
                                                                                        $internalEk = $article?->internal_cost;
                                                                                        $extLink = $article?->suppliers?->first(fn($s) => (bool) ($s->pivot?->is_preferred))
                                                                                            ?? $article?->suppliers?->first();
                                                                                        $externalEk = $extLink?->pivot?->purchase_price !== null
                                                                                            ? (float) $extLink->pivot->purchase_price
                                                                                            : null;
                                                                                        $ek = $externalEk ?? $internalEk;
                                                                                        $ekSource = $externalEk !== null ? ($extLink?->name ?? 'extern') : ($article?->costStandard?->name);
                                                                                        $margin = ($vk !== null && $ek !== null) ? $vk - $ek : null;
                                                                                        $marginPct = ($margin !== null && $vk && $vk > 0) ? round(($margin / $vk) * 100) : null;
                                                                                    @endphp
                                                                                    <tr class="border-b border-gray-50 hover:bg-blue-50/30 transition-colors">
                                                                                        <td class="py-1.5 px-2">
                                                                                            <a href="{{ route('commerce.products.show', $product) }}" wire:navigate
                                                                                               class="text-[13px] text-gray-900 hover:text-[#166EE1]">{{ $product->name }}</a>
                                                                                        </td>
                                                                                        <td class="py-1.5 px-2">
                                                                                            @if($article)
                                                                                                <a href="{{ route('commerce.articles.show', $article) }}" wire:navigate
                                                                                                   class="text-[11px] text-gray-500 hover:text-[#166EE1] font-mono">{{ $article->sku }}</a>
                                                                                            @else
                                                                                                <span class="text-[11px] text-gray-300">—</span>
                                                                                            @endif
                                                                                        </td>
                                                                                        <td class="py-1.5 px-2 text-right text-[13px] text-gray-900 font-medium">
                                                                                            @if($vk !== null)
                                                                                                {{ number_format($vk, 2, ',', '.') }}&nbsp;€
                                                                                                @if($unit)
                                                                                                    <span class="text-[10px] text-gray-400">/&nbsp;{{ $unit }}</span>
                                                                                                @endif
                                                                                            @else
                                                                                                <span class="text-gray-300">—</span>
                                                                                            @endif
                                                                                        </td>
                                                                                        <td class="py-1.5 px-2 text-right text-[13px] text-gray-700">
                                                                                            @if($ek !== null)
                                                                                                {{ number_format($ek, 2, ',', '.') }}&nbsp;€
                                                                                                @if($ekSource)
                                                                                                    <div class="text-[10px] text-gray-400">{{ $ekSource }}</div>
                                                                                                @endif
                                                                                            @else
                                                                                                <span class="text-gray-300">—</span>
                                                                                            @endif
                                                                                        </td>
                                                                                        <td class="py-1.5 px-2 text-right text-[13px]">
                                                                                            @if($margin !== null)
                                                                                                <span class="font-medium {{ $margin >= 0 ? 'text-green-700' : 'text-red-600' }}">{{ number_format($margin, 2, ',', '.') }}&nbsp;€</span>
                                                                                                @if($marginPct !== null)
                                                                                                    <div class="text-[10px] text-gray-400">{{ $marginPct }}&nbsp;%</div>
                                                                                                @endif
                                                                                            @else
                                                                                                <span class="text-gray-300">—</span>
                                                                                            @endif
                                                                                        </td>
                                                                                    </tr>
                                                                                @endforeach
                                                                            </tbody>
                                                                        </table>
                                                                    @else
                                                                        <p class="text-[11px] text-gray-400 italic px-2">Keine Produkte in diesem Slot.</p>
                                                                    @endif
                                                                </div>
                                                            @empty
                                                                <p class="text-[11px] text-gray-400 italic">Dieses Board hat noch keine Slots.</p>
                                                            @endforelse
                                                        </div>
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
