<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="{{ $board->name }}">
            <x-slot name="actions">
                <div class="flex items-center gap-2">
                    <button wire:click="createProductBoardSlot"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-[#166EE1] text-white text-[13px] font-medium hover:bg-blue-700 transition-colors">
                        <x-heroicon-s-plus class="w-4 h-4"/>
                        Slot hinzufügen
                    </button>
                </div>
            </x-slot>
        </x-ui-page-navbar>
    </x-slot>

    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Übersicht" width="w-80" :defaultOpen="true" storeKey="sidebarOpen" side="left">
            <div class="p-6 space-y-6">
                <div>
                    <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-4">Navigation</h3>
                    <div class="space-y-1">
                        <a href="{{ route('commerce.products.index') }}" wire:navigate
                           class="flex items-center gap-2 px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 text-[13px] font-medium hover:bg-gray-50 transition-colors w-full">
                            @svg('heroicon-o-arrow-left', 'w-4 h-4')
                            Zur Produktübersicht
                        </a>
                    </div>
                </div>

                <hr class="border-gray-200">

                <div>
                    <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-4">Board Info</h3>
                    <div class="space-y-2 text-[13px]">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Erstellt:</span>
                            <span class="font-medium text-gray-900">{{ $board->created_at->format('d.m.Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Geändert:</span>
                            <span class="font-medium text-gray-900">{{ $board->updated_at->format('d.m.Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Slots:</span>
                            <span class="font-medium text-gray-900">{{ $board->productBoardSlots->count() }}</span>
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
