<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar :title="$board->name" icon="heroicon-o-view-columns" />
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Commerce', 'href' => route('commerce.index'), 'icon' => 'shopping-bag'],
            ['label' => 'Boards', 'href' => route('commerce.products.boards.index')],
            ['label' => $board->name],
        ]">
            <x-ui-button variant="primary" size="sm" wire:click="createProductBoardSlot">
                @svg('heroicon-o-plus', 'w-4 h-4')
                <span>Slot hinzufügen</span>
            </x-ui-button>
        </x-ui-page-actionbar>
    </x-slot>

    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Board-Übersicht" width="w-80" :defaultOpen="true" storeKey="sidebarOpen" side="left">
            <div class="p-6 space-y-6">
                <div>
                    <h3 class="text-[11px] font-medium text-[var(--ui-muted)] uppercase tracking-wide mb-4">Navigation</h3>
                    <div class="space-y-1">
                        <a href="{{ route('commerce.products.boards.index') }}" wire:navigate
                           class="flex items-center gap-2 px-3 py-1.5 rounded-md text-[var(--ui-secondary)] text-[13px] font-medium hover:bg-[var(--ui-muted-5)] transition-colors w-full">
                            @svg('heroicon-o-arrow-left', 'w-4 h-4')
                            Zur Board-Übersicht
                        </a>
                    </div>
                </div>

                <hr class="border-[var(--ui-border)]">

                <div>
                    <h3 class="text-[11px] font-medium text-[var(--ui-muted)] uppercase tracking-wide mb-4">Board Info</h3>
                    <div class="space-y-2 text-[13px]">
                        <div class="flex justify-between py-2 px-3 bg-[var(--ui-muted-5)] border border-[var(--ui-border)]/40">
                            <span class="text-[var(--ui-muted)]">Slots</span>
                            <span class="font-medium text-[var(--ui-secondary)]">{{ $board->productBoardSlots->count() }}</span>
                        </div>
                        <div class="flex justify-between py-2 px-3 bg-[var(--ui-muted-5)] border border-[var(--ui-border)]/40">
                            <span class="text-[var(--ui-muted)]">Produkte</span>
                            <span class="font-medium text-[var(--ui-secondary)]">{{ $board->productBoardSlots->sum(fn($s) => $s->products->count()) }}</span>
                        </div>
                        <div class="flex justify-between py-2 px-3 bg-[var(--ui-muted-5)] border border-[var(--ui-border)]/40">
                            <span class="text-[var(--ui-muted)]">Erstellt</span>
                            <span class="font-medium text-[var(--ui-secondary)]">{{ $board->created_at->format('d.m.Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    <x-slot name="activity">
        <x-ui-page-sidebar title="Aktivitäten" width="w-80" :defaultOpen="false" storeKey="activityOpen" side="right">
            <div class="p-4">
                <livewire:activity-log.index
                    :model="$board"
                    :key="get_class($board) . '_' . $board->id"
                />
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    @if($board->productBoardSlots->count() > 0)
        <x-ui-kanban-container sortable="updateProductBoardSlotOrder" sortable-group="updateProductOrder">
            @foreach($board->productBoardSlots as $slot)
                <x-ui-kanban-column :title="$slot->name ?? 'Slot'" :sortable-id="$slot->id" :scrollable="true">
                    <x-slot name="headerActions">
                        <button
                            wire:click="$dispatch('createProductInSlot', { slotId: {{ $slot->id }} })"
                            class="text-[var(--ui-muted)] hover:text-[var(--ui-secondary)] transition-colors"
                            title="Neues Produkt"
                        >
                            @svg('heroicon-o-plus-circle', 'w-4 h-4')
                        </button>
                    </x-slot>

                    @foreach($slot->products as $product)
                        <x-ui-kanban-card
                            :title="$product->name"
                            :sortable-id="$product->id"
                            :href="route('commerce.products.show', $product)"
                        >
                            @if($product->article)
                                <div class="text-xs text-[var(--ui-muted)]">
                                    @svg('heroicon-o-rectangle-stack', 'w-3 h-3 inline')
                                    {{ $product->article->name }}
                                </div>
                            @endif
                            @if($product->description)
                                <div class="text-xs text-[var(--ui-muted)] truncate mt-1">{{ Str::limit($product->description, 80) }}</div>
                            @endif
                            <x-slot name="footer">
                                <div class="flex items-center justify-between text-xs text-[var(--ui-muted)]">
                                    @if($product->price_deviation_value)
                                        <span>{{ number_format($product->price_deviation_value, 2, ',', '.') }}{{ $product->price_deviation_type === 'relative' ? '%' : '€' }}</span>
                                    @else
                                        <span></span>
                                    @endif
                                    @if($product->productSlots && $product->productSlots->count() > 0)
                                        <span>{{ $product->productSlots->count() }} Slot(s)</span>
                                    @endif
                                </div>
                            </x-slot>
                        </x-ui-kanban-card>
                    @endforeach
                </x-ui-kanban-column>
            @endforeach
        </x-ui-kanban-container>
    @else
        <div class="flex flex-col items-center justify-center py-20">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-[var(--ui-primary-5)] mb-4">
                @svg('heroicon-o-view-columns', 'w-7 h-7 text-[var(--ui-primary)]')
            </div>
            <h3 class="text-sm font-medium text-[var(--ui-secondary)] mb-1">Noch keine Slots vorhanden</h3>
            <p class="text-sm text-[var(--ui-muted)] mb-4">Erstelle deinen ersten Slot, um Produkte auf diesem Board zu organisieren.</p>
            <x-ui-button variant="primary" size="sm" wire:click="createProductBoardSlot">
                @svg('heroicon-o-plus', 'w-4 h-4')
                <span>Ersten Slot erstellen</span>
            </x-ui-button>
        </div>
    @endif
</x-ui-page>
