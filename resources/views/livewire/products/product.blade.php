<x-ui-page x-data="{ 
    selectedTab: 'general',
    selectedColor: @entangle('product.color').live || '#000000',
    scrollToSection(sectionId) {
        const section = document.getElementById(sectionId);
        if (section) {
            section.scrollIntoView({ behavior: 'smooth' });
        }
        this.selectedTab = sectionId;
    }
}">

    <x-slot name="navbar">
        <x-ui-page-navbar title="{{ $product->name }}">
            <x-slot name="actions">
                <div class="flex items-center gap-2">
                    <div class="p-1 bg-slate-50 rounded-lg ring-1 ring-slate-200/50">
                        <input type="color" 
                               x-model="selectedColor"
                               wire:model.live="product.color"
                               class="h-6 w-6 rounded cursor-pointer"
                               title="Produkt-Farbe ändern" />
                    </div>
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

                {{-- Quick Stats --}}
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-4">Schnellübersicht</h3>
                    <div class="space-y-3">
                        @if($product->price_deviation_value)
                            <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="text-xs text-blue-600 font-medium">Preisabweichung</div>
                                <div class="text-lg font-bold text-blue-800">
                                    {{ number_format($product->price_deviation_value, 2, ',', '.') }} 
                                    {{ $product->price_deviation_type === 'relative' ? '%' : '€' }}
                                </div>
                            </div>
                        @endif
                        @if($product->productSlots->count() > 0)
                            <div class="p-3 bg-green-50 border border-green-200 rounded-lg">
                                <div class="text-xs text-green-600 font-medium">Produkt Slots</div>
                                <div class="text-sm font-medium text-green-800">{{ $product->productSlots->count() }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                <hr>

                {{-- Product Info --}}
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-4">Produkt Info</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-[var(--ui-muted)]">Erstellt:</span>
                            <span class="font-medium text-[var(--ui-secondary)]">{{ $product->created_at->format('d.m.Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[var(--ui-muted)]">Geändert:</span>
                            <span class="font-medium text-[var(--ui-secondary)]">{{ $product->updated_at->format('d.m.Y H:i') }}</span>
                        </div>
                        @if($product->creator)
                            <div class="flex justify-between">
                                <span class="text-[var(--ui-muted)]">Erstellt von:</span>
                                <span class="font-medium text-[var(--ui-secondary)]">{{ $product->creator->name ?? 'Unbekannt' }}</span>
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
        <!-- Tabs Navigation -->
        <div class="border-b border-[var(--ui-border)]/60 mb-6">
            <nav class="flex gap-1 flex-wrap">
                <button @click="scrollToSection('general')" 
                        :class="{ 'border-b-2 border-[var(--ui-primary)] text-[var(--ui-primary)]': selectedTab === 'general', 'text-[var(--ui-muted)] hover:text-[var(--ui-primary)]': selectedTab !== 'general' }"
                        class="px-3 py-2 text-sm font-medium transition-all duration-200">
                    Allgemein
                </button>
                <button @click="scrollToSection('article')" 
                        :class="{ 'border-b-2 border-[var(--ui-primary)] text-[var(--ui-primary)]': selectedTab === 'article', 'text-[var(--ui-muted)] hover:text-[var(--ui-primary)]': selectedTab !== 'article' }"
                        class="px-3 py-2 text-sm font-medium transition-all duration-200">
                    Artikel
                </button>
                <button @click="scrollToSection('price')" 
                        :class="{ 'border-b-2 border-[var(--ui-primary)] text-[var(--ui-primary)]': selectedTab === 'price', 'text-[var(--ui-muted)] hover:text-[var(--ui-primary)]': selectedTab !== 'price' }"
                        class="px-3 py-2 text-sm font-medium transition-all duration-200">
                    Produkt Konditionen
                </button>
                <button @click="scrollToSection('additional')" 
                        :class="{ 'border-b-2 border-[var(--ui-primary)] text-[var(--ui-primary)]': selectedTab === 'additional', 'text-[var(--ui-muted)] hover:text-[var(--ui-primary)]': selectedTab !== 'additional' }"
                        class="px-3 py-2 text-sm font-medium transition-all duration-200">
                    Zusatz
                </button>
                <button @click="scrollToSection('attachments')" 
                        :class="{ 'border-b-2 border-[var(--ui-primary)] text-[var(--ui-primary)]': selectedTab === 'attachments', 'text-[var(--ui-muted)] hover:text-[var(--ui-primary)]': selectedTab !== 'attachments' }"
                        class="px-3 py-2 text-sm font-medium transition-all duration-200">
                    Produktbilder
                </button>
            </nav>
        </div>

        <!-- General Section -->
        <section id="general" class="scroll-mt-4">
            <x-ui-panel title="Allgemeine Einstellungen">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Basis Informationen -->
                    <div class="space-y-4">
                        <x-ui-input-text 
                            name="product.name"
                            label="Titel *"
                            wire:model.live="product.name"
                            placeholder="Produkttitel eingeben..."
                            required
                            :errorKey="'product.name'"
                        />
                        <x-ui-input-textarea 
                            name="product.description"
                            label="Beschreibung"
                            wire:model.live="product.description"
                            rows="4"
                            placeholder="Beschreiben Sie hier das Produkt..."
                            :errorKey="'product.description'"
                        />

                        <livewire:components.hero.index :model="$product" />
                    </div>

                    <!-- Context Section -->
                    <div class="space-y-4">
                        <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4">
                            <div class="flex items-center mb-2">
                                <x-heroicon-o-tag class="h-5 w-5 text-slate-400 mr-2"/>
                                <label class="text-sm font-medium text-slate-700">Kontext</label>
                            </div>
                            @livewire('components.context.index', ['model' => $product], key("product-context-" .$product->id))
                        </div>
                    </div>
                </div>
            </x-ui-panel>
        </section>

        <!-- Artikel Section -->
        <section id="article" class="scroll-mt-4">
            <x-ui-panel title="Artikel">
                <x-slot name="actions">
                    <x-ui-button 
                        variant="primary" 
                        size="sm" 
                        wire:click="createProductSlot"
                        class="d-flex items-center gap-2"
                    >
                        <x-heroicon-s-plus class="h-4 w-4"/>
                        Neuer Produkt Slot
                    </x-ui-button>
                </x-slot>

                <!-- Product Slots -->
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
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-purple-50 mb-4">
                                <x-heroicon-s-squares-plus class="h-6 w-6 text-purple-600"/>
                            </div>
                            <h3 class="text-sm font-medium text-slate-900 mb-1">Keine Produkt Slots vorhanden</h3>
                            <p class="text-sm text-slate-500">Klicken Sie auf "Neuer Produkt Slot" um einen Slot anzulegen.</p>
                        </div>
                    @endif
                </div>
            </x-ui-panel>
        </section>

        <!-- Preis Section -->
        <section id="price" class="scroll-mt-4">
            <x-ui-panel title="Produkt Konditionen">
                <div class="max-w-2xl space-y-6">
                    <x-ui-input-select
                        name="product.price_deviation_type"
                        label="Art der Preisabweichung"
                        :options="[
                            ['id' => 'absolute', 'name' => 'Absoluter Betrag'],
                            ['id' => 'relative', 'name' => 'Prozentualer Wert'],
                        ]"
                        optionValue="id"
                        optionLabel="name"
                        wire:model.live="product.price_deviation_type"
                        :errorKey="'product.price_deviation_type'"
                    />

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Wert der Preisabweichung
                            @if($product->price_deviation_type === 'relative')
                                (in %)
                            @else
                                (in €)
                            @endif
                        </label>
                        <div class="relative">
                            <x-ui-input-number 
                                name="product.price_deviation_value"
                                wire:model.live="product.price_deviation_value"
                                step="0.01"
                                :errorKey="'product.price_deviation_value'"
                            />
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <span class="text-sm text-slate-500">
                                    {{ $product->price_deviation_type === 'relative' ? '%' : '€' }}
                                </span>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">
                            @if($product->price_deviation_type === 'relative')
                                Positiver Wert erhöht den Preis, negativer Wert reduziert ihn
                            @else
                                Wird zum Basis-Artikelpreis addiert (oder subtrahiert bei negativem Wert)
                            @endif
                        </p>
                    </div>
                </div>
            </x-ui-panel>
        </section>

        <!-- Zusatz Section -->
        <section id="additional" class="scroll-mt-4">
            <x-ui-panel title="Zusatz">
                <div class="space-y-6">
                    <livewire:components.toolbox.index :model="$product" />
                    <livewire:components.custom-fields.index :model="$product" />
                </div>
            </x-ui-panel>
        </section>

        <!-- Produktbilder Section -->
        <section id="attachments" class="scroll-mt-4">
            <x-ui-panel title="Produktbilder">
                <livewire:components.uploads.index :model="$product" />
            </x-ui-panel>
        </section>
    </x-ui-page-container>
</x-ui-page>
