{{--
    AttributeSet Detail View
    Einzelnes Attributset bearbeiten und Items verwalten

    WICHTIG FÜR LLMs:
    - Zeigt Details eines Attributsets
    - Ermöglicht das Bearbeiten und Hinzufügen von Items
    - Verwendet moderne UI-Komponenten
--}}

<x-ui-page x-data="{
    createModalOpen: false,
    editModalOpen: false,
    confirmDeleteItem: null
}" @open-modal.window="createModalOpen = true; $wire.resetItemFields()">

    <x-slot name="navbar">
        <x-ui-page-navbar title="{{ $attributeSet->name }}" icon="heroicon-o-tag">
            <x-slot name="actions">
                <x-ui-button
                    variant="primary"
                    size="sm"
                    @click="createModalOpen = true"
                    class="d-flex items-center gap-2"
                >
                    <x-heroicon-s-plus class="w-4 h-4"/>
                    Item hinzufügen
                </x-ui-button>
            </x-slot>
        </x-ui-page-navbar>
    </x-slot>

    {{-- Actionbar mit Breadcrumbs --}}
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

                <hr class="border-[var(--ui-border)]">

                {{-- AttributeSet Info --}}
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-4">Attributset Info</h3>
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
                        <div class="flex justify-between">
                            <span class="text-[var(--ui-muted)]">Mehrfachauswahl:</span>
                            <span class="font-medium text-[var(--ui-secondary)]">{{ $attributeSet->is_multiselect ? 'Ja' : 'Nein' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[var(--ui-muted)]">Pflichtfeld:</span>
                            <span class="font-medium text-[var(--ui-secondary)]">{{ $attributeSet->is_required ? 'Ja' : 'Nein' }}</span>
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
        <x-ui-panel title="Attributset Details">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <x-ui-input-text
                    name="attributeSet.name"
                    label="Name"
                    wire:model.live="attributeSet.name"
                    required
                    :errorKey="'attributeSet.name'"
                />
                <div>
                    <label class="block text-sm font-medium text-[var(--ui-secondary)] mb-1">Farbe</label>
                    <input type="color"
                           wire:model.live="attributeSet.color"
                           class="w-full h-10 bg-[var(--ui-muted-5)] border-0 rounded-lg px-3 text-sm ring-1 ring-[var(--ui-border)] focus:ring-2 focus:ring-[var(--ui-primary)]">
                </div>
                <div class="flex flex-col justify-center">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox"
                               wire:model.live="attributeSet.is_multiselect"
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-[var(--ui-muted-10)] peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-[var(--ui-primary-light)] rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-[var(--ui-border)] after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[var(--ui-primary)]"></div>
                        <span class="ml-3 text-sm font-medium text-[var(--ui-secondary)]">Mehrfachauswahl</span>
                    </label>
                </div>
                <div class="flex flex-col justify-center">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox"
                               wire:model.live="attributeSet.is_required"
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-[var(--ui-muted-10)] peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-[var(--ui-primary-light)] rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-[var(--ui-border)] after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[var(--ui-primary)]"></div>
                        <span class="ml-3 text-sm font-medium text-[var(--ui-secondary)]">Pflichtfeld</span>
                    </label>
                </div>
            </div>
        </x-ui-panel>

        {{-- Items List --}}
        <x-ui-panel title="Items" :subtitle="$items->count() . ' Item(s)'">
            @if($items->count() > 0)
                <div class="divide-y divide-[var(--ui-border)]">
                    @foreach($items as $item)
                        <div class="py-3 flex items-center justify-between group">
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded-full flex-shrink-0" style="background-color: {{ $item->color ?? 'var(--ui-muted-10)' }};"></div>
                                <div>
                                    <span class="text-sm font-medium text-[var(--ui-secondary)]">{{ $item->name }}</span>
                                    @if($item->description)
                                        <p class="text-xs text-[var(--ui-muted)]">{{ $item->description }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button wire:click="$set('itemId', {{ $item->id }}); $set('itemName', '{{ addslashes($item->name) }}'); $set('itemDescription', '{{ addslashes($item->description ?? '') }}'); $set('itemColor', '{{ $item->color ?? '' }}')"
                                        @click="editModalOpen = true"
                                        class="p-1.5 rounded-lg text-[var(--ui-muted)] hover:bg-[var(--ui-muted-10)] hover:text-[var(--ui-secondary)] transition-all">
                                    <x-heroicon-s-pencil-square class="w-4 h-4"/>
                                </button>
                                <button @click="confirmDeleteItem = {{ $item->id }}"
                                        class="p-1.5 rounded-lg text-[var(--ui-muted)] hover:bg-red-50 hover:text-red-600 transition-all">
                                    <x-heroicon-s-trash class="w-4 h-4"/>
                                </button>
                            </div>
                        </div>

                        {{-- Inline Delete Confirmation --}}
                        <div x-show="confirmDeleteItem === {{ $item->id }}" x-cloak
                             class="py-2 px-3 bg-red-50 rounded-lg flex items-center justify-between">
                            <span class="text-sm text-red-700">Wirklich löschen?</span>
                            <div class="flex items-center gap-2">
                                <button @click="confirmDeleteItem = null"
                                        class="text-xs px-2 py-1 rounded bg-white text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)]">
                                    Abbrechen
                                </button>
                                <button wire:click="deleteItem({{ $item->id }})"
                                        @click="confirmDeleteItem = null"
                                        class="text-xs px-2 py-1 rounded bg-red-600 text-white hover:bg-red-700">
                                    Löschen
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 text-[var(--ui-muted)]">
                    <x-heroicon-o-square-3-stack-3d class="w-12 h-12 mx-auto mb-4 text-[var(--ui-muted-20)]"/>
                    <p>Keine Items vorhanden.</p>
                    <p class="text-sm mt-2">Klicken Sie auf "Item hinzufügen" um ein Item anzulegen.</p>
                </div>
            @endif
        </x-ui-panel>
    </x-ui-page-container>

    {{-- Create Item Modal --}}
    <div x-show="createModalOpen" x-cloak @keydown.escape.window="createModalOpen = false">
        <x-ui-modal size="md">
            <x-slot name="header">
                Neues Item erstellen
            </x-slot>

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
                    <label class="block text-sm font-medium text-[var(--ui-secondary)] mb-1">Farbe</label>
                    <input type="color"
                           wire:model="itemColor"
                           class="w-full h-10 bg-[var(--ui-muted-5)] border-0 rounded-lg px-3 text-sm ring-1 ring-[var(--ui-border)] focus:ring-2 focus:ring-[var(--ui-primary)]">
                </div>
            </div>

            <x-slot name="footer">
                <div class="flex justify-end gap-2">
                    <x-ui-button
                        type="button"
                        variant="secondary-outline"
                        @click="createModalOpen = false"
                    >
                        Abbrechen
                    </x-ui-button>
                    <x-ui-button
                        type="button"
                        variant="primary"
                        wire:click="createItem"
                        @click="createModalOpen = false"
                    >
                        Item erstellen
                    </x-ui-button>
                </div>
            </x-slot>
        </x-ui-modal>
    </div>

    {{-- Edit Item Modal --}}
    <div x-show="editModalOpen" x-cloak @keydown.escape.window="editModalOpen = false">
        <x-ui-modal size="md">
            <x-slot name="header">
                Item bearbeiten
            </x-slot>

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
                    <label class="block text-sm font-medium text-[var(--ui-secondary)] mb-1">Farbe</label>
                    <input type="color"
                           wire:model="itemColor"
                           class="w-full h-10 bg-[var(--ui-muted-5)] border-0 rounded-lg px-3 text-sm ring-1 ring-[var(--ui-border)] focus:ring-2 focus:ring-[var(--ui-primary)]">
                </div>
            </div>

            <x-slot name="footer">
                <div class="flex justify-end gap-2">
                    <x-ui-button
                        type="button"
                        variant="secondary-outline"
                        @click="editModalOpen = false"
                    >
                        Abbrechen
                    </x-ui-button>
                    <x-ui-button
                        type="button"
                        variant="primary"
                        wire:click="updateItem"
                        @click="editModalOpen = false"
                    >
                        Speichern
                    </x-ui-button>
                </div>
            </x-slot>
        </x-ui-modal>
    </div>
</x-ui-page>
