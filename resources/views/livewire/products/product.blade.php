<x-ui-page>

    <x-slot name="navbar">
        <x-ui-page-navbar title="{{ $product->name }}">
            <x-slot name="actions">
                <div class="flex items-center gap-2">
                    <div class="p-1 bg-gray-50 rounded-lg ring-1 ring-gray-200">
                        <input type="color"
                               x-data="{ selectedColor: @entangle('product.color').live || '#000000' }"
                               x-model="selectedColor"
                               wire:model.live="product.color"
                               class="h-6 w-6 rounded cursor-pointer"
                               title="Produkt-Farbe ändern" />
                    </div>
                </div>
            </x-slot>
        </x-ui-page-navbar>
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Commerce', 'href' => route('commerce.index'), 'icon' => 'shopping-bag'],
            ['label' => 'Produkte', 'href' => route('commerce.products.index')],
            ['label' => $product->name],
        ]" />
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

                <div>
                    <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-4">Schnellübersicht</h3>
                    <div class="space-y-2">
                        @if($product->price_deviation_value)
                            <div class="p-3 rounded-md bg-blue-50 border border-blue-200">
                                <div class="text-[11px] text-blue-600 font-medium">Preisabweichung</div>
                                <div class="text-lg font-bold text-blue-800">
                                    {{ number_format($product->price_deviation_value, 2, ',', '.') }}
                                    {{ $product->price_deviation_type === 'relative' ? '%' : '€' }}
                                </div>
                            </div>
                        @endif
                        @if($product->productSlots->count() > 0)
                            <div class="p-3 rounded-md bg-green-50 border border-green-200">
                                <div class="text-[11px] text-green-600 font-medium">Produkt Slots</div>
                                <div class="text-[13px] font-medium text-green-800">{{ $product->productSlots->count() }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                <hr class="border-gray-200">

                <div>
                    <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-4">Produkt Info</h3>
                    <div class="space-y-2 text-[13px]">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Erstellt:</span>
                            <span class="font-medium text-gray-900">{{ $product->created_at->format('d.m.Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Geändert:</span>
                            <span class="font-medium text-gray-900">{{ $product->updated_at->format('d.m.Y H:i') }}</span>
                        </div>
                        @if($product->creator)
                            <div class="flex justify-between">
                                <span class="text-gray-500">Erstellt von:</span>
                                <span class="font-medium text-gray-900">{{ $product->creator->name ?? 'Unbekannt' }}</span>
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
                    :model="$product"
                    :key="get_class($product) . '_' . $product->id"
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
            <!-- Tabs Navigation -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="flex gap-1 flex-wrap">
                    <button x-on:click="scrollToSection('general')"
                            :class="{ 'border-[#166EE1] text-[#166EE1]': selectedTab === 'general', 'border-transparent text-gray-400 hover:text-gray-700': selectedTab !== 'general' }"
                            class="px-4 py-2 text-[13px] font-medium border-b-2 -mb-px transition-all duration-200">
                        Allgemein
                    </button>
                    <button x-on:click="scrollToSection('article')"
                            :class="{ 'border-[#166EE1] text-[#166EE1]': selectedTab === 'article', 'border-transparent text-gray-400 hover:text-gray-700': selectedTab !== 'article' }"
                            class="px-4 py-2 text-[13px] font-medium border-b-2 -mb-px transition-all duration-200">
                        Artikel
                    </button>
                    <button x-on:click="scrollToSection('price')"
                            :class="{ 'border-[#166EE1] text-[#166EE1]': selectedTab === 'price', 'border-transparent text-gray-400 hover:text-gray-700': selectedTab !== 'price' }"
                            class="px-4 py-2 text-[13px] font-medium border-b-2 -mb-px transition-all duration-200">
                        Produkt Konditionen
                    </button>
                    <button x-on:click="scrollToSection('additional')"
                            :class="{ 'border-[#166EE1] text-[#166EE1]': selectedTab === 'additional', 'border-transparent text-gray-400 hover:text-gray-700': selectedTab !== 'additional' }"
                            class="px-4 py-2 text-[13px] font-medium border-b-2 -mb-px transition-all duration-200">
                        Zusatz
                    </button>
                    <button x-on:click="scrollToSection('attachments')"
                            :class="{ 'border-[#166EE1] text-[#166EE1]': selectedTab === 'attachments', 'border-transparent text-gray-400 hover:text-gray-700': selectedTab !== 'attachments' }"
                            class="px-4 py-2 text-[13px] font-medium border-b-2 -mb-px transition-all duration-200">
                        Produktbilder
                    </button>
                </nav>
            </div>

            <!-- General Section -->
            <section id="general" class="scroll-mt-4 mb-8">
                <section class="bg-white rounded-lg border border-gray-200">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Allgemeine Einstellungen</h3>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[11px] font-medium text-gray-500 mb-1">Titel *</label>
                                    <input type="text" wire:model.live="product.name" placeholder="Produkttitel eingeben..." required
                                           class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                                    @error('product.name') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-[11px] font-medium text-gray-500 mb-1">Beschreibung</label>
                                    <textarea wire:model.live="product.description" rows="4" placeholder="Beschreiben Sie hier das Produkt..."
                                              class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]"></textarea>
                                    @error('product.description') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div class="space-y-4">
                                {{-- TODO: Context-Komponente einbinden wenn verfügbar --}}
                            </div>
                        </div>
                    </div>
                </section>
            </section>

            <!-- Artikel Section -->
            <section id="article" class="scroll-mt-4 mb-8">
                <section class="bg-white rounded-lg border border-gray-200">
                    <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900">Artikel</h3>
                        <button wire:click="createProductSlot"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-[#166EE1] text-white text-[13px] font-medium hover:bg-blue-700 transition-colors">
                            <x-heroicon-s-plus class="h-4 w-4"/>
                            Neuer Produkt Slot
                        </button>
                    </div>
                    <div class="p-4">
                        <div class="space-y-8">
                            @foreach ($product->productSlots as $slot)
                                <livewire:commerce.products.slot
                                    :productSlot="$slot"
                                    :product="$product"
                                    :key="'product-' . $product->id . '-slot-' . $slot->id"
                                />
                            @endforeach

                            @if($product->productSlots->isEmpty())
                                <div class="text-center py-12">
                                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-50 mb-4">
                                        <x-heroicon-s-squares-plus class="h-6 w-6 text-[#166EE1]"/>
                                    </div>
                                    <h3 class="text-[13px] font-medium text-gray-900 mb-1">Keine Produkt Slots vorhanden</h3>
                                    <p class="text-[13px] text-gray-500">Klicken Sie auf "Neuer Produkt Slot" um einen Slot anzulegen.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </section>
            </section>

            <!-- Preis Section -->
            <section id="price" class="scroll-mt-4 mb-8">
                <section class="bg-white rounded-lg border border-gray-200">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Produkt Konditionen</h3>
                    </div>
                    <div class="p-4">
                        <div class="max-w-2xl space-y-6">
                            <div>
                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Art der Preisabweichung</label>
                                <select wire:model.live="product.price_deviation_type"
                                        class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                                    <option value="absolute">Absoluter Betrag</option>
                                    <option value="relative">Prozentualer Wert</option>
                                </select>
                                @error('product.price_deviation_type') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-[11px] font-medium text-gray-500 mb-1">
                                    Wert der Preisabweichung
                                    @if($product->price_deviation_type === 'relative') (in %) @else (in €) @endif
                                </label>
                                <div class="relative">
                                    <input type="number" wire:model.live="product.price_deviation_value" step="0.01"
                                           class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <span class="text-[13px] text-gray-500">
                                            {{ $product->price_deviation_type === 'relative' ? '%' : '€' }}
                                        </span>
                                    </div>
                                </div>
                                @error('product.price_deviation_value') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                                <p class="mt-1 text-[11px] text-gray-500">
                                    @if($product->price_deviation_type === 'relative')
                                        Positiver Wert erhöht den Preis, negativer Wert reduziert ihn
                                    @else
                                        Wird zum Basis-Artikelpreis addiert (oder subtrahiert bei negativem Wert)
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </section>
            </section>

            <!-- Zusatz Section -->
            <section id="additional" class="scroll-mt-4 mb-8">
                <section class="bg-white rounded-lg border border-gray-200">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Zusatz</h3>
                    </div>
                    <div class="p-4">
                        <p class="text-[13px] text-gray-500">Zusatzfelder werden hier angezeigt, sobald die Komponenten verfügbar sind.</p>
                    </div>
                </section>
            </section>

            <!-- Produktbilder Section -->
            <section id="attachments" class="scroll-mt-4">
                <section class="bg-white rounded-lg border border-gray-200">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Produktbilder</h3>
                    </div>
                    <div class="p-4">
                        <p class="text-[13px] text-gray-500">Produktbilder werden hier angezeigt, sobald die Upload-Komponente verfügbar ist.</p>
                    </div>
                </section>
            </section>
        </div>
    </x-ui-page-container>
</x-ui-page>
