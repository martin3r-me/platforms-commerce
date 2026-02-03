{{--
    Product Slot View
    Vollständige Slot-Konfiguration mit Dimensionen, Varianten und Abhängigkeiten

    WICHTIG FÜR LLMs:
    - Slots sind konfigurierbare Produktbestandteile
    - Dimensionen definieren Varianten (z.B. Größe: S, M, L)
    - Varianten-Matrix verknüpft Kombinationen mit Artikeln
--}}

<div class="bg-white rounded-xl shadow-sm ring-1 ring-[var(--ui-border)] p-6 space-y-6">
    {{-- Slot Konfiguration Header --}}
    <div class="flex items-center justify-between border-b border-[var(--ui-border)]/60 pb-4">
        <div class="flex items-center gap-2">
            @svg('heroicon-s-cube', 'h-5 w-5 text-[var(--ui-muted)]')
            <h3 class="text-base font-medium text-[var(--ui-secondary)]">Slot Konfiguration</h3>
        </div>
    </div>

    {{-- Varianten Abhängigkeiten --}}
    @if($product && count($availableVariants) > 0)
        <div class="space-y-4">
            <div class="flex items-center gap-2 mb-2">
                @svg('heroicon-s-arrow-path', 'h-4 w-4 text-[var(--ui-muted)]')
                <h4 class="text-sm font-medium text-[var(--ui-secondary)]">Abhängigkeiten zu Varianten</h4>
            </div>

            <select
                wire:model.live="selectedVariant"
                wire:change="addDependency($event.target.value)"
                class="w-full bg-[var(--ui-muted-5)] border-0 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[var(--ui-primary)]"
            >
                <option value="">Variante auswählen</option>
                @foreach($availableVariants as $variant)
                    <option value="{{ $variant->id }}">{{ $variant->variant_name }}</option>
                @endforeach
            </select>

            {{-- Aktive Abhängigkeiten --}}
            <div class="flex flex-wrap gap-2">
                @foreach($dependencies as $dependency)
                    <span wire:click="removeDependency({{ $dependency->id }})"
                          class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-sm font-medium bg-[var(--ui-primary-light)] text-[var(--ui-primary)] hover:bg-[var(--ui-primary-light)]/80 cursor-pointer transition-colors">
                        {{ $dependency->variant->variant_name ?? 'Variante ' . $dependency->variant->id }}
                        @svg('heroicon-s-x-mark', 'h-4 w-4')
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Slot Einstellungen --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-4">
            {{-- Slot-Name --}}
            <div>
                <label class="block text-sm font-medium text-[var(--ui-secondary)] mb-1">Slot Name</label>
                <input type="text"
                       wire:model.live="productSlot.name"
                       placeholder="Slot Name eingeben"
                       class="w-full bg-[var(--ui-muted-5)] border-0 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[var(--ui-primary)]">
            </div>

            {{-- Slot-Beschreibung --}}
            <div>
                <label class="block text-sm font-medium text-[var(--ui-secondary)] mb-1">Slot Beschreibung</label>
                <textarea
                       wire:model.live="productSlot.description"
                       placeholder="Slot Beschreibung eingeben"
                       rows="3"
                       class="w-full bg-[var(--ui-muted-5)] border-0 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[var(--ui-primary)]"></textarea>
            </div>

            {{-- Auswahlgrenzen --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[var(--ui-secondary)] mb-1">Min. Auswahl</label>
                    <input type="number"
                           wire:model.live="productSlot.min_selection"
                           class="w-full bg-[var(--ui-muted-5)] border-0 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[var(--ui-primary)]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[var(--ui-secondary)] mb-1">Max. Auswahl</label>
                    <input type="number"
                           wire:model.live="productSlot.max_selection"
                           class="w-full bg-[var(--ui-muted-5)] border-0 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[var(--ui-primary)]">
                </div>
            </div>
        </div>

        {{-- Slot-Eigenschaften --}}
        <div class="bg-[var(--ui-muted-5)] rounded-lg p-4">
            <h4 class="text-sm font-medium text-[var(--ui-secondary)] mb-3">Eigenschaften</h4>
            <div class="space-y-3">
                <label class="flex items-center gap-2">
                    <input type="checkbox"
                           wire:model.live="productSlot.required"
                           class="rounded border-[var(--ui-border)] text-[var(--ui-primary)] focus:ring-[var(--ui-primary)]">
                    <span class="text-sm text-[var(--ui-secondary)]">Pflichtfeld</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox"
                           wire:model.live="productSlot.multi_select"
                           class="rounded border-[var(--ui-border)] text-[var(--ui-primary)] focus:ring-[var(--ui-primary)]">
                    <span class="text-sm text-[var(--ui-secondary)]">Mehrfachauswahl</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox"
                           wire:model.live="productSlot.active"
                           class="rounded border-[var(--ui-border)] text-[var(--ui-primary)] focus:ring-[var(--ui-primary)]">
                    <span class="text-sm text-[var(--ui-secondary)]">Aktiv</span>
                </label>
            </div>
        </div>
    </div>

    {{-- Dimensionen --}}
    <div class="border-t border-[var(--ui-border)]/60 pt-6 space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                @svg('heroicon-s-squares-2x2', 'h-5 w-5 text-[var(--ui-muted)]')
                <h3 class="text-base font-medium text-[var(--ui-secondary)]">Dimensionen</h3>
            </div>
            <div class="flex gap-2">
                <input type="text"
                       wire:model.live="newDimensionName"
                       placeholder="Neue Dimension"
                       class="bg-[var(--ui-muted-5)] border-0 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[var(--ui-primary)]">
                <x-ui-button wire:click="addDimension({{ $productSlot->id }})" variant="primary" size="sm">
                    Hinzufügen
                </x-ui-button>
            </div>
        </div>

        <div class="grid gap-4">
            @foreach ($productSlot->dimensions as $dimension)
                <div wire:key="dimension-{{ $dimension->id }}"
                     class="bg-white rounded-lg shadow-sm ring-1 ring-[var(--ui-border)] p-4">
                    <div class="flex items-center justify-between mb-3">
                        <span class="font-medium text-[var(--ui-secondary)]">{{ $dimension->name }}</span>
                        <button wire:click="removeDimension({{ $dimension->id }})"
                                class="p-1 text-[var(--ui-muted)] hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                title="Dimension entfernen">
                            @svg('heroicon-s-trash', 'h-4 w-4')
                        </button>
                    </div>

                    <div class="space-y-2">
                        @foreach ($dimension->values as $value)
                            <div wire:key="dimension-value-{{ $value->id }}"
                                 class="flex items-center justify-between bg-[var(--ui-muted-5)] rounded px-3 py-2">
                                <span class="text-sm text-[var(--ui-secondary)]">{{ $value->value }}</span>
                                <button wire:click="removeDimensionValue({{ $value->id }})"
                                        class="text-sm text-red-600 hover:text-red-700">
                                    Entfernen
                                </button>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex gap-2 mt-3">
                        <input type="text"
                               wire:model.live="dimensionValues.{{ $dimension->id }}"
                               placeholder="Neuer Wert"
                               class="flex-1 bg-[var(--ui-muted-5)] border-0 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[var(--ui-primary)]">
                        <x-ui-button wire:click="addDimensionValue({{ $dimension->id }})" variant="primary" size="sm">
                            Hinzufügen
                        </x-ui-button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Varianten-Matrix --}}
    @if($productSlot->variants->count() > 0)
        <div class="border-t border-[var(--ui-border)]/60 pt-6 space-y-4">
            <div class="flex items-center gap-2">
                @svg('heroicon-s-table-cells', 'h-5 w-5 text-[var(--ui-muted)]')
                <h3 class="text-base font-medium text-[var(--ui-secondary)]">Varianten-Matrix</h3>
            </div>

            <div class="space-y-4">
                @foreach ($productSlot->variants as $variant)
                    <livewire:commerce.products.matrix-row
                        :variant="$variant"
                        :articles="$articles"
                        :key="'productslot-' . $productSlot->id . '-variant-' . $variant->id"
                    />
                @endforeach
            </div>
        </div>
    @endif
</div>
