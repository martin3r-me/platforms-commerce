{{--
    Commerce Settings View
    Zentrale Konfiguration: Artikel-Kategorien, Artikel-Typen, Steuern, Verkaufskontexte
--}}

<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Einstellungen" icon="heroicon-o-cog-6-tooth" />
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Commerce', 'href' => route('commerce.index'), 'icon' => 'shopping-bag'],
            ['label' => 'Einstellungen'],
        ]" />
    </x-slot>

    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Navigation" width="w-80" :defaultOpen="true" storeKey="sidebarOpen" side="left">
            <div class="p-6 space-y-6" x-data>
                <div>
                    <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-3">Bereiche</h3>
                    <div class="space-y-1">
                        <button x-on:click="$dispatch('scroll-to', { section: 'articlecategories' })"
                                class="w-full text-left px-3 py-2 rounded-md text-[13px] font-medium text-gray-700 hover:bg-gray-100 transition-colors">
                            <span class="flex items-center gap-2">
                                @svg('heroicon-o-folder', 'w-4 h-4 text-gray-400')
                                Artikel-Kategorien
                            </span>
                        </button>
                        <button x-on:click="$dispatch('scroll-to', { section: 'articletypes' })"
                                class="w-full text-left px-3 py-2 rounded-md text-[13px] font-medium text-gray-700 hover:bg-gray-100 transition-colors">
                            <span class="flex items-center gap-2">
                                @svg('heroicon-o-tag', 'w-4 h-4 text-gray-400')
                                Artikel-Typen
                            </span>
                        </button>
                        <button x-on:click="$dispatch('scroll-to', { section: 'tax' })"
                                class="w-full text-left px-3 py-2 rounded-md text-[13px] font-medium text-gray-700 hover:bg-gray-100 transition-colors">
                            <span class="flex items-center gap-2">
                                @svg('heroicon-o-receipt-percent', 'w-4 h-4 text-gray-400')
                                Steuerkategorien
                            </span>
                        </button>
                        <button x-on:click="$dispatch('scroll-to', { section: 'salescontext' })"
                                class="w-full text-left px-3 py-2 rounded-md text-[13px] font-medium text-gray-700 hover:bg-gray-100 transition-colors">
                            <span class="flex items-center gap-2">
                                @svg('heroicon-o-building-storefront', 'w-4 h-4 text-gray-400')
                                Verkaufskontext
                            </span>
                        </button>
                        <button x-on:click="$dispatch('scroll-to', { section: 'taxrules' })"
                                class="w-full text-left px-3 py-2 rounded-md text-[13px] font-medium text-gray-700 hover:bg-gray-100 transition-colors">
                            <span class="flex items-center gap-2">
                                @svg('heroicon-o-table-cells', 'w-4 h-4 text-gray-400')
                                Steuer Matrix
                            </span>
                        </button>
                    </div>
                </div>

                <hr class="border-gray-200">

                <div>
                    <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-3">Empfohlene Reihenfolge</h3>
                    <div class="space-y-2 text-[13px] text-gray-500">
                        <div class="flex items-start gap-2">
                            <span class="text-[#166EE1] font-bold mt-0.5">1.</span>
                            <span>Artikel-Kategorien anlegen</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="text-[#166EE1] font-bold mt-0.5">2.</span>
                            <span>Artikel-Typen definieren</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="text-[#166EE1] font-bold mt-0.5">3.</span>
                            <span>Steuerkategorien einrichten</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="text-[#166EE1] font-bold mt-0.5">4.</span>
                            <span>Verkaufskontexte erstellen</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="text-[#166EE1] font-bold mt-0.5">5.</span>
                            <span>Steuer Matrix prüfen</span>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    <x-ui-page-container spacing="space-y-8">
      <div x-data="{
          selectedTab: 'articlecategories',
          editCategoryOpen: false,
          editContextOpen: false,
          editTypeOpen: false,
          editArticleCategoryOpen: false,
          confirmDeleteCategory: null,
          confirmDeleteContext: null,
          confirmDeleteType: null,
          confirmDeleteArticleCategory: null,
          scrollToSection(sectionId) {
              const section = document.getElementById(sectionId);
              if (section) {
                  section.scrollIntoView({ behavior: 'smooth' });
              }
              this.selectedTab = sectionId;
          }
      }"
      x-on:open-edit-category-modal.window="editCategoryOpen = true"
      x-on:open-edit-context-modal.window="editContextOpen = true"
      x-on:open-edit-type-modal.window="editTypeOpen = true"
      x-on:open-edit-article-category-modal.window="editArticleCategoryOpen = true"
      x-on:scroll-to.window="scrollToSection($event.detail.section)"
      class="space-y-8">

        {{-- ARTIKEL-KATEGORIEN --}}
        <section id="articlecategories" class="scroll-mt-4">
            <section class="bg-white rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Artikel-Kategorien</h3>
                </div>
                <div class="p-4">
                    <div class="mb-6 p-3 rounded-md bg-blue-50 border border-blue-200 text-[13px] text-blue-800">
                        <p class="font-medium text-blue-900 mb-2">Wozu Artikel-Kategorien?</p>
                        <p class="mb-3">Kategorien bilden die grobe Struktur deines Sortiments ab. Sie beantworten die Frage: <strong class="text-blue-900">Was verkaufe ich?</strong></p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                            <div class="bg-white rounded-lg p-3 border border-blue-200/40">
                                <div class="font-medium text-gray-900 text-[11px] uppercase tracking-wide mb-2">Beispiel Catering</div>
                                <div class="space-y-1 text-[11px]">
                                    <div class="flex items-center gap-1"><span class="text-[#166EE1]">Food</span></div>
                                    <div class="flex items-center gap-1 pl-4">Vorspeisen</div>
                                    <div class="flex items-center gap-1 pl-4">Hauptgerichte</div>
                                    <div class="flex items-center gap-1 pl-4">Desserts</div>
                                    <div class="flex items-center gap-1"><span class="text-[#166EE1]">Beverage</span></div>
                                    <div class="flex items-center gap-1 pl-4">Softdrinks</div>
                                    <div class="flex items-center gap-1 pl-4">Alkoholisch</div>
                                </div>
                            </div>
                            <div class="bg-white rounded-lg p-3 border border-blue-200/40">
                                <div class="font-medium text-gray-900 text-[11px] uppercase tracking-wide mb-2">So funktioniert es</div>
                                <ul class="list-disc list-inside space-y-1 text-[11px]">
                                    <li>Erstelle zuerst <strong>Hauptkategorien</strong> (Food, NonFood, ...)</li>
                                    <li>Dann <strong>Unterkategorien</strong> innerhalb der Hauptkategorien</li>
                                    <li>Beliebig viele Ebenen verschachtelbar</li>
                                    <li>Jeder Artikel wird einer Kategorie zugeordnet</li>
                                </ul>
                            </div>
                        </div>
                        <p class="text-[11px]"><strong class="text-blue-900">Unterschied zu Artikel-Typen:</strong> Kategorien strukturieren dein Sortiment (Was?). Typen beschreiben die Beschaffenheit eines Artikels (Wie? - z.B. physisch, digital, Service).</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Kategoriename</label>
                                <input type="text" wire:model="cat_name" placeholder="z.B. Vorspeisen, Softdrinks, Equipment..." required
                                       class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                                @error('cat_name') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Beschreibung</label>
                                <textarea wire:model="cat_description" rows="2" placeholder="Wofür wird diese Kategorie verwendet?"
                                          class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]"></textarea>
                                @error('cat_description') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Übergeordnete Kategorie</label>
                                <select wire:model="cat_parent_id"
                                        class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                                    <option value="">-- Hauptkategorie (oberste Ebene) --</option>
                                    @foreach($allArticleCategories as $opt)
                                        <option value="{{ $opt->id }}">{{ $opt->display_name }}</option>
                                    @endforeach
                                </select>
                                @error('cat_parent_id') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Farbe</label>
                                <input type="color" wire:model="cat_color"
                                       class="h-10 w-full rounded-md cursor-pointer border border-gray-300">
                            </div>
                            <button wire:click="saveArticleCategory"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-[#166EE1] text-white text-[13px] font-medium hover:bg-blue-700 transition-colors">
                                Kategorie anlegen
                            </button>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-[13px] font-medium text-gray-900 mb-3">Kategorie-Baum</h3>
                            @if($articleCategories->count() > 0)
                                <div class="space-y-1">
                                    @foreach($articleCategories as $rootCat)
                                        @include('commerce::livewire.settings.category-tree-item', ['category' => $rootCat, 'depth' => 0])
                                    @endforeach
                                </div>
                            @else
                                <div class="py-4 text-[13px] text-gray-500 text-center">
                                    Noch keine Kategorien vorhanden. Lege deine erste Hauptkategorie an.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        </section>

        {{-- ARTIKEL-TYPEN --}}
        <section id="articletypes" class="scroll-mt-4">
            <section class="bg-white rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Artikel-Typen</h3>
                </div>
                <div class="p-4">
                    <div class="mb-6 p-3 rounded-md bg-blue-50 border border-blue-200 text-[13px] text-blue-800">
                        <p class="font-medium text-blue-900 mb-2">Wozu Artikel-Typen?</p>
                        <p class="mb-3">Typen beschreiben die <strong class="text-blue-900">Beschaffenheit</strong> eines Artikels - nicht was er ist, sondern wie er gehandhabt wird.</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                            @foreach(['Food' => 'Essbare Waren mit Haltbarkeitsdatum, Kühlkette, Allergenen', 'Non-Food' => 'Physische Ware ohne Sonderbehandlung (Equipment, Dekoration)', 'Dienstleistung' => 'Kein Lager, kein Versand - reine Arbeitsleistung'] as $typeName => $typeDesc)
                                <div class="bg-white rounded-lg p-3 border border-blue-200/40 text-center">
                                    <div class="text-[11px] uppercase tracking-wide text-gray-500 mb-1">Typ</div>
                                    <div class="font-medium text-gray-900">{{ $typeName }}</div>
                                    <div class="text-[11px] mt-1">{{ $typeDesc }}</div>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-[11px]"><strong class="text-blue-900">Auswirkung:</strong> Der Typ bestimmt, welche Felder beim Artikel relevant sind (z.B. Lager, Versand, Temperatur). Produkte erben den Typ vom zugewiesenen Artikel.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Name</label>
                                <input type="text" wire:model="type_name" placeholder="z.B. Food, Non-Food, Dienstleistung..." required
                                       class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                                @error('type_name') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Beschreibung</label>
                                <textarea wire:model="type_description" rows="2" placeholder="Welche Eigenschaften hat dieser Typ? Wann wird er verwendet?"
                                          class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]"></textarea>
                                @error('type_description') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Farbe</label>
                                <input type="color" wire:model="type_color"
                                       class="h-10 w-full rounded-md cursor-pointer border border-gray-300">
                            </div>
                            <button wire:click="saveArticleType"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-[#166EE1] text-white text-[13px] font-medium hover:bg-blue-700 transition-colors">
                                Typ anlegen
                            </button>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-[13px] font-medium text-gray-900 mb-3">Bestehende Artikel-Typen</h3>
                            <div class="divide-y divide-gray-200">
                                @forelse($articleTypes as $type)
                                    <div class="py-2 group">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                @if($type->color)
                                                    <span class="w-4 h-4 rounded-full flex-shrink-0" style="background-color: {{ $type->color }}"></span>
                                                @endif
                                                <div>
                                                    <span class="text-[13px] font-medium text-gray-900">{{ $type->name }}</span>
                                                    @if($type->articles_count > 0)
                                                        <span class="text-[11px] text-gray-500 ml-1">({{ $type->articles_count }} Artikel)</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button wire:click="editArticleType({{ $type->id }})"
                                                        class="p-1.5 rounded-md text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition-colors">
                                                    <x-heroicon-s-pencil-square class="w-4 h-4"/>
                                                </button>
                                                <button x-on:click="confirmDeleteType = {{ $type->id }}"
                                                        class="p-1.5 rounded-md text-gray-400 hover:bg-red-50 hover:text-red-600 transition-colors">
                                                    <x-heroicon-s-trash class="w-4 h-4"/>
                                                </button>
                                            </div>
                                        </div>
                                        @if($type->description)
                                            <p class="text-[11px] text-gray-500 mt-1">{{ $type->description }}</p>
                                        @endif
                                    </div>

                                    <div x-show="confirmDeleteType === {{ $type->id }}" x-cloak
                                         class="py-2 px-3 bg-red-50 rounded-lg flex items-center justify-between">
                                        <span class="text-[13px] text-red-700">Wirklich löschen?</span>
                                        <div class="flex items-center gap-2">
                                            <button x-on:click="confirmDeleteType = null"
                                                    class="inline-flex items-center px-2 py-1 rounded-md border border-gray-300 bg-white text-gray-700 text-[11px] font-medium hover:bg-gray-50 transition-colors">
                                                Abbrechen
                                            </button>
                                            <button wire:click="deleteArticleType({{ $type->id }})"
                                                    x-on:click="confirmDeleteType = null"
                                                    class="inline-flex items-center px-2 py-1 rounded-md bg-red-600 text-white text-[11px] font-medium hover:bg-red-700 transition-colors">
                                                Löschen
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="py-4 text-[13px] text-gray-500 text-center">Keine Artikel-Typen vorhanden.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </section>

        {{-- STEUERKATEGORIEN --}}
        <section id="tax" class="scroll-mt-4">
            <section class="bg-white rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Steuerkategorien</h3>
                </div>
                <div class="p-4">
                    <div class="mb-6 p-3 rounded-md bg-blue-50 border border-blue-200 text-[13px] text-blue-800">
                        <p class="font-medium text-blue-900 mb-2">Wozu Steuerkategorien?</p>
                        <p class="mb-3">Steuerkategorien legen fest, <strong class="text-blue-900">welcher Steuersatz</strong> für eine Gruppe von Artikeln gilt. In Deutschland typischerweise:</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                            @foreach(['19%' => ['Regelsteuersatz', 'Equipment, Dekoration, ...'], '7%' => ['Ermäßigter Satz', 'Lebensmittel, Bücher, ...'], '0%' => ['Steuerfrei', 'Export, steuerbefreite Leistungen']] as $rate => $info)
                                <div class="bg-white rounded-lg p-3 border border-blue-200/40 text-center">
                                    <div class="text-2xl font-bold text-gray-900">{{ $rate }}</div>
                                    <div class="text-[11px]">{{ $info[0] }}</div>
                                    <div class="text-[11px] text-blue-600">{{ $info[1] }}</div>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-[11px]"><strong class="text-blue-900">Zusammenspiel:</strong> Eine Steuerkategorie wird jedem Artikel zugewiesen. In der Steuer Matrix (unten) wird dann pro Verkaufskontext der tatsächliche Satz bestimmt.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Name</label>
                                <input type="text" wire:model="name" placeholder="z.B. Regelsteuersatz, Ermäßigt, Steuerfrei..." required
                                       class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                                @error('name') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Standard-Steuersatz (%)</label>
                                <input type="number" wire:model="default_rate" min="0" max="100" step="0.01" placeholder="z.B. 19" required
                                       class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                                @error('default_rate') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <button wire:click="save"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-[#166EE1] text-white text-[13px] font-medium hover:bg-blue-700 transition-colors">
                                Kategorie anlegen
                            </button>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-[13px] font-medium text-gray-900 mb-3">Bestehende Steuerkategorien</h3>
                            <div class="divide-y divide-gray-200">
                                @forelse($categories as $category)
                                    <div class="py-2 flex items-center justify-between group">
                                        <div>
                                            <span class="text-[13px] text-gray-900">{{ $category->name }}</span>
                                            <span class="text-[13px] font-medium text-gray-900 ml-2">{{ $category->default_rate }}%</span>
                                        </div>
                                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button wire:click="editCategory({{ $category->id }})"
                                                    class="p-1.5 rounded-md text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition-colors">
                                                <x-heroicon-s-pencil-square class="w-4 h-4"/>
                                            </button>
                                            <button x-on:click="confirmDeleteCategory = {{ $category->id }}"
                                                    class="p-1.5 rounded-md text-gray-400 hover:bg-red-50 hover:text-red-600 transition-colors">
                                                <x-heroicon-s-trash class="w-4 h-4"/>
                                            </button>
                                        </div>
                                    </div>

                                    <div x-show="confirmDeleteCategory === {{ $category->id }}" x-cloak
                                         class="py-2 px-3 bg-red-50 rounded-lg flex items-center justify-between">
                                        <span class="text-[13px] text-red-700">Wirklich löschen?</span>
                                        <div class="flex items-center gap-2">
                                            <button x-on:click="confirmDeleteCategory = null"
                                                    class="inline-flex items-center px-2 py-1 rounded-md border border-gray-300 bg-white text-gray-700 text-[11px] font-medium hover:bg-gray-50 transition-colors">
                                                Abbrechen
                                            </button>
                                            <button wire:click="deleteCategory({{ $category->id }})"
                                                    x-on:click="confirmDeleteCategory = null"
                                                    class="inline-flex items-center px-2 py-1 rounded-md bg-red-600 text-white text-[11px] font-medium hover:bg-red-700 transition-colors">
                                                Löschen
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="py-4 text-[13px] text-gray-500 text-center">Keine Steuerkategorien vorhanden.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </section>

        {{-- VERKAUFSKONTEXT --}}
        <section id="salescontext" class="scroll-mt-4">
            <section class="bg-white rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Verkaufskontext</h3>
                </div>
                <div class="p-4">
                    <div class="mb-6 p-3 rounded-md bg-blue-50 border border-blue-200 text-[13px] text-blue-800">
                        <p class="font-medium text-blue-900 mb-2">Wozu Verkaufskontexte?</p>
                        <p class="mb-3">Ein Verkaufskontext beschreibt <strong class="text-blue-900">wo und wie</strong> verkauft wird. Der Kontext bestimmt den Steuersatz und die Verfügbarkeit von Artikeln.</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                            @foreach(['Vor-Ort-Verzehr' => 'Gast isst vor Ort im Restaurant/auf dem Event. Voller MwSt-Satz (19%).', 'Außer-Haus / Lieferung' => 'Essen zum Mitnehmen oder Lieferung. Ermäßigter Satz (7%) auf Speisen.', 'Online-Shop' => 'Verkauf über Website. Eigene Preislisten und Verfügbarkeiten möglich.'] as $ctxName => $ctxDesc)
                                <div class="bg-white rounded-lg p-3 border border-blue-200/40">
                                    <div class="font-medium text-gray-900 text-[13px] mb-1">{{ $ctxName }}</div>
                                    <div class="text-[11px]">{{ $ctxDesc }}</div>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-[11px]"><strong class="text-blue-900">Wichtig:</strong> Jede Kombination aus Steuerkategorie + Verkaufskontext ergibt einen konkreten Steuersatz in der Steuer Matrix.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Kontext Name</label>
                                <input type="text" wire:model="context_name" placeholder="z.B. Vor-Ort-Verzehr, Lieferung, Online-Shop..." required
                                       class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                                @error('context_name') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Kontext Beschreibung</label>
                                <textarea wire:model="context_description" rows="3" placeholder="In welcher Situation wird dieser Kontext verwendet? Welche Steuerregeln gelten?"
                                          class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]"></textarea>
                                @error('context_description') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <button wire:click="saveSalesContext"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-[#166EE1] text-white text-[13px] font-medium hover:bg-blue-700 transition-colors">
                                Kontext anlegen
                            </button>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-[13px] font-medium text-gray-900 mb-3">Bestehende Verkaufskontexte</h3>
                            <div class="divide-y divide-gray-200">
                                @forelse($contexts as $context)
                                    <div class="py-2 group">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[13px] font-medium text-gray-900">{{ $context->name }}</span>
                                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button wire:click="editContext({{ $context->id }})"
                                                        class="p-1.5 rounded-md text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition-colors">
                                                    <x-heroicon-s-pencil-square class="w-4 h-4"/>
                                                </button>
                                                <button x-on:click="confirmDeleteContext = {{ $context->id }}"
                                                        class="p-1.5 rounded-md text-gray-400 hover:bg-red-50 hover:text-red-600 transition-colors">
                                                    <x-heroicon-s-trash class="w-4 h-4"/>
                                                </button>
                                            </div>
                                        </div>
                                        @if($context->description)
                                            <p class="text-[11px] text-gray-500 mt-1">{{ $context->description }}</p>
                                        @endif
                                    </div>

                                    <div x-show="confirmDeleteContext === {{ $context->id }}" x-cloak
                                         class="py-2 px-3 bg-red-50 rounded-lg flex items-center justify-between">
                                        <span class="text-[13px] text-red-700">Wirklich löschen?</span>
                                        <div class="flex items-center gap-2">
                                            <button x-on:click="confirmDeleteContext = null"
                                                    class="inline-flex items-center px-2 py-1 rounded-md border border-gray-300 bg-white text-gray-700 text-[11px] font-medium hover:bg-gray-50 transition-colors">
                                                Abbrechen
                                            </button>
                                            <button wire:click="deleteContext({{ $context->id }})"
                                                    x-on:click="confirmDeleteContext = null"
                                                    class="inline-flex items-center px-2 py-1 rounded-md bg-red-600 text-white text-[11px] font-medium hover:bg-red-700 transition-colors">
                                                Löschen
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="py-4 text-[13px] text-gray-500 text-center">Keine Verkaufskontexte vorhanden.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </section>

        {{-- STEUER MATRIX --}}
        <section id="taxrules" class="scroll-mt-4">
            <section class="bg-white rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Steuer Matrix</h3>
                </div>
                <div class="p-4">
                    <div class="mb-6 p-3 rounded-md bg-blue-50 border border-blue-200 text-[13px] text-blue-800">
                        <p class="font-medium text-blue-900 mb-2">So funktioniert die Steuer Matrix</p>
                        <p class="mb-3">Die Matrix wird <strong class="text-blue-900">automatisch</strong> aus deinen Steuerkategorien und Verkaufskontexten erzeugt. Jede Zeile zeigt: Welcher Steuersatz gilt für welche Kombination?</p>
                        <div class="bg-white rounded-lg p-3 border border-blue-200/40 text-[11px]">
                            <div class="font-medium text-gray-900 mb-2">Beispiel-Ablauf:</div>
                            <div class="space-y-1">
                                <div>1. Artikel "Schnitzel" hat Steuerkategorie <strong>"Ermäßigt 7%"</strong></div>
                                <div>2. Verkauf findet im Kontext <strong>"Vor-Ort-Verzehr"</strong> statt</div>
                                <div>3. Matrix sagt: Ermäßigt + Vor-Ort = <strong>19%</strong> (Restaurationsleistung)</div>
                                <div>4. Gleicher Artikel im Kontext <strong>"Lieferung"</strong> = <strong>7%</strong> (Lieferung von Speisen)</div>
                            </div>
                        </div>
                    </div>

                    @if($matrix->count() > 0)
                        {{-- Table Header --}}
                        <div class="bg-gray-50 rounded-t-lg border border-gray-200 border-b-0">
                            <div class="grid grid-cols-12 px-6 py-2">
                                <div class="col-span-4 text-[11px] font-medium text-gray-400 uppercase tracking-wide">Verkaufskontext</div>
                                <div class="col-span-4 text-[11px] font-medium text-gray-400 uppercase tracking-wide">Steuerkategorie</div>
                                <div class="col-span-3 text-[11px] font-medium text-gray-400 uppercase tracking-wide">Steuersatz (%)</div>
                                <div class="col-span-1 text-[11px] font-medium text-gray-400 uppercase tracking-wide text-right">Aktionen</div>
                            </div>
                        </div>

                        {{-- Table Body --}}
                        <div class="divide-y divide-gray-100 bg-white rounded-b-lg border border-gray-200 border-t-0">
                            @foreach($matrix as $rule)
                                <livewire:commerce.settings.tax-rule-row :rule="$rule" :key="'tax-rule-' . $rule->id" />
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12 text-gray-500">
                            <x-heroicon-o-table-cells class="w-12 h-12 mx-auto mb-4 text-gray-300"/>
                            <p class="font-medium text-gray-900 text-[13px]">Noch keine Steuerregeln</p>
                            <p class="text-[13px] mt-2">Erstelle zuerst mindestens eine Steuerkategorie und einen Verkaufskontext.<br>Die Matrix wird dann automatisch generiert.</p>
                        </div>
                    @endif
                </div>
            </section>
        </section>

        {{-- MODALS --}}

        {{-- Edit Tax Category Modal --}}
        <div x-show="editCategoryOpen" x-cloak x-on:keydown.escape.window="editCategoryOpen = false">
            <x-ui-modal size="md">
                <x-slot name="header">Steuerkategorie bearbeiten</x-slot>
                <div class="space-y-4">
                    <div>
                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Name</label>
                        <input type="text" wire:model="editCategoryName" required
                               class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                        @error('editCategoryName') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Standard-Steuersatz (%)</label>
                        <input type="number" wire:model="editCategoryRate" min="0" max="100" step="0.01" required
                               class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                        @error('editCategoryRate') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <x-slot name="footer">
                    <div class="flex justify-end gap-2">
                        <button type="button" x-on:click="editCategoryOpen = false"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 text-[13px] font-medium hover:bg-gray-50 transition-colors">Abbrechen</button>
                        <button type="button" wire:click="updateCategory" x-on:click="editCategoryOpen = false"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-[#166EE1] text-white text-[13px] font-medium hover:bg-blue-700 transition-colors">Speichern</button>
                    </div>
                </x-slot>
            </x-ui-modal>
        </div>

        {{-- Edit Sales Context Modal --}}
        <div x-show="editContextOpen" x-cloak x-on:keydown.escape.window="editContextOpen = false">
            <x-ui-modal size="md">
                <x-slot name="header">Verkaufskontext bearbeiten</x-slot>
                <div class="space-y-4">
                    <div>
                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Kontext Name</label>
                        <input type="text" wire:model="editContextName" required
                               class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                        @error('editContextName') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Kontext Beschreibung</label>
                        <textarea wire:model="editContextDescription" rows="3"
                                  class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]"></textarea>
                        @error('editContextDescription') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <x-slot name="footer">
                    <div class="flex justify-end gap-2">
                        <button type="button" x-on:click="editContextOpen = false"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 text-[13px] font-medium hover:bg-gray-50 transition-colors">Abbrechen</button>
                        <button type="button" wire:click="updateContext" x-on:click="editContextOpen = false"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-[#166EE1] text-white text-[13px] font-medium hover:bg-blue-700 transition-colors">Speichern</button>
                    </div>
                </x-slot>
            </x-ui-modal>
        </div>

        {{-- Edit Article Type Modal --}}
        <div x-show="editTypeOpen" x-cloak x-on:keydown.escape.window="editTypeOpen = false">
            <x-ui-modal size="md">
                <x-slot name="header">Artikel-Typ bearbeiten</x-slot>
                <div class="space-y-4">
                    <div>
                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Name</label>
                        <input type="text" wire:model="editTypeName" required
                               class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                        @error('editTypeName') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Beschreibung</label>
                        <textarea wire:model="editTypeDescription" rows="2"
                                  class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]"></textarea>
                        @error('editTypeDescription') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Farbe</label>
                        <input type="color" wire:model="editTypeColor"
                               class="h-10 w-full rounded-md cursor-pointer border border-gray-300">
                    </div>
                </div>
                <x-slot name="footer">
                    <div class="flex justify-end gap-2">
                        <button type="button" x-on:click="editTypeOpen = false"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 text-[13px] font-medium hover:bg-gray-50 transition-colors">Abbrechen</button>
                        <button type="button" wire:click="updateArticleType" x-on:click="editTypeOpen = false"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-[#166EE1] text-white text-[13px] font-medium hover:bg-blue-700 transition-colors">Speichern</button>
                    </div>
                </x-slot>
            </x-ui-modal>
        </div>

        {{-- Edit Article Category Modal --}}
        <div x-show="editArticleCategoryOpen" x-cloak x-on:keydown.escape.window="editArticleCategoryOpen = false">
            <x-ui-modal size="md">
                <x-slot name="header">Artikel-Kategorie bearbeiten</x-slot>
                <div class="space-y-4">
                    <div>
                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Name</label>
                        <input type="text" wire:model="editCatName" required
                               class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                        @error('editCatName') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Beschreibung</label>
                        <textarea wire:model="editCatDescription" rows="2"
                                  class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]"></textarea>
                        @error('editCatDescription') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Übergeordnete Kategorie</label>
                        <select wire:model="editCatParentId"
                                class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                            <option value="">-- Hauptkategorie (oberste Ebene) --</option>
                            @foreach($allArticleCategories as $opt)
                                <option value="{{ $opt->id }}">{{ $opt->display_name }}</option>
                            @endforeach
                        </select>
                        @error('editCatParentId') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-gray-500 mb-1">Farbe</label>
                        <input type="color" wire:model="editCatColor"
                               class="h-10 w-full rounded-md cursor-pointer border border-gray-300">
                    </div>
                </div>
                <x-slot name="footer">
                    <div class="flex justify-end gap-2">
                        <button type="button" x-on:click="editArticleCategoryOpen = false"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 text-[13px] font-medium hover:bg-gray-50 transition-colors">Abbrechen</button>
                        <button type="button" wire:click="updateArticleCategory" x-on:click="editArticleCategoryOpen = false"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-[#166EE1] text-white text-[13px] font-medium hover:bg-blue-700 transition-colors">Speichern</button>
                    </div>
                </x-slot>
            </x-ui-modal>
        </div>

      </div>
    </x-ui-page-container>
</x-ui-page>
