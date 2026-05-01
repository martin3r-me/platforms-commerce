<x-ui-page>

    <x-slot name="navbar">
        <x-ui-page-navbar title="{{ $article->name }}">
            <x-slot name="actions">
                <div class="flex items-center gap-2">
                    <div class="p-1 bg-gray-50 rounded-lg ring-1 ring-gray-200">
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
                    <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-4">Navigation</h3>
                    <div class="space-y-1">
                        <a href="{{ route('commerce.articles.index') }}" wire:navigate
                           class="flex items-center gap-2 px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 text-[13px] font-medium hover:bg-gray-50 transition-colors w-full">
                            @svg('heroicon-o-arrow-left', 'w-4 h-4')
                            Zur Artikelübersicht
                        </a>
                    </div>
                </div>

                <hr class="border-gray-200">

                <div>
                    <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-4">Artikel Info</h3>
                    <div class="space-y-2 text-[13px]">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Erstellt:</span>
                            <span class="font-medium text-gray-900">{{ $article->created_at->format('d.m.Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Geändert:</span>
                            <span class="font-medium text-gray-900">{{ $article->updated_at->format('d.m.Y H:i') }}</span>
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
                                <h3 class="text-lg font-medium text-gray-900" x-text="confirmTitle"></h3>
                            </div>
                            <p class="text-[13px] text-gray-600 mb-6" x-text="confirmMessage"></p>
                            <div class="flex justify-end gap-3">
                                <button x-on:click="showConfirmModal = false"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 text-[13px] font-medium hover:bg-gray-50 transition-colors">
                                    Abbrechen
                                </button>
                                <button x-on:click="handleConfirm()"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-[#166EE1] text-white text-[13px] font-medium hover:bg-blue-700 transition-colors">
                                    Bestätigen
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Tabs Navigation -->
        <div class="border-b border-gray-200 mb-6">
            <nav class="flex gap-1 flex-wrap">
                <button x-on:click="scrollToSection('general')"
                        :class="{ 'border-[#166EE1] text-[#166EE1]': selectedTab === 'general', 'border-transparent text-gray-400 hover:text-gray-700': selectedTab !== 'general' }"
                        class="px-4 py-2 text-[13px] font-medium border-b-2 -mb-px transition-all duration-200">
                    Allgemein
                </button>
                <button x-on:click="scrollToSection('prices')"
                        :class="{ 'border-[#166EE1] text-[#166EE1]': selectedTab === 'prices', 'border-transparent text-gray-400 hover:text-gray-700': selectedTab !== 'prices' }"
                        class="px-4 py-2 text-[13px] font-medium border-b-2 -mb-px transition-all duration-200">
                    Preise
                </button>
                <button x-on:click="scrollToSection('identification')"
                        :class="{ 'border-[#166EE1] text-[#166EE1]': selectedTab === 'identification', 'border-transparent text-gray-400 hover:text-gray-700': selectedTab !== 'identification' }"
                        class="px-4 py-2 text-[13px] font-medium border-b-2 -mb-px transition-all duration-200">
                    Identifikation
                </button>
                <button x-on:click="scrollToSection('attributes')"
                        :class="{ 'border-[#166EE1] text-[#166EE1]': selectedTab === 'attributes', 'border-transparent text-gray-400 hover:text-gray-700': selectedTab !== 'attributes' }"
                        class="px-4 py-2 text-[13px] font-medium border-b-2 -mb-px transition-all duration-200">
                    Attribute
                </button>
                <button x-on:click="scrollToSection('stock')"
                        :class="{ 'border-[#166EE1] text-[#166EE1]': selectedTab === 'stock', 'border-transparent text-gray-400 hover:text-gray-700': selectedTab !== 'stock' }"
                        class="px-4 py-2 text-[13px] font-medium border-b-2 -mb-px transition-all duration-200">
                    Lagerbestand
                </button>
                <button x-on:click="scrollToSection('shipping')"
                        :class="{ 'border-[#166EE1] text-[#166EE1]': selectedTab === 'shipping', 'border-transparent text-gray-400 hover:text-gray-700': selectedTab !== 'shipping' }"
                        class="px-4 py-2 text-[13px] font-medium border-b-2 -mb-px transition-all duration-200">
                    Versand
                </button>
            </nav>
        </div>

        <!-- General Section -->
        <section id="general">
            <section class="bg-white rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Allgemeine Informationen</h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Name *</label>
                                <input type="text" wire:model.live="article.name" required
                                       class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                                @error('article.name') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Kurzbeschreibung</label>
                                <textarea wire:model.live="article.short_description" rows="2"
                                          class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]"></textarea>
                                @error('article.short_description') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Ausführliche Beschreibung</label>
                                <textarea wire:model.live="article.long_description" rows="4"
                                          class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]"></textarea>
                                @error('article.long_description') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Kategorie</label>
                                <select wire:model.live="article.category_id"
                                        class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                                    <option value="">Keine Kategorie</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('article.category_id') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Artikel-Typ</label>
                                <select wire:model.live="article.commerce_article_type_id"
                                        class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                                    <option value="">Kein Typ</option>
                                    @foreach($articleTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                @error('article.commerce_article_type_id') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Tags</label>
                                <input type="text" wire:model.live="article.tags" placeholder="Tags im JSON-Format"
                                       class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                                @error('article.tags') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" wire:model.live="article.is_digital"
                                           class="rounded border-gray-300 text-[#166EE1] focus:ring-[#166EE1]">
                                    <span class="text-[13px] text-gray-700">Digital</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" wire:model.live="article.is_physical"
                                           class="rounded border-gray-300 text-[#166EE1] focus:ring-[#166EE1]">
                                    <span class="text-[13px] text-gray-700">Physisch</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </section>

        <!-- Prices Section -->
        <section id="prices">
            <section class="bg-white rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Artikel Preis</h3>
                </div>
                <div class="p-4">
                    <div class="space-y-6">
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Mehrwertsteuerkategorie *</label>
                                <select wire:model.live="article.commerce_tax_category_id"
                                        class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                                    <option value="">Bitte wählen</option>
                                    @foreach($taxCategories as $tc)
                                        <option value="{{ $tc->id }}">{{ $tc->name }}</option>
                                    @endforeach
                                </select>
                                @error('article.commerce_tax_category_id') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                            </div>

                            @if($article->commerce_tax_category_id)
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Nettopreis (€)</label>
                                        <div class="flex gap-2">
                                            <input type="number" wire:model="netPrice" step="0.01"
                                                   class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
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
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-[#166EE1] text-white text-[13px] font-medium hover:bg-blue-700 transition-colors whitespace-nowrap">
                                                Preis speichern
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if($article->commerce_tax_category_id)
                            <div class="border-t border-gray-200 pt-6">
                                <h4 class="text-[13px] font-medium text-gray-700 mb-4">Preishistorie:</h4>
                                <div class="grid grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <h5 class="text-[11px] font-medium text-gray-500">Nettopreise:</h5>
                                        @foreach($article->articleNetPrices->sortByDesc('valid_from') as $price)
                                            <div class="p-3 bg-gray-50 rounded-lg">
                                                @if($loop->first)
                                                    <div class="text-[11px] font-medium text-[#166EE1] mb-1">Aktueller Preis</div>
                                                @endif
                                                <div class="text-[13px] font-medium">{{ number_format($price->net_price, 2, ',', '.') }} €</div>
                                                <div class="text-[11px] text-gray-500">
                                                    Gültig ab: {{ $price->valid_from->format('d.m.Y H:i') }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="space-y-2">
                                        <h5 class="text-[11px] font-medium text-gray-500">Aktuelle Bruttopreise:</h5>
                                        @foreach($article->articlePrices as $grossPrice)
                                            <div class="p-3 bg-gray-50 rounded-lg">
                                                <div class="text-[11px] text-gray-600 mb-1">
                                                    {{ $grossPrice->salesContext->name ?? 'N/A' }}
                                                </div>
                                                <div class="text-[13px] font-medium">
                                                    {{ number_format($grossPrice->gross_price, 2, ',', '.') }} €
                                                    <span class="text-[11px] text-gray-500">
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
                </div>
            </section>
        </section>

        <!-- Identification Section -->
        <section id="identification">
            <section class="bg-white rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Identifikation</h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach(['sku' => 'SKU', 'gtin' => 'GTIN', 'ean' => 'EAN', 'upc' => 'UPC', 'isbn' => 'ISBN', 'manufacturer_part_number' => 'Hersteller-Artikelnummer', 'country_of_origin' => 'Herkunftsland (ISO)', 'hs_code' => 'HS-Code'] as $field => $label)
                            <div>
                                <label class="block text-[11px] font-medium text-gray-500 mb-1">{{ $label }}</label>
                                <input type="text" wire:model.live="article.{{ $field }}"
                                       @if($field === 'country_of_origin') maxlength="2" @endif
                                       class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                                @error('article.' . $field) <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        </section>

        <!-- Attributes Section -->
        <section id="attributes">
            <section class="bg-white rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Attribute</h3>
                </div>
                <div class="p-4">
                    <div class="flex flex-col gap-2 mb-6">
                        @foreach($teamAttributeSets as $set)
                            <label class="flex items-center gap-2" wire:key="attribute-set-{{ $set->id }}">
                                <input type="checkbox" wire:model.live="selectedAttributeSets" value="{{ $set->id }}"
                                       class="rounded border-gray-300 text-[#166EE1] focus:ring-[#166EE1]">
                                <span class="text-[13px] text-gray-700">{{ $set->name }}</span>
                            </label>
                        @endforeach
                    </div>

                    @foreach($selectedAttributeSets as $setId)
                        @php $attributeSet = $teamAttributeSets->find($setId); @endphp
                        @if($attributeSet)
                            <div class="mb-4" wire:key="attribute-set-items-{{ $attributeSet->id }}">
                                <h3 class="text-[13px] font-medium text-gray-900 mb-2">{{ $attributeSet->name }}</h3>
                                <div class="flex flex-col gap-2 pl-4">
                                    @foreach($attributeSet->attributeSetItems as $item)
                                        <label class="flex items-center gap-2" wire:key="attribute-set-item-{{ $item->id }}">
                                            <input type="checkbox" wire:model.live="selectedAttributeSetItems" value="{{ $item->id }}"
                                                   class="rounded border-gray-300 text-[#166EE1] focus:ring-[#166EE1]">
                                            <span class="text-[13px] text-gray-700">{{ $item->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </section>
        </section>

        <!-- Stock Section -->
        <section id="stock">
            <section class="bg-white rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Lagerbestand & Verfügbarkeit</h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        @foreach(['stock_level' => 'Lagerbestand', 'stock_alert_threshold' => 'Mindestbestand', 'lead_time_days' => 'Lieferzeit (Tage)'] as $field => $label)
                            <div>
                                <label class="block text-[11px] font-medium text-gray-500 mb-1">{{ $label }}</label>
                                <input type="number" wire:model.live="article.{{ $field }}" min="0"
                                       class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                                @error('article.' . $field) <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                        <div class="flex flex-col gap-2">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" wire:model.live="article.is_available"
                                       class="rounded border-gray-300 text-[#166EE1] focus:ring-[#166EE1]">
                                <span class="text-[13px] text-gray-700">Verfügbar</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" wire:model.live="article.backorder_allowed"
                                       class="rounded border-gray-300 text-[#166EE1] focus:ring-[#166EE1]">
                                <span class="text-[13px] text-gray-700">Nachbestellung erlaubt</span>
                            </label>
                        </div>
                    </div>
                </div>
            </section>
        </section>

        <!-- Shipping Section -->
        <section id="shipping">
            <section class="bg-white rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Versand & Handhabung</h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                @foreach(['weight' => 'Gewicht (kg)', 'volume' => 'Volumen'] as $field => $label)
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500 mb-1">{{ $label }}</label>
                                        <input type="number" wire:model.live="article.{{ $field }}" step="0.01"
                                               class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                                        @error('article.' . $field) <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                @endforeach
                            </div>
                            <div class="grid grid-cols-3 gap-4">
                                @foreach(['width' => 'Breite (cm)', 'height' => 'Höhe (cm)', 'depth' => 'Tiefe (cm)'] as $field => $label)
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-500 mb-1">{{ $label }}</label>
                                        <input type="number" wire:model.live="article.{{ $field }}" step="0.01"
                                               class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                                        @error('article.' . $field) <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Versandklasse</label>
                                <input type="text" wire:model.live="article.shipping_class"
                                       class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                                @error('article.shipping_class') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Lagertemperatur</label>
                                <input type="number" wire:model.live="article.storage_temperature" step="0.1"
                                       class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                                @error('article.storage_temperature') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" wire:model.live="article.is_fragile"
                                           class="rounded border-gray-300 text-[#166EE1] focus:ring-[#166EE1]">
                                    <span class="text-[13px] text-gray-700">Zerbrechlich</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" wire:model.live="article.is_hazardous"
                                           class="rounded border-gray-300 text-[#166EE1] focus:ring-[#166EE1]">
                                    <span class="text-[13px] text-gray-700">Gefahrgut</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" wire:model.live="article.recyclable"
                                           class="rounded border-gray-300 text-[#166EE1] focus:ring-[#166EE1]">
                                    <span class="text-[13px] text-gray-700">Recycelbar</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </section>
      </div>
    </x-ui-page-container>
</x-ui-page>
