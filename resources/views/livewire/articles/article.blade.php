<x-ui-page>

    <x-slot name="navbar">
        <x-ui-page-navbar title="{{ $article->name }}">
            <x-slot name="actions">
                <div class="flex items-center gap-2">
                    <div class="p-1 bg-slate-50 rounded-lg ring-1 ring-slate-200/50">
                        <input type="color"
                               wire:model.live="article.color"
                               class="h-6 w-6 rounded cursor-pointer"
                               title="Artikel-Farbe ändern" />
                    </div>
                </div>
            </x-slot>
        </x-ui-page-navbar>
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Commerce', 'href' => route('commerce.index'), 'icon' => 'shopping-bag'],
            ['label' => 'Artikel', 'href' => route('commerce.articles.index')],
            ['label' => $article->name],
        ]" />
    </x-slot>

    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Übersicht" width="w-80" :defaultOpen="true" storeKey="sidebarOpen" side="left">
            <div class="p-6 space-y-6">
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-4">Navigation</h3>
                    <div class="space-y-2">
                        <x-ui-button
                            variant="secondary-outline"
                            size="sm"
                            :href="route('commerce.articles.index')"
                            wire:navigate
                            class="w-full"
                        >
                            <span class="flex items-center gap-2">
                                @svg('heroicon-o-arrow-left', 'w-4 h-4')
                                Zur Artikelübersicht
                            </span>
                        </x-ui-button>
                    </div>
                </div>

                <hr>

                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-4">Artikel Info</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-[var(--ui-muted)]">Erstellt:</span>
                            <span class="font-medium text-[var(--ui-secondary)]">{{ $article->created_at->format('d.m.Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[var(--ui-muted)]">Geändert:</span>
                            <span class="font-medium text-[var(--ui-secondary)]">{{ $article->updated_at->format('d.m.Y H:i') }}</span>
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
                    :model="$article"
                    :key="get_class($article) . '_' . $article->id"
                />
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    <x-ui-page-container spacing="space-y-8">
      <div x-data="{
          selectedTab: 'general',
          showConfirmModal: false,
          confirmAction: null,
          confirmTitle: '',
          confirmMessage: '',
          handleConfirm() {
              if (this.confirmAction) {
                  this.confirmAction();
              }
              this.showConfirmModal = false;
          },
          scrollToSection(sectionId) {
              const section = document.getElementById(sectionId);
              if (section) {
                  section.scrollIntoView({ behavior: 'smooth' });
              }
              this.selectedTab = sectionId;
          }
      }" class="space-y-8">

        <!-- Confirm Modal -->
        <template x-teleport="body">
            <div x-show="showConfirmModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div x-show="showConfirmModal" class="fixed inset-0 bg-black/50" x-on:click="showConfirmModal = false"></div>
                    <div x-show="showConfirmModal" class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-auto">
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center">
                                    <x-heroicon-s-exclamation-triangle class="w-5 h-5 text-amber-600"/>
                                </div>
                                <h3 class="text-lg font-medium text-slate-900" x-text="confirmTitle"></h3>
                            </div>
                            <p class="text-sm text-slate-600 mb-6" x-text="confirmMessage"></p>
                            <div class="flex justify-end gap-3">
                                <button x-on:click="showConfirmModal = false"
                                        class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg transition-colors">
                                    Abbrechen
                                </button>
                                <button x-on:click="handleConfirm()"
                                        class="px-4 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition-colors">
                                    Bestätigen
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Tabs Navigation -->
        <div class="border-b border-[var(--ui-border)]/60 mb-6">
            <nav class="flex gap-1 flex-wrap">
                <button x-on:click="scrollToSection('general')"
                        :class="{ 'border-b-2 border-[var(--ui-primary)] text-[var(--ui-primary)]': selectedTab === 'general', 'text-[var(--ui-muted)] hover:text-[var(--ui-primary)]': selectedTab !== 'general' }"
                        class="px-3 py-2 text-sm font-medium transition-all duration-200">
                    Allgemein
                </button>
                <button x-on:click="scrollToSection('prices')"
                        :class="{ 'border-b-2 border-[var(--ui-primary)] text-[var(--ui-primary)]': selectedTab === 'prices', 'text-[var(--ui-muted)] hover:text-[var(--ui-primary)]': selectedTab !== 'prices' }"
                        class="px-3 py-2 text-sm font-medium transition-all duration-200">
                    Preise
                </button>
                <button x-on:click="scrollToSection('identification')"
                        :class="{ 'border-b-2 border-[var(--ui-primary)] text-[var(--ui-primary)]': selectedTab === 'identification', 'text-[var(--ui-muted)] hover:text-[var(--ui-primary)]': selectedTab !== 'identification' }"
                        class="px-3 py-2 text-sm font-medium transition-all duration-200">
                    Identifikation
                </button>
                <button x-on:click="scrollToSection('attributes')"
                        :class="{ 'border-b-2 border-[var(--ui-primary)] text-[var(--ui-primary)]': selectedTab === 'attributes', 'text-[var(--ui-muted)] hover:text-[var(--ui-primary)]': selectedTab !== 'attributes' }"
                        class="px-3 py-2 text-sm font-medium transition-all duration-200">
                    Attribute
                </button>
                <button x-on:click="scrollToSection('stock')"
                        :class="{ 'border-b-2 border-[var(--ui-primary)] text-[var(--ui-primary)]': selectedTab === 'stock', 'text-[var(--ui-muted)] hover:text-[var(--ui-primary)]': selectedTab !== 'stock' }"
                        class="px-3 py-2 text-sm font-medium transition-all duration-200">
                    Lagerbestand
                </button>
                <button x-on:click="scrollToSection('shipping')"
                        :class="{ 'border-b-2 border-[var(--ui-primary)] text-[var(--ui-primary)]': selectedTab === 'shipping', 'text-[var(--ui-muted)] hover:text-[var(--ui-primary)]': selectedTab !== 'shipping' }"
                        class="px-3 py-2 text-sm font-medium transition-all duration-200">
                    Versand
                </button>
            </nav>
        </div>

        <!-- General Section -->
        <section id="general">
            <x-ui-panel title="Allgemeine Informationen">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <x-ui-input-text
                            name="article.name"
                            label="Name *"
                            wire:model.live="article.name"
                            required
                            :errorKey="'article.name'"
                        />
                        <x-ui-input-textarea
                            name="article.short_description"
                            label="Kurzbeschreibung"
                            wire:model.live="article.short_description"
                            rows="2"
                            :errorKey="'article.short_description'"
                        />
                        <x-ui-input-textarea
                            name="article.long_description"
                            label="Ausführliche Beschreibung"
                            wire:model.live="article.long_description"
                            rows="4"
                            :errorKey="'article.long_description'"
                        />
                    </div>

                    <div class="space-y-4">
                        <x-ui-input-select
                            name="article.category_id"
                            label="Kategorie"
                            :options="$categories"
                            optionValue="id"
                            optionLabel="name"
                            :nullable="true"
                            nullLabel="Keine Kategorie"
                            wire:model.live="article.category_id"
                            :errorKey="'article.category_id'"
                        />
                        <x-ui-input-select
                            name="article.commerce_article_type_id"
                            label="Artikel-Typ"
                            :options="$articleTypes"
                            optionValue="id"
                            optionLabel="name"
                            :nullable="true"
                            nullLabel="Kein Typ"
                            wire:model.live="article.commerce_article_type_id"
                            :errorKey="'article.commerce_article_type_id'"
                        />
                        <x-ui-input-text
                            name="article.tags"
                            label="Tags"
                            wire:model.live="article.tags"
                            placeholder="Tags im JSON-Format"
                            :errorKey="'article.tags'"
                        />
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2">
                                <input type="checkbox"
                                       wire:model.live="article.is_digital"
                                       class="rounded border-slate-300 text-purple-600 focus:ring-purple-500">
                                <span class="text-sm text-slate-700">Digital</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox"
                                       wire:model.live="article.is_physical"
                                       class="rounded border-slate-300 text-purple-600 focus:ring-purple-500">
                                <span class="text-sm text-slate-700">Physisch</span>
                            </label>
                        </div>
                    </div>
                </div>
            </x-ui-panel>
        </section>

        <!-- Prices Section -->
        <section id="prices">
            <x-ui-panel title="Artikel Preis">
                <div class="space-y-6">
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <x-ui-input-select
                                name="article.commerce_tax_category_id"
                                label="Mehrwertsteuerkategorie *"
                                :options="$taxCategories"
                                optionValue="id"
                                optionLabel="name"
                                :nullable="true"
                                nullLabel="Bitte wählen"
                                wire:model.live="article.commerce_tax_category_id"
                                :errorKey="'article.commerce_tax_category_id'"
                            />
                        </div>

                        @if($article->commerce_tax_category_id)
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Nettopreis (€)</label>
                                    <div class="flex gap-2">
                                        <input type="number"
                                               wire:model="netPrice"
                                               step="0.01"
                                               class="w-full bg-slate-50 border-0 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                                        <button x-on:click="
                                                    @if($article->articleNetPrices->count() > 0)
                                                        confirmTitle = 'Preis speichern';
                                                        confirmMessage = 'Ein neuer Preis überschreibt die Gültigkeit aller vorherigen Preise. Fortfahren?';
                                                        confirmAction = () => $wire.createPrice();
                                                        showConfirmModal = true;
                                                    @else
                                                        $wire.createPrice();
                                                    @endif
                                                "
                                                class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm hover:bg-purple-700 transition-colors whitespace-nowrap">
                                            Preis speichern
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($article->commerce_tax_category_id)
                        <div class="border-t border-slate-200/60 pt-6">
                            <h4 class="text-sm font-medium text-slate-700 mb-4">Preishistorie:</h4>
                            <div class="grid grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <h5 class="text-xs font-medium text-slate-500">Nettopreise:</h5>
                                    @foreach($article->articleNetPrices->sortByDesc('valid_from') as $price)
                                        <div class="p-3 bg-slate-50 rounded-lg">
                                            @if($loop->first)
                                                <div class="text-xs font-medium text-purple-600 mb-1">Aktueller Preis</div>
                                            @endif
                                            <div class="text-sm font-medium">{{ number_format($price->net_price, 2, ',', '.') }} €</div>
                                            <div class="text-xs text-slate-500">
                                                Gültig ab: {{ $price->valid_from->format('d.m.Y H:i') }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="space-y-2">
                                    <h5 class="text-xs font-medium text-slate-500">Aktuelle Bruttopreise:</h5>
                                    @foreach($article->articlePrices as $grossPrice)
                                        <div class="p-3 bg-slate-50 rounded-lg">
                                            <div class="text-xs text-slate-600 mb-1">
                                                {{ $grossPrice->salesContext->name ?? 'N/A' }}
                                            </div>
                                            <div class="text-sm font-medium">
                                                {{ number_format($grossPrice->gross_price, 2, ',', '.') }} €
                                                <span class="text-xs text-slate-500">
                                                    (inkl. {{ $grossPrice->tax_rate }}% MwSt.)
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </x-ui-panel>
        </section>

        <!-- Identification Section -->
        <section id="identification">
            <x-ui-panel title="Identifikation">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <x-ui-input-text name="article.sku" label="SKU" wire:model.live="article.sku" :errorKey="'article.sku'" />
                    <x-ui-input-text name="article.gtin" label="GTIN" wire:model.live="article.gtin" :errorKey="'article.gtin'" />
                    <x-ui-input-text name="article.ean" label="EAN" wire:model.live="article.ean" :errorKey="'article.ean'" />
                    <x-ui-input-text name="article.upc" label="UPC" wire:model.live="article.upc" :errorKey="'article.upc'" />
                    <x-ui-input-text name="article.isbn" label="ISBN" wire:model.live="article.isbn" :errorKey="'article.isbn'" />
                    <x-ui-input-text name="article.manufacturer_part_number" label="Hersteller-Artikelnummer" wire:model.live="article.manufacturer_part_number" :errorKey="'article.manufacturer_part_number'" />
                    <x-ui-input-text name="article.country_of_origin" label="Herkunftsland (ISO)" wire:model.live="article.country_of_origin" maxlength="2" :errorKey="'article.country_of_origin'" />
                    <x-ui-input-text name="article.hs_code" label="HS-Code" wire:model.live="article.hs_code" :errorKey="'article.hs_code'" />
                </div>
            </x-ui-panel>
        </section>

        <!-- Attributes Section -->
        <section id="attributes">
            <x-ui-panel title="Attribute">
                <div class="flex flex-col gap-2 mb-6">
                    @foreach($teamAttributeSets as $set)
                        <label class="flex items-center gap-2" wire:key="attribute-set-{{ $set->id }}">
                            <input type="checkbox"
                                   wire:model.live="selectedAttributeSets"
                                   value="{{ $set->id }}"
                                   class="rounded border-slate-300 text-purple-600 focus:ring-purple-500">
                            <span class="text-sm text-slate-700">{{ $set->name }}</span>
                        </label>
                    @endforeach
                </div>

                @foreach($selectedAttributeSets as $setId)
                    @php
                        $attributeSet = $teamAttributeSets->find($setId);
                    @endphp

                    @if($attributeSet)
                        <div class="mb-4" wire:key="attribute-set-items-{{ $attributeSet->id }}">
                            <h3 class="text-sm font-medium text-slate-800 mb-2">{{ $attributeSet->name }}</h3>
                            <div class="flex flex-col gap-2 pl-4">
                                @foreach($attributeSet->attributeSetItems as $item)
                                    <label class="flex items-center gap-2" wire:key="attribute-set-item-{{ $item->id }}">
                                        <input type="checkbox"
                                               wire:model.live="selectedAttributeSetItems"
                                               value="{{ $item->id }}"
                                               class="rounded border-slate-300 text-purple-600 focus:ring-purple-500">
                                        <span class="text-sm text-slate-700">{{ $item->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </x-ui-panel>
        </section>

        <!-- Stock Section -->
        <section id="stock">
            <x-ui-panel title="Lagerbestand & Verfügbarkeit">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <x-ui-input-number name="article.stock_level" label="Lagerbestand" wire:model.live="article.stock_level" min="0" :errorKey="'article.stock_level'" />
                    <x-ui-input-number name="article.stock_alert_threshold" label="Mindestbestand" wire:model.live="article.stock_alert_threshold" min="0" :errorKey="'article.stock_alert_threshold'" />
                    <x-ui-input-number name="article.lead_time_days" label="Lieferzeit (Tage)" wire:model.live="article.lead_time_days" min="0" :errorKey="'article.lead_time_days'" />
                    <div class="flex flex-col gap-2">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model.live="article.is_available" class="rounded border-slate-300 text-purple-600 focus:ring-purple-500">
                            <span class="text-sm text-slate-700">Verfügbar</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model.live="article.backorder_allowed" class="rounded border-slate-300 text-purple-600 focus:ring-purple-500">
                            <span class="text-sm text-slate-700">Nachbestellung erlaubt</span>
                        </label>
                    </div>
                </div>
            </x-ui-panel>
        </section>

        <!-- Shipping Section -->
        <section id="shipping">
            <x-ui-panel title="Versand & Handhabung">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <x-ui-input-number name="article.weight" label="Gewicht (kg)" wire:model.live="article.weight" step="0.01" :errorKey="'article.weight'" />
                            <x-ui-input-number name="article.volume" label="Volumen" wire:model.live="article.volume" step="0.01" :errorKey="'article.volume'" />
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <x-ui-input-number name="article.width" label="Breite (cm)" wire:model.live="article.width" step="0.01" :errorKey="'article.width'" />
                            <x-ui-input-number name="article.height" label="Höhe (cm)" wire:model.live="article.height" step="0.01" :errorKey="'article.height'" />
                            <x-ui-input-number name="article.depth" label="Tiefe (cm)" wire:model.live="article.depth" step="0.01" :errorKey="'article.depth'" />
                        </div>
                    </div>
                    <div class="space-y-4">
                        <x-ui-input-text name="article.shipping_class" label="Versandklasse" wire:model.live="article.shipping_class" :errorKey="'article.shipping_class'" />
                        <x-ui-input-number name="article.storage_temperature" label="Lagertemperatur" wire:model.live="article.storage_temperature" step="0.1" :errorKey="'article.storage_temperature'" />
                        <div class="flex flex-col gap-2">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" wire:model.live="article.is_fragile" class="rounded border-slate-300 text-purple-600 focus:ring-purple-500">
                                <span class="text-sm text-slate-700">Zerbrechlich</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" wire:model.live="article.is_hazardous" class="rounded border-slate-300 text-purple-600 focus:ring-purple-500">
                                <span class="text-sm text-slate-700">Gefahrgut</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" wire:model.live="article.recyclable" class="rounded border-slate-300 text-purple-600 focus:ring-purple-500">
                                <span class="text-sm text-slate-700">Recycelbar</span>
                            </label>
                        </div>
                    </div>
                </div>
            </x-ui-panel>
        </section>
      </div>
    </x-ui-page-container>
</x-ui-page>
