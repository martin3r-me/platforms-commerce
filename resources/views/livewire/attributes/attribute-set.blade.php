{{--
    AttributeSet Detail View
    Einzelnes Attributset bearbeiten und Items verwalten
--}}

<x-ui-page>
    <div x-data="{
        createModalOpen: false,
        editModalOpen: false,
        confirmDeleteItem: null
    }" x-on:open-modal.window="createModalOpen = true; $wire.resetItemFields()">

    <x-slot name="navbar">
        <x-ui-page-navbar title="{{ $attributeSet->name }}" icon="heroicon-o-tag">
            <x-slot name="actions">
                <button x-on:click="createModalOpen = true"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-[#166EE1] text-white text-[13px] font-medium hover:bg-blue-700 transition-colors">
                    <x-heroicon-s-plus class="w-4 h-4"/>
                    Item hinzufügen
                </button>
            </x-slot>
        </x-ui-page-navbar>
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Commerce', 'href' => route('commerce.index'), 'icon' => 'shopping-bag'],
            ['label' => 'Attribute', 'href' => route('commerce.attributes.index')],
            ['label' => $attributeSet->name],
        ]" />
    </x-slot>

    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Übersicht" width="w-80" :defaultOpen="true" storeKey="sidebarOpen" side="left">
            <div class="p-6 space-y-6">
                <div>
                    <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-4">Navigation</h3>
                    <div class="space-y-1">
                        <a href="{{ route('commerce.attributes.index') }}" wire:navigate
                           class="flex items-center gap-2 px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 text-[13px] font-medium hover:bg-gray-50 transition-colors w-full">
                            @svg('heroicon-o-arrow-left', 'w-4 h-4')
                            Zur Attributübersicht
                        </a>
                    </div>
                </div>

                <hr class="border-gray-200">

                <div>
                    <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-4">Attributset Info</h3>
                    <div class="space-y-2 text-[13px]">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Erstellt:</span>
                            <span class="font-medium text-gray-900">{{ $attributeSet->created_at->format('d.m.Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Geändert:</span>
                            <span class="font-medium text-gray-900">{{ $attributeSet->updated_at->format('d.m.Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Items:</span>
                            <span class="font-medium text-gray-900">{{ $items->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Mehrfachauswahl:</span>
                            <span class="font-medium text-gray-900">{{ $attributeSet->is_multiselect ? 'Ja' : 'Nein' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Pflichtfeld:</span>
                            <span class="font-medium text-gray-900">{{ $attributeSet->is_required ? 'Ja' : 'Nein' }}</span>
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
        {{-- AttributeSet Details --}}
        <section class="bg-white rounded-lg border border-gray-200">
            <div class="px-4 py-3 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900">Attributset Details</h3>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Name</label>
                        <input type="text" wire:model.live="attributeSet.name" required
                               class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                        @error('attributeSet.name') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Farbe</label>
                        <input type="color" wire:model.live="attributeSet.color"
                               class="w-full h-10 bg-white border border-gray-300 rounded-md px-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                    </div>
                    <div class="flex flex-col justify-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model.live="attributeSet.is_multiselect" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-[#166EE1]/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#166EE1]"></div>
                            <span class="ml-3 text-[13px] font-medium text-gray-900">Mehrfachauswahl</span>
                        </label>
                    </div>
                    <div class="flex flex-col justify-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model.live="attributeSet.is_required" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-[#166EE1]/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#166EE1]"></div>
                            <span class="ml-3 text-[13px] font-medium text-gray-900">Pflichtfeld</span>
                        </label>
                    </div>
                </div>
            </div>
        </section>

        {{-- Items List --}}
        <section class="bg-white rounded-lg border border-gray-200">
            <div class="px-4 py-3 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900">Items</h3>
                <p class="text-[11px] text-gray-500">{{ $items->count() }} Item(s)</p>
            </div>
            <div class="p-4">
                @if($items->count() > 0)
                    <div class="divide-y divide-gray-100">
                        @foreach($items as $item)
                            <div class="py-3 flex items-center justify-between group">
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 rounded-full flex-shrink-0" style="background-color: {{ $item->color ?? '#e5e7eb' }};"></div>
                                    <div>
                                        <span class="text-[13px] font-medium text-gray-900">{{ $item->name }}</span>
                                        @if($item->description)
                                            <p class="text-[11px] text-gray-500">{{ $item->description }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button wire:click="$set('itemId', {{ $item->id }}); $set('itemName', '{{ addslashes($item->name) }}'); $set('itemDescription', '{{ addslashes($item->description ?? '') }}'); $set('itemColor', '{{ $item->color ?? '' }}')"
                                            x-on:click="editModalOpen = true"
                                            class="p-1.5 rounded-md text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                                        <x-heroicon-s-pencil-square class="w-4 h-4"/>
                                    </button>
                                    <button x-on:click="confirmDeleteItem = {{ $item->id }}"
                                            class="p-1.5 rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                        <x-heroicon-s-trash class="w-4 h-4"/>
                                    </button>
                                </div>
                            </div>

                            {{-- Inline Delete Confirmation --}}
                            <div x-show="confirmDeleteItem === {{ $item->id }}" x-cloak
                                 class="py-2 px-3 bg-red-50 rounded-lg flex items-center justify-between">
                                <span class="text-[13px] text-red-700">Wirklich löschen?</span>
                                <div class="flex items-center gap-2">
                                    <button x-on:click="confirmDeleteItem = null"
                                            class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md border border-gray-300 bg-white text-gray-700 text-[11px] font-medium hover:bg-gray-50 transition-colors">
                                        Abbrechen
                                    </button>
                                    <button wire:click="deleteItem({{ $item->id }})"
                                            x-on:click="confirmDeleteItem = null"
                                            class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-red-600 text-white text-[11px] font-medium hover:bg-red-700 transition-colors">
                                        Löschen
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12 text-gray-500">
                        <x-heroicon-o-square-3-stack-3d class="w-12 h-12 mx-auto mb-4 text-gray-300"/>
                        <p class="text-[13px]">Keine Items vorhanden.</p>
                        <p class="text-[13px] mt-2">Klicken Sie auf "Item hinzufügen" um ein Item anzulegen.</p>
                    </div>
                @endif
            </div>
        </section>
    </x-ui-page-container>

    {{-- Create Item Modal --}}
    <div x-show="createModalOpen" x-cloak x-on:keydown.escape.window="createModalOpen = false">
        <x-ui-modal size="md">
            <x-slot name="header">Neues Item erstellen</x-slot>
            <div class="space-y-4">
                <div>
                    <label class="block text-[11px] font-medium text-gray-500 mb-1">Name</label>
                    <input type="text" wire:model="itemName" required
                           class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                    @error('itemName') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-gray-500 mb-1">Beschreibung</label>
                    <textarea wire:model="itemDescription" rows="3"
                              class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]"></textarea>
                    @error('itemDescription') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-gray-500 mb-1">Farbe</label>
                    <input type="color" wire:model="itemColor"
                           class="w-full h-10 bg-white border border-gray-300 rounded-md px-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                </div>
            </div>
            <x-slot name="footer">
                <div class="flex justify-end gap-2">
                    <button type="button" x-on:click="createModalOpen = false"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 text-[13px] font-medium hover:bg-gray-50 transition-colors">
                        Abbrechen
                    </button>
                    <button type="button" wire:click="createItem" x-on:click="createModalOpen = false"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-[#166EE1] text-white text-[13px] font-medium hover:bg-blue-700 transition-colors">
                        Item erstellen
                    </button>
                </div>
            </x-slot>
        </x-ui-modal>
    </div>

    {{-- Edit Item Modal --}}
    <div x-show="editModalOpen" x-cloak x-on:keydown.escape.window="editModalOpen = false">
        <x-ui-modal size="md">
            <x-slot name="header">Item bearbeiten</x-slot>
            <div class="space-y-4">
                <div>
                    <label class="block text-[11px] font-medium text-gray-500 mb-1">Name</label>
                    <input type="text" wire:model="itemName" required
                           class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                    @error('itemName') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-gray-500 mb-1">Beschreibung</label>
                    <textarea wire:model="itemDescription" rows="3"
                              class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]"></textarea>
                    @error('itemDescription') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-gray-500 mb-1">Farbe</label>
                    <input type="color" wire:model="itemColor"
                           class="w-full h-10 bg-white border border-gray-300 rounded-md px-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                </div>
            </div>
            <x-slot name="footer">
                <div class="flex justify-end gap-2">
                    <button type="button" x-on:click="editModalOpen = false"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 text-[13px] font-medium hover:bg-gray-50 transition-colors">
                        Abbrechen
                    </button>
                    <button type="button" wire:click="updateItem" x-on:click="editModalOpen = false"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-[#166EE1] text-white text-[13px] font-medium hover:bg-blue-700 transition-colors">
                        Speichern
                    </button>
                </div>
            </x-slot>
        </x-ui-modal>
    </div>

    </div>
</x-ui-page>
