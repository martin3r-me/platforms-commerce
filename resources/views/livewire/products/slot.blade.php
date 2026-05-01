{{--
    Product Slot View
    Vollständige Slot-Konfiguration mit Dimensionen, Varianten und Abhängigkeiten

    WICHTIG FÜR LLMs:
    - Slots sind konfigurierbare Produktbestandteile
    - Dimensionen definieren Varianten (z.B. Größe: S, M, L)
    - Varianten-Matrix verknüpft Kombinationen mit Artikeln
--}}

<div class="bg-white rounded-lg border border-gray-200 p-6 space-y-6">
    {{-- Slot Konfiguration Header --}}
    <div class="flex items-center justify-between border-b border-gray-200 pb-4">
        <div class="flex items-center gap-2">
            @svg('heroicon-s-cube', 'h-5 w-5 text-gray-400')
            <h3 class="text-sm font-semibold text-gray-900">Slot Konfiguration</h3>
        </div>
    </div>

    {{-- Varianten Abhängigkeiten --}}
    @if($product && count($availableVariants) > 0)
        <div class="space-y-4">
            <div class="flex items-center gap-2 mb-2">
                @svg('heroicon-s-arrow-path', 'h-4 w-4 text-gray-400')
                <h4 class="text-[13px] font-medium text-gray-900">Abhängigkeiten zu Varianten</h4>
            </div>

            <select
                wire:model.live="selectedVariant"
                wire:change="addDependency($event.target.value)"
                class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]"
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
                          class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-[13px] font-medium bg-blue-50 text-[#166EE1] hover:bg-blue-100 cursor-pointer transition-colors">
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
                <label class="block text-[11px] font-medium text-gray-500 mb-1">Slot Name</label>
                <input type="text"
                       wire:model.live="productSlot.name"
                       placeholder="Slot Name eingeben"
                       class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
            </div>

            {{-- Slot-Beschreibung --}}
            <div>
                <label class="block text-[11px] font-medium text-gray-500 mb-1">Slot Beschreibung</label>
                <textarea
                       wire:model.live="productSlot.description"
                       placeholder="Slot Beschreibung eingeben"
                       rows="3"
                       class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]"></textarea>
            </div>

            {{-- Auswahlgrenzen --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] font-medium text-gray-500 mb-1">Min. Auswahl</label>
                    <input type="number"
                           wire:model.live="productSlot.min_selection"
                           class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-gray-500 mb-1">Max. Auswahl</label>
                    <input type="number"
                           wire:model.live="productSlot.max_selection"
                           class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                </div>
            </div>
        </div>

        {{-- Slot-Eigenschaften --}}
        <div class="bg-gray-50 rounded-lg p-4">
            <h4 class="text-[13px] font-medium text-gray-900 mb-3">Eigenschaften</h4>
            <div class="space-y-3">
                <label class="flex items-center gap-2">
                    <input type="checkbox"
                           wire:model.live="productSlot.required"
                           class="rounded border-gray-300 text-[#166EE1] focus:ring-[#166EE1]">
                    <span class="text-[13px] text-gray-700">Pflichtfeld</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox"
                           wire:model.live="productSlot.multi_select"
                           class="rounded border-gray-300 text-[#166EE1] focus:ring-[#166EE1]">
                    <span class="text-[13px] text-gray-700">Mehrfachauswahl</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox"
                           wire:model.live="productSlot.active"
                           class="rounded border-gray-300 text-[#166EE1] focus:ring-[#166EE1]">
                    <span class="text-[13px] text-gray-700">Aktiv</span>
                </label>
            </div>
        </div>
    </div>

    {{-- Dimensionen --}}
    <div class="border-t border-gray-200 pt-6 space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                @svg('heroicon-s-squares-2x2', 'h-5 w-5 text-gray-400')
                <h3 class="text-sm font-semibold text-gray-900">Dimensionen</h3>
            </div>
            <div class="flex gap-2">
                <input type="text"
                       wire:model.live="newDimensionName"
                       placeholder="Neue Dimension"
                       class="px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                <button wire:click="addDimension({{ $productSlot->id }})"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-[#166EE1] text-white text-[13px] font-medium hover:bg-blue-700 transition-colors">
                    Hinzufügen
                </button>
            </div>
        </div>

        <div class="grid gap-4">
            @foreach ($productSlot->dimensions as $dimension)
                <div wire:key="dimension-{{ $dimension->id }}"
                     class="bg-white rounded-lg border border-gray-200 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[13px] font-medium text-gray-900">{{ $dimension->name }}</span>
                        <button wire:click="removeDimension({{ $dimension->id }})"
                                class="p-1.5 rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                title="Dimension entfernen">
                            @svg('heroicon-s-trash', 'h-4 w-4')
                        </button>
                    </div>

                    <div class="space-y-2">
                        @foreach ($dimension->values as $value)
                            <div wire:key="dimension-value-{{ $value->id }}"
                                 class="flex items-center justify-between bg-gray-50 rounded px-3 py-2">
                                <span class="text-[13px] text-gray-700">{{ $value->value }}</span>
                                <button wire:click="removeDimensionValue({{ $value->id }})"
                                        class="text-[13px] text-red-600 hover:text-red-700">
                                    Entfernen
                                </button>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex gap-2 mt-3">
                        <input type="text"
                               wire:model.live="dimensionValues.{{ $dimension->id }}"
                               placeholder="Neuer Wert"
                               class="flex-1 px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                        <button wire:click="addDimensionValue({{ $dimension->id }})"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-[#166EE1] text-white text-[13px] font-medium hover:bg-blue-700 transition-colors">
                            Hinzufügen
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Varianten-Matrix --}}
    @if($productSlot->variants->count() > 0)
        <div class="border-t border-gray-200 pt-6 space-y-4">
            <div class="flex items-center gap-2">
                @svg('heroicon-s-table-cells', 'h-5 w-5 text-gray-400')
                <h3 class="text-sm font-semibold text-gray-900">Varianten-Matrix</h3>
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
