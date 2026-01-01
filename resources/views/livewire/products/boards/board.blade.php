<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="{{ $board->name }}">
            <x-slot name="actions">
                <div class="flex items-center gap-2">
                    <x-ui-button 
                        variant="primary" 
                        size="sm" 
                        wire:click="createProductBoardSlot"
                        class="d-flex items-center gap-2"
                    >
                        <x-heroicon-s-plus class="w-4 h-4"/>
                        Slot hinzufügen
                    </x-ui-button>
                </div>
            </x-slot>
        </x-ui-page-navbar>
    </x-slot>

    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Übersicht" width="w-80" :defaultOpen="true" storeKey="sidebarOpen" side="left">
            <div class="p-6 space-y-6">
                {{-- Navigation Buttons --}}
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-4">Navigation</h3>
                    <div class="space-y-2">
                        <x-ui-button 
                            variant="secondary-outline" 
                            size="sm" 
                            :href="route('commerce.products.index')" 
                            wire:navigate
                            class="w-full"
                        >
                            <span class="flex items-center gap-2">
                                @svg('heroicon-o-arrow-left', 'w-4 h-4')
                                Zur Produktübersicht
                            </span>
                        </x-ui-button>
                    </div>
                </div>

                <hr>

                {{-- Board Info --}}
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-4">Board Info</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-[var(--ui-muted)]">Erstellt:</span>
                            <span class="font-medium text-[var(--ui-secondary)]">{{ $board->created_at->format('d.m.Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[var(--ui-muted)]">Geändert:</span>
                            <span class="font-medium text-[var(--ui-secondary)]">{{ $board->updated_at->format('d.m.Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[var(--ui-muted)]">Slots:</span>
                            <span class="font-medium text-[var(--ui-secondary)]">{{ $board->productBoardSlots->count() }}</span>
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

    <x-ui-page-container>
        <div class="overflow-x-auto">
            <div class="flex gap-4 min-w-max pb-4" 
                 wire:sortable="updateProductBoardSlotOrder" 
                 wire:sortable-group="updateProductOrder" 
                 wire:sortable.options="{ animation: 300 }">
                @foreach($board->productBoardSlots as $slot)
                    <div wire:sortable.item="{{ $slot->id }}" 
                         wire:key="product-board-slot-wrapper-{{ $board->id }}-slot-{{$slot->id}}" 
                         class="flex-shrink-0 w-80">
                        <livewire:commerce.products.boards.slot 
                            :productBoardSlot="$slot" 
                            :key="'product-board-slot-id' . $slot->id" 
                            @deleted="$refresh"/>
                    </div>
                @endforeach
            </div>
        </div>
    </x-ui-page-container>
</x-ui-page>
