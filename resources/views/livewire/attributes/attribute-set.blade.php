<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="{{ $attributeSet->name }}">
            <x-slot name="actions">
                <x-ui-button 
                    variant="primary" 
                    size="sm" 
                    @click="$dispatch('open-modal')"
                    class="d-flex items-center gap-2"
                >
                    <x-heroicon-s-plus class="w-4 h-4"/>
                    Item hinzufügen
                </x-ui-button>
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
                            :href="route('commerce.attributes.index')" 
                            wire:navigate
                            class="w-full"
                        >
                            <span class="flex items-center gap-2">
                                @svg('heroicon-o-arrow-left', 'w-4 h-4')
                                Zur Attributübersicht
                            </span>
                        </x-ui-button>
                    </div>
                </div>

                <hr>

                {{-- AttributeSet Info --}}
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-4">AttributSet Info</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-[var(--ui-muted)]">Erstellt:</span>
                            <span class="font-medium text-[var(--ui-secondary)]">{{ $attributeSet->created_at->format('d.m.Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[var(--ui-muted)]">Geändert:</span>
                            <span class="font-medium text-[var(--ui-secondary)]">{{ $attributeSet->updated_at->format('d.m.Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[var(--ui-muted)]">Items:</span>
                            <span class="font-medium text-[var(--ui-secondary)]">{{ $items->count() }}</span>
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
                    :model="$attributeSet"
                    :key="get_class($attributeSet) . '_' . $attributeSet->id"
                />
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    <x-ui-page-container spacing="space-y-8">
        <!-- AttributeSet Details -->
        <x-ui-panel title="AttributeSet Details">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <x-ui-input-text 
                    name="attributeSet.name"
                    label="Name"
                    wire:model.live="attributeSet.name"
                    required
                    :errorKey="'attributeSet.name'"
                />
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Farbe</label>
                    <input type="color"
                           wire:model.live="attributeSet.color"
                           class="w-full h-10 bg-slate-50 border-0 rounded-lg px-3 text-sm ring-1 ring-slate-200 focus:ring-2 focus:ring-purple-500">
                </div>
                <div class="flex flex-col justify-center">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox"
                               wire:model.live="attributeSet.is_multiselect"
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                        <span class="ml-3 text-sm font-medium text-slate-700">Mehrfachauswahl</span>
                    </label>
                </div>
                <div class="flex flex-col justify-center">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox"
                               wire:model.live="attributeSet.is_required"
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                        <span class="ml-3 text-sm font-medium text-slate-700">Pflichtfeld</span>
                    </label>
                </div>
            </div>
        </x-ui-panel>

        <!-- Items List -->
        <x-ui-panel title="Items">
            <x-ui-table compact="true">
                <x-ui-table-header>
                    <x-ui-table-header-cell compact="true">Name</x-ui-table-header-cell>
                    <x-ui-table-header-cell compact="true">Beschreibung</x-ui-table-header-cell>
                    <x-ui-table-header-cell compact="true">Farbe</x-ui-table-header-cell>
                    <x-ui-table-header-cell compact="true" class="text-right">Aktionen</x-ui-table-header-cell>
                </x-ui-table-header>
                
                <x-ui-table-body>
                    @forelse($items as $item)
                        <x-ui-table-row compact="true">
                            <x-ui-table-cell compact="true">
                                <span class="text-sm font-medium text-slate-900">{{ $item->name }}</span>
                            </x-ui-table-cell>
                            <x-ui-table-cell compact="true">
                                <span class="text-sm text-slate-600">{{ $item->description ?? '-' }}</span>
                            </x-ui-table-cell>
                            <x-ui-table-cell compact="true">
                                <div class="w-6 h-6 rounded-full" style="background-color: {{ $item->color ?? '#ccc' }};"></div>
                            </x-ui-table-cell>
                            <x-ui-table-cell compact="true" class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="$dispatch('open-edit-modal', { item: {{ json_encode($item) }} })"
                                            class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-all duration-200">
                                        <x-heroicon-s-pencil-square class="w-4 h-4"/>
                                    </button>
                                    <button wire:click="deleteItem({{ $item->id }})"
                                            class="p-1.5 rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600 transition-all duration-200">
                                        <x-heroicon-s-trash class="w-4 h-4"/>
                                    </button>
                                </div>
                            </x-ui-table-cell>
                        </x-ui-table-row>
                    @empty
                        <x-ui-table-row compact="true">
                            <x-ui-table-cell compact="true" colspan="4" class="text-center py-8">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <x-heroicon-o-square-3-stack-3d class="w-12 h-12 text-slate-300"/>
                                    <div class="text-sm text-slate-500">Keine Items gefunden</div>
                                </div>
                            </x-ui-table-cell>
                        </x-ui-table-row>
                    @endforelse
                </x-ui-table-body>
            </x-ui-table>
        </x-ui-panel>
    </x-ui-page-container>

    <!-- Create Item Modal -->
    <div x-data="{ open: false }" 
         @open-modal.window="open = true; $wire.resetItemFields()" 
         @keydown.escape.window="open = false"
         x-cloak>
        <x-ui-modal wire:model="open" size="md">
            <x-slot name="header">
                Neues Item erstellen
            </x-slot>

            <form wire:submit.prevent="createItem">
                <div class="space-y-4">
                    <x-ui-input-text 
                        name="itemName"
                        label="Name"
                        wire:model="itemName"
                        required
                        :errorKey="'itemName'"
                    />
                    <x-ui-input-textarea 
                        name="itemDescription"
                        label="Beschreibung"
                        wire:model="itemDescription"
                        rows="3"
                        :errorKey="'itemDescription'"
                    />
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Farbe</label>
                        <input type="color"
                               wire:model="itemColor"
                               class="w-full h-10 bg-slate-50 border-0 rounded-lg px-3 text-sm ring-1 ring-slate-200 focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>
            </form>

            <x-slot name="footer">
                <div class="d-flex justify-end gap-2">
                    <x-ui-button 
                        type="button" 
                        variant="secondary-outline" 
                        @click="open = false"
                    >
                        Abbrechen
                    </x-ui-button>
                    <x-ui-button 
                        type="button" 
                        variant="primary" 
                        wire:click="createItem"
                        @click="open = false"
                    >
                        Item erstellen
                    </x-ui-button>
                </div>
            </x-slot>
        </x-ui-modal>
    </div>

    <!-- Edit Item Modal -->
    <div x-data="{ 
            open: false,
            item: null
         }" 
         @open-edit-modal.window="open = true; item = $event.detail.item"
         @keydown.escape.window="open = false"
         x-cloak>
        <x-ui-modal wire:model="open" size="md">
            <x-slot name="header">
                Item bearbeiten
            </x-slot>

            <form wire:submit.prevent="updateItem" x-init="$watch('item', value => { 
                $wire.set('itemId', value.id);
                $wire.set('itemName', value.name);
                $wire.set('itemDescription', value.description);
                $wire.set('itemColor', value.color);
            })">
                <div class="space-y-4">
                    <x-ui-input-text 
                        name="itemName"
                        label="Name"
                        wire:model="itemName"
                        required
                        :errorKey="'itemName'"
                    />
                    <x-ui-input-textarea 
                        name="itemDescription"
                        label="Beschreibung"
                        wire:model="itemDescription"
                        rows="3"
                        :errorKey="'itemDescription'"
                    />
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Farbe</label>
                        <input type="color"
                               wire:model="itemColor"
                               class="w-full h-10 bg-slate-50 border-0 rounded-lg px-3 text-sm ring-1 ring-slate-200 focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>
            </form>

            <x-slot name="footer">
                <div class="d-flex justify-end gap-2">
                    <x-ui-button 
                        type="button" 
                        variant="secondary-outline" 
                        @click="open = false"
                    >
                        Abbrechen
                    </x-ui-button>
                    <x-ui-button 
                        type="button" 
                        variant="primary" 
                        wire:click="updateItem"
                        @click="open = false"
                    >
                        Speichern
                    </x-ui-button>
                </div>
            </x-slot>
        </x-ui-modal>
    </div>
</x-ui-page>
