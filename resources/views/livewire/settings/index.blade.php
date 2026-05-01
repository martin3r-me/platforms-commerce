{{--
    Commerce Settings View
    Zentrale Konfiguration: Artikel-Kategorien, Artikel-Typen, Steuern, Verkaufskontexte
--}}

<x-ui-page x-data="{
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
}" @open-edit-category-modal.window="editCategoryOpen = true" @open-edit-context-modal.window="editContextOpen = true" @open-edit-type-modal.window="editTypeOpen = true" @open-edit-article-category-modal.window="editArticleCategoryOpen = true">
    <x-slot name="navbar">
        <x-ui-page-navbar title="Einstellungen" icon="heroicon-o-cog-6-tooth" />
    </x-slot>

    {{-- Actionbar mit Breadcrumbs --}}
    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Commerce', 'href' => route('commerce.index'), 'icon' => 'shopping-bag'],
            ['label' => 'Einstellungen'],
        ]" />
    </x-slot>

    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Navigation" width="w-80" :defaultOpen="true" storeKey="sidebarOpen" side="left">
            <div class="p-6 space-y-6">
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-3">Bereiche</h3>
                    <div class="space-y-1">
                        <button @click="scrollToSection('articlecategories')"
                                :class="{ 'bg-[var(--ui-primary-light)] text-[var(--ui-primary)]': selectedTab === 'articlecategories' }"
                                class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-all hover:bg-[var(--ui-muted-5)]">
                            <span class="flex items-center gap-2">
                                @svg('heroicon-o-folder', 'w-4 h-4')
                                Artikel-Kategorien
                            </span>
                        </button>
                        <button @click="scrollToSection('articletypes')"
                                :class="{ 'bg-[var(--ui-primary-light)] text-[var(--ui-primary)]': selectedTab === 'articletypes' }"
                                class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-all hover:bg-[var(--ui-muted-5)]">
                            <span class="flex items-center gap-2">
                                @svg('heroicon-o-tag', 'w-4 h-4')
                                Artikel-Typen
                            </span>
                        </button>
                        <button @click="scrollToSection('tax')"
                                :class="{ 'bg-[var(--ui-primary-light)] text-[var(--ui-primary)]': selectedTab === 'tax' }"
                                class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-all hover:bg-[var(--ui-muted-5)]">
                            <span class="flex items-center gap-2">
                                @svg('heroicon-o-receipt-percent', 'w-4 h-4')
                                Steuerkategorien
                            </span>
                        </button>
                        <button @click="scrollToSection('salescontext')"
                                :class="{ 'bg-[var(--ui-primary-light)] text-[var(--ui-primary)]': selectedTab === 'salescontext' }"
                                class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-all hover:bg-[var(--ui-muted-5)]">
                            <span class="flex items-center gap-2">
                                @svg('heroicon-o-building-storefront', 'w-4 h-4')
                                Verkaufskontext
                            </span>
                        </button>
                        <button @click="scrollToSection('taxrules')"
                                :class="{ 'bg-[var(--ui-primary-light)] text-[var(--ui-primary)]': selectedTab === 'taxrules' }"
                                class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-all hover:bg-[var(--ui-muted-5)]">
                            <span class="flex items-center gap-2">
                                @svg('heroicon-o-table-cells', 'w-4 h-4')
                                Steuer Matrix
                            </span>
                        </button>
                    </div>
                </div>

                <hr class="border-[var(--ui-border)]">

                {{-- Workflow-Hinweis --}}
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-3">Empfohlene Reihenfolge</h3>
                    <div class="space-y-2 text-sm text-[var(--ui-muted)]">
                        <div class="flex items-start gap-2">
                            <span class="text-[var(--ui-primary)] font-bold mt-0.5">1.</span>
                            <span>Artikel-Kategorien anlegen</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="text-[var(--ui-primary)] font-bold mt-0.5">2.</span>
                            <span>Artikel-Typen definieren</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="text-[var(--ui-primary)] font-bold mt-0.5">3.</span>
                            <span>Steuerkategorien einrichten</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="text-[var(--ui-primary)] font-bold mt-0.5">4.</span>
                            <span>Verkaufskontexte erstellen</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="text-[var(--ui-primary)] font-bold mt-0.5">5.</span>
                            <span>Steuer Matrix prüfen</span>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    <x-ui-page-container spacing="space-y-8">

        {{-- ═══════════════════════════════════════ --}}
        {{-- ARTIKEL-KATEGORIEN (hierarchisch)       --}}
        {{-- ═══════════════════════════════════════ --}}
        <section id="articlecategories" class="scroll-mt-4">
            <x-ui-panel title="Artikel-Kategorien">
                <div class="mb-6 bg-[var(--ui-muted-5)] rounded-lg p-4 text-sm text-[var(--ui-muted)]">
                    <p class="font-medium text-[var(--ui-secondary)] mb-2">Wozu Artikel-Kategorien?</p>
                    <p class="mb-3">Kategorien bilden die grobe Struktur deines Sortiments ab. Sie beantworten die Frage: <strong class="text-[var(--ui-secondary)]">Was verkaufe ich?</strong></p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                        <div class="bg-white rounded-lg p-3 border border-[var(--ui-border)]/40">
                            <div class="font-medium text-[var(--ui-secondary)] text-xs uppercase tracking-wider mb-2">Beispiel Catering</div>
                            <div class="space-y-1 text-xs">
                                <div class="flex items-center gap-1"><span class="text-[var(--ui-primary)]">Food</span></div>
                                <div class="flex items-center gap-1 pl-4">Vorspeisen</div>
                                <div class="flex items-center gap-1 pl-4">Hauptgerichte</div>
                                <div class="flex items-center gap-1 pl-4">Desserts</div>
                                <div class="flex items-center gap-1"><span class="text-[var(--ui-primary)]">Beverage</span></div>
                                <div class="flex items-center gap-1 pl-4">Softdrinks</div>
                                <div class="flex items-center gap-1 pl-4">Alkoholisch</div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg p-3 border border-[var(--ui-border)]/40">
                            <div class="font-medium text-[var(--ui-secondary)] text-xs uppercase tracking-wider mb-2">So funktioniert es</div>
                            <ul class="list-disc list-inside space-y-1 text-xs">
                                <li>Erstelle zuerst <strong>Hauptkategorien</strong> (Food, NonFood, ...)</li>
                                <li>Dann <strong>Unterkategorien</strong> innerhalb der Hauptkategorien</li>
                                <li>Beliebig viele Ebenen verschachtelbar</li>
                                <li>Jeder Artikel wird einer Kategorie zugeordnet</li>
                            </ul>
                        </div>
                    </div>
                    <p class="text-xs"><strong class="text-[var(--ui-secondary)]">Unterschied zu Artikel-Typen:</strong> Kategorien strukturieren dein Sortiment (Was?). Typen beschreiben die Beschaffenheit eines Artikels (Wie? - z.B. physisch, digital, Service).</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Formular --}}
                    <div class="space-y-4">
                        <x-ui-input-text
                            name="cat_name"
                            label="Kategoriename"
                            wire:model="cat_name"
                            placeholder="z.B. Vorspeisen, Softdrinks, Equipment..."
                            required
                            :errorKey="'cat_name'"
                        />
                        <x-ui-input-textarea
                            name="cat_description"
                            label="Beschreibung"
                            wire:model="cat_description"
                            rows="2"
                            placeholder="Wofür wird diese Kategorie verwendet?"
                            :errorKey="'cat_description'"
                        />
                        <x-ui-input-select
                            name="cat_parent_id"
                            label="Übergeordnete Kategorie"
                            :options="$allArticleCategories"
                            optionValue="id"
                            optionLabel="display_name"
                            :nullable="true"
                            nullLabel="-- Hauptkategorie (oberste Ebene) --"
                            wire:model="cat_parent_id"
                            :errorKey="'cat_parent_id'"
                        />
                        <div>
                            <label class="block text-sm font-medium text-[var(--ui-secondary)] mb-1">Farbe</label>
                            <input type="color"
                                   wire:model="cat_color"
                                   class="h-10 w-full rounded-lg cursor-pointer border border-[var(--ui-border)]">
                        </div>
                        <x-ui-button wire:click="saveArticleCategory" variant="primary">
                            Kategorie anlegen
                        </x-ui-button>
                    </div>

                    {{-- Bestehende Kategorien (hierarchisch) --}}
                    <div class="bg-[var(--ui-muted-5)] rounded-lg p-4">
                        <h3 class="text-sm font-medium text-[var(--ui-secondary)] mb-3">Kategorie-Baum</h3>
                        @if($articleCategories->count() > 0)
                            <div class="space-y-1">
                                @foreach($articleCategories as $rootCat)
                                    @include('commerce::livewire.settings.category-tree-item', ['category' => $rootCat, 'depth' => 0])
                                @endforeach
                            </div>
                        @else
                            <div class="py-4 text-sm text-[var(--ui-muted)] text-center">
                                Noch keine Kategorien vorhanden. Lege deine erste Hauptkategorie an.
                            </div>
                        @endif
                    </div>
                </div>
            </x-ui-panel>
        </section>

        {{-- ═══════════════════════════════════════ --}}
        {{-- ARTIKEL-TYPEN                           --}}
        {{-- ═══════════════════════════════════════ --}}
        <section id="articletypes" class="scroll-mt-4">
            <x-ui-panel title="Artikel-Typen">
                <div class="mb-6 bg-[var(--ui-muted-5)] rounded-lg p-4 text-sm text-[var(--ui-muted)]">
                    <p class="font-medium text-[var(--ui-secondary)] mb-2">Wozu Artikel-Typen?</p>
                    <p class="mb-3">Typen beschreiben die <strong class="text-[var(--ui-secondary)]">Beschaffenheit</strong> eines Artikels - nicht was er ist, sondern wie er gehandhabt wird.</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                        <div class="bg-white rounded-lg p-3 border border-[var(--ui-border)]/40 text-center">
                            <div class="text-xs uppercase tracking-wider text-[var(--ui-muted)] mb-1">Typ</div>
                            <div class="font-medium text-[var(--ui-secondary)]">Food</div>
                            <div class="text-xs mt-1">Essbare Waren mit Haltbarkeitsdatum, Kühlkette, Allergenen</div>
                        </div>
                        <div class="bg-white rounded-lg p-3 border border-[var(--ui-border)]/40 text-center">
                            <div class="text-xs uppercase tracking-wider text-[var(--ui-muted)] mb-1">Typ</div>
                            <div class="font-medium text-[var(--ui-secondary)]">Non-Food</div>
                            <div class="text-xs mt-1">Physische Ware ohne Sonderbehandlung (Equipment, Dekoration)</div>
                        </div>
                        <div class="bg-white rounded-lg p-3 border border-[var(--ui-border)]/40 text-center">
                            <div class="text-xs uppercase tracking-wider text-[var(--ui-muted)] mb-1">Typ</div>
                            <div class="font-medium text-[var(--ui-secondary)]">Dienstleistung</div>
                            <div class="text-xs mt-1">Kein Lager, kein Versand - reine Arbeitsleistung</div>
                        </div>
                    </div>
                    <p class="text-xs"><strong class="text-[var(--ui-secondary)]">Auswirkung:</strong> Der Typ bestimmt, welche Felder beim Artikel relevant sind (z.B. Lager, Versand, Temperatur). Produkte erben den Typ vom zugewiesenen Artikel.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Formular --}}
                    <div class="space-y-4">
                        <x-ui-input-text
                            name="type_name"
                            label="Name"
                            wire:model="type_name"
                            placeholder="z.B. Food, Non-Food, Dienstleistung..."
                            required
                            :errorKey="'type_name'"
                        />
                        <x-ui-input-textarea
                            name="type_description"
                            label="Beschreibung"
                            wire:model="type_description"
                            rows="2"
                            placeholder="Welche Eigenschaften hat dieser Typ? Wann wird er verwendet?"
                            :errorKey="'type_description'"
                        />
                        <div>
                            <label class="block text-sm font-medium text-[var(--ui-secondary)] mb-1">Farbe</label>
                            <input type="color"
                                   wire:model="type_color"
                                   class="h-10 w-full rounded-lg cursor-pointer border border-[var(--ui-border)]">
                        </div>
                        <x-ui-button wire:click="saveArticleType" variant="primary">
                            Typ anlegen
                        </x-ui-button>
                    </div>

                    {{-- Bestehende Typen --}}
                    <div class="bg-[var(--ui-muted-5)] rounded-lg p-4">
                        <h3 class="text-sm font-medium text-[var(--ui-secondary)] mb-3">Bestehende Artikel-Typen</h3>
                        <div class="divide-y divide-[var(--ui-border)]">
                            @forelse($articleTypes as $type)
                                <div class="py-2 group">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            @if($type->color)
                                                <span class="w-4 h-4 rounded-full flex-shrink-0" style="background-color: {{ $type->color }}"></span>
                                            @endif
                                            <div>
                                                <span class="text-sm font-medium text-[var(--ui-secondary)]">{{ $type->name }}</span>
                                                @if($type->articles_count > 0)
                                                    <span class="text-xs text-[var(--ui-muted)] ml-1">({{ $type->articles_count }} Artikel)</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button wire:click="editArticleType({{ $type->id }})"
                                                    class="p-1.5 rounded-lg text-[var(--ui-muted)] hover:bg-[var(--ui-muted-10)] hover:text-[var(--ui-secondary)] transition-all">
                                                <x-heroicon-s-pencil-square class="w-4 h-4"/>
                                            </button>
                                            <button @click="confirmDeleteType = {{ $type->id }}"
                                                    class="p-1.5 rounded-lg text-[var(--ui-muted)] hover:bg-red-50 hover:text-red-600 transition-all">
                                                <x-heroicon-s-trash class="w-4 h-4"/>
                                            </button>
                                        </div>
                                    </div>
                                    @if($type->description)
                                        <p class="text-xs text-[var(--ui-muted)] mt-1">{{ $type->description }}</p>
                                    @endif
                                </div>

                                {{-- Inline Delete Confirmation --}}
                                <div x-show="confirmDeleteType === {{ $type->id }}" x-cloak
                                     class="py-2 px-3 bg-red-50 rounded-lg flex items-center justify-between">
                                    <span class="text-sm text-red-700">Wirklich löschen?</span>
                                    <div class="flex items-center gap-2">
                                        <button @click="confirmDeleteType = null"
                                                class="text-xs px-2 py-1 rounded bg-white text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)]">
                                            Abbrechen
                                        </button>
                                        <button wire:click="deleteArticleType({{ $type->id }})"
                                                @click="confirmDeleteType = null"
                                                class="text-xs px-2 py-1 rounded bg-red-600 text-white hover:bg-red-700">
                                            Löschen
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="py-4 text-sm text-[var(--ui-muted)] text-center">Keine Artikel-Typen vorhanden.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </x-ui-panel>
        </section>

        {{-- ═══════════════════════════════════════ --}}
        {{-- STEUERKATEGORIEN                        --}}
        {{-- ═══════════════════════════════════════ --}}
        <section id="tax" class="scroll-mt-4">
            <x-ui-panel title="Steuerkategorien">
                <div class="mb-6 bg-[var(--ui-muted-5)] rounded-lg p-4 text-sm text-[var(--ui-muted)]">
                    <p class="font-medium text-[var(--ui-secondary)] mb-2">Wozu Steuerkategorien?</p>
                    <p class="mb-3">Steuerkategorien legen fest, <strong class="text-[var(--ui-secondary)]">welcher Steuersatz</strong> für eine Gruppe von Artikeln gilt. In Deutschland typischerweise:</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                        <div class="bg-white rounded-lg p-3 border border-[var(--ui-border)]/40 text-center">
                            <div class="text-2xl font-bold text-[var(--ui-secondary)]">19%</div>
                            <div class="text-xs">Regelsteuersatz</div>
                            <div class="text-xs text-[var(--ui-muted)]">Equipment, Dekoration, ...</div>
                        </div>
                        <div class="bg-white rounded-lg p-3 border border-[var(--ui-border)]/40 text-center">
                            <div class="text-2xl font-bold text-[var(--ui-secondary)]">7%</div>
                            <div class="text-xs">Ermäßigter Satz</div>
                            <div class="text-xs text-[var(--ui-muted)]">Lebensmittel, Bücher, ...</div>
                        </div>
                        <div class="bg-white rounded-lg p-3 border border-[var(--ui-border)]/40 text-center">
                            <div class="text-2xl font-bold text-[var(--ui-secondary)]">0%</div>
                            <div class="text-xs">Steuerfrei</div>
                            <div class="text-xs text-[var(--ui-muted)]">Export, steuerbefreite Leistungen</div>
                        </div>
                    </div>
                    <p class="text-xs"><strong class="text-[var(--ui-secondary)]">Zusammenspiel:</strong> Eine Steuerkategorie wird jedem Artikel zugewiesen. In der Steuer Matrix (unten) wird dann pro Verkaufskontext der tatsächliche Satz bestimmt.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Formular --}}
                    <div class="space-y-4">
                        <x-ui-input-text
                            name="name"
                            label="Name"
                            wire:model="name"
                            placeholder="z.B. Regelsteuersatz, Ermäßigt, Steuerfrei..."
                            required
                            :errorKey="'name'"
                        />
                        <x-ui-input-number
                            name="default_rate"
                            label="Standard-Steuersatz (%)"
                            wire:model="default_rate"
                            min="0"
                            max="100"
                            step="0.01"
                            placeholder="z.B. 19"
                            required
                            :errorKey="'default_rate'"
                        />
                        <x-ui-button wire:click="save" variant="primary">
                            Kategorie anlegen
                        </x-ui-button>
                    </div>

                    {{-- Bestehende Kategorien --}}
                    <div class="bg-[var(--ui-muted-5)] rounded-lg p-4">
                        <h3 class="text-sm font-medium text-[var(--ui-secondary)] mb-3">Bestehende Steuerkategorien</h3>
                        <div class="divide-y divide-[var(--ui-border)]">
                            @forelse($categories as $category)
                                <div class="py-2 flex items-center justify-between group">
                                    <div>
                                        <span class="text-sm text-[var(--ui-secondary)]">{{ $category->name }}</span>
                                        <span class="text-sm font-medium text-[var(--ui-secondary)] ml-2">{{ $category->default_rate }}%</span>
                                    </div>
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button wire:click="editCategory({{ $category->id }})"
                                                class="p-1.5 rounded-lg text-[var(--ui-muted)] hover:bg-[var(--ui-muted-10)] hover:text-[var(--ui-secondary)] transition-all">
                                            <x-heroicon-s-pencil-square class="w-4 h-4"/>
                                        </button>
                                        <button @click="confirmDeleteCategory = {{ $category->id }}"
                                                class="p-1.5 rounded-lg text-[var(--ui-muted)] hover:bg-red-50 hover:text-red-600 transition-all">
                                            <x-heroicon-s-trash class="w-4 h-4"/>
                                        </button>
                                    </div>
                                </div>

                                {{-- Inline Delete Confirmation --}}
                                <div x-show="confirmDeleteCategory === {{ $category->id }}" x-cloak
                                     class="py-2 px-3 bg-red-50 rounded-lg flex items-center justify-between">
                                    <span class="text-sm text-red-700">Wirklich löschen?</span>
                                    <div class="flex items-center gap-2">
                                        <button @click="confirmDeleteCategory = null"
                                                class="text-xs px-2 py-1 rounded bg-white text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)]">
                                            Abbrechen
                                        </button>
                                        <button wire:click="deleteCategory({{ $category->id }})"
                                                @click="confirmDeleteCategory = null"
                                                class="text-xs px-2 py-1 rounded bg-red-600 text-white hover:bg-red-700">
                                            Löschen
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="py-4 text-sm text-[var(--ui-muted)] text-center">Keine Steuerkategorien vorhanden.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </x-ui-panel>
        </section>

        {{-- ═══════════════════════════════════════ --}}
        {{-- VERKAUFSKONTEXT                         --}}
        {{-- ═══════════════════════════════════════ --}}
        <section id="salescontext" class="scroll-mt-4">
            <x-ui-panel title="Verkaufskontext">
                <div class="mb-6 bg-[var(--ui-muted-5)] rounded-lg p-4 text-sm text-[var(--ui-muted)]">
                    <p class="font-medium text-[var(--ui-secondary)] mb-2">Wozu Verkaufskontexte?</p>
                    <p class="mb-3">Ein Verkaufskontext beschreibt <strong class="text-[var(--ui-secondary)]">wo und wie</strong> verkauft wird. Der Kontext bestimmt den Steuersatz und die Verfügbarkeit von Artikeln.</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                        <div class="bg-white rounded-lg p-3 border border-[var(--ui-border)]/40">
                            <div class="font-medium text-[var(--ui-secondary)] text-sm mb-1">Vor-Ort-Verzehr</div>
                            <div class="text-xs">Gast isst vor Ort im Restaurant/auf dem Event. Voller MwSt-Satz (19%).</div>
                        </div>
                        <div class="bg-white rounded-lg p-3 border border-[var(--ui-border)]/40">
                            <div class="font-medium text-[var(--ui-secondary)] text-sm mb-1">Außer-Haus / Lieferung</div>
                            <div class="text-xs">Essen zum Mitnehmen oder Lieferung. Ermäßigter Satz (7%) auf Speisen.</div>
                        </div>
                        <div class="bg-white rounded-lg p-3 border border-[var(--ui-border)]/40">
                            <div class="font-medium text-[var(--ui-secondary)] text-sm mb-1">Online-Shop</div>
                            <div class="text-xs">Verkauf über Website. Eigene Preislisten und Verfügbarkeiten möglich.</div>
                        </div>
                    </div>
                    <p class="text-xs"><strong class="text-[var(--ui-secondary)]">Wichtig:</strong> Jede Kombination aus Steuerkategorie + Verkaufskontext ergibt einen konkreten Steuersatz in der Steuer Matrix.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Formular --}}
                    <div class="space-y-4">
                        <x-ui-input-text
                            name="context_name"
                            label="Kontext Name"
                            wire:model="context_name"
                            placeholder="z.B. Vor-Ort-Verzehr, Lieferung, Online-Shop..."
                            required
                            :errorKey="'context_name'"
                        />
                        <x-ui-input-textarea
                            name="context_description"
                            label="Kontext Beschreibung"
                            wire:model="context_description"
                            rows="3"
                            placeholder="In welcher Situation wird dieser Kontext verwendet? Welche Steuerregeln gelten?"
                            :errorKey="'context_description'"
                        />
                        <x-ui-button wire:click="saveSalesContext" variant="primary">
                            Kontext anlegen
                        </x-ui-button>
                    </div>

                    {{-- Bestehende Kontexte --}}
                    <div class="bg-[var(--ui-muted-5)] rounded-lg p-4">
                        <h3 class="text-sm font-medium text-[var(--ui-secondary)] mb-3">Bestehende Verkaufskontexte</h3>
                        <div class="divide-y divide-[var(--ui-border)]">
                            @forelse($contexts as $context)
                                <div class="py-2 group">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-[var(--ui-secondary)]">{{ $context->name }}</span>
                                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button wire:click="editContext({{ $context->id }})"
                                                    class="p-1.5 rounded-lg text-[var(--ui-muted)] hover:bg-[var(--ui-muted-10)] hover:text-[var(--ui-secondary)] transition-all">
                                                <x-heroicon-s-pencil-square class="w-4 h-4"/>
                                            </button>
                                            <button @click="confirmDeleteContext = {{ $context->id }}"
                                                    class="p-1.5 rounded-lg text-[var(--ui-muted)] hover:bg-red-50 hover:text-red-600 transition-all">
                                                <x-heroicon-s-trash class="w-4 h-4"/>
                                            </button>
                                        </div>
                                    </div>
                                    @if($context->description)
                                        <p class="text-xs text-[var(--ui-muted)] mt-1">{{ $context->description }}</p>
                                    @endif
                                </div>

                                {{-- Inline Delete Confirmation --}}
                                <div x-show="confirmDeleteContext === {{ $context->id }}" x-cloak
                                     class="py-2 px-3 bg-red-50 rounded-lg flex items-center justify-between">
                                    <span class="text-sm text-red-700">Wirklich löschen?</span>
                                    <div class="flex items-center gap-2">
                                        <button @click="confirmDeleteContext = null"
                                                class="text-xs px-2 py-1 rounded bg-white text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)]">
                                            Abbrechen
                                        </button>
                                        <button wire:click="deleteContext({{ $context->id }})"
                                                @click="confirmDeleteContext = null"
                                                class="text-xs px-2 py-1 rounded bg-red-600 text-white hover:bg-red-700">
                                            Löschen
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="py-4 text-sm text-[var(--ui-muted)] text-center">Keine Verkaufskontexte vorhanden.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </x-ui-panel>
        </section>

        {{-- ═══════════════════════════════════════ --}}
        {{-- STEUER MATRIX                           --}}
        {{-- ═══════════════════════════════════════ --}}
        <section id="taxrules" class="scroll-mt-4">
            <x-ui-panel title="Steuer Matrix">
                <div class="mb-6 bg-[var(--ui-muted-5)] rounded-lg p-4 text-sm text-[var(--ui-muted)]">
                    <p class="font-medium text-[var(--ui-secondary)] mb-2">So funktioniert die Steuer Matrix</p>
                    <p class="mb-3">Die Matrix wird <strong class="text-[var(--ui-secondary)]">automatisch</strong> aus deinen Steuerkategorien und Verkaufskontexten erzeugt. Jede Zeile zeigt: Welcher Steuersatz gilt für welche Kombination?</p>
                    <div class="bg-white rounded-lg p-3 border border-[var(--ui-border)]/40 text-xs">
                        <div class="font-medium text-[var(--ui-secondary)] mb-2">Beispiel-Ablauf:</div>
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
                    <div class="bg-[var(--ui-muted-5)] rounded-t-lg">
                        <div class="grid grid-cols-12 px-6 py-3 text-xs font-medium text-[var(--ui-muted)] uppercase tracking-wider">
                            <div class="col-span-4">Verkaufskontext</div>
                            <div class="col-span-4">Steuerkategorie</div>
                            <div class="col-span-3">Steuersatz (%)</div>
                            <div class="col-span-1 text-right">Aktionen</div>
                        </div>
                    </div>

                    {{-- Table Body --}}
                    <div class="divide-y divide-[var(--ui-border)] bg-white rounded-b-lg border border-[var(--ui-border)] border-t-0">
                        @foreach($matrix as $rule)
                            <livewire:commerce.settings.tax-rule-row :rule="$rule" :key="'tax-rule-' . $rule->id" />
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12 text-[var(--ui-muted)]">
                        <x-heroicon-o-table-cells class="w-12 h-12 mx-auto mb-4 text-[var(--ui-muted-20)]"/>
                        <p class="font-medium text-[var(--ui-secondary)]">Noch keine Steuerregeln</p>
                        <p class="text-sm mt-2">Erstelle zuerst mindestens eine Steuerkategorie und einen Verkaufskontext.<br>Die Matrix wird dann automatisch generiert.</p>
                    </div>
                @endif
            </x-ui-panel>
        </section>
    </x-ui-page-container>

    {{-- ═══════════════════════════════════════ --}}
    {{-- MODALS                                  --}}
    {{-- ═══════════════════════════════════════ --}}

    {{-- Edit Tax Category Modal --}}
    <div x-show="editCategoryOpen" x-cloak @keydown.escape.window="editCategoryOpen = false">
        <x-ui-modal size="md">
            <x-slot name="header">Steuerkategorie bearbeiten</x-slot>
            <div class="space-y-4">
                <x-ui-input-text name="editCategoryName" label="Name" wire:model="editCategoryName" required :errorKey="'editCategoryName'" />
                <x-ui-input-number name="editCategoryRate" label="Standard-Steuersatz (%)" wire:model="editCategoryRate" min="0" max="100" step="0.01" required :errorKey="'editCategoryRate'" />
            </div>
            <x-slot name="footer">
                <div class="flex justify-end gap-2">
                    <x-ui-button type="button" variant="secondary-outline" @click="editCategoryOpen = false">Abbrechen</x-ui-button>
                    <x-ui-button type="button" variant="primary" wire:click="updateCategory" @click="editCategoryOpen = false">Speichern</x-ui-button>
                </div>
            </x-slot>
        </x-ui-modal>
    </div>

    {{-- Edit Sales Context Modal --}}
    <div x-show="editContextOpen" x-cloak @keydown.escape.window="editContextOpen = false">
        <x-ui-modal size="md">
            <x-slot name="header">Verkaufskontext bearbeiten</x-slot>
            <div class="space-y-4">
                <x-ui-input-text name="editContextName" label="Kontext Name" wire:model="editContextName" required :errorKey="'editContextName'" />
                <x-ui-input-textarea name="editContextDescription" label="Kontext Beschreibung" wire:model="editContextDescription" rows="3" :errorKey="'editContextDescription'" />
            </div>
            <x-slot name="footer">
                <div class="flex justify-end gap-2">
                    <x-ui-button type="button" variant="secondary-outline" @click="editContextOpen = false">Abbrechen</x-ui-button>
                    <x-ui-button type="button" variant="primary" wire:click="updateContext" @click="editContextOpen = false">Speichern</x-ui-button>
                </div>
            </x-slot>
        </x-ui-modal>
    </div>

    {{-- Edit Article Type Modal --}}
    <div x-show="editTypeOpen" x-cloak @keydown.escape.window="editTypeOpen = false">
        <x-ui-modal size="md">
            <x-slot name="header">Artikel-Typ bearbeiten</x-slot>
            <div class="space-y-4">
                <x-ui-input-text name="editTypeName" label="Name" wire:model="editTypeName" required :errorKey="'editTypeName'" />
                <x-ui-input-textarea name="editTypeDescription" label="Beschreibung" wire:model="editTypeDescription" rows="2" :errorKey="'editTypeDescription'" />
                <div>
                    <label class="block text-sm font-medium text-[var(--ui-secondary)] mb-1">Farbe</label>
                    <input type="color" wire:model="editTypeColor" class="h-10 w-full rounded-lg cursor-pointer border border-[var(--ui-border)]">
                </div>
            </div>
            <x-slot name="footer">
                <div class="flex justify-end gap-2">
                    <x-ui-button type="button" variant="secondary-outline" @click="editTypeOpen = false">Abbrechen</x-ui-button>
                    <x-ui-button type="button" variant="primary" wire:click="updateArticleType" @click="editTypeOpen = false">Speichern</x-ui-button>
                </div>
            </x-slot>
        </x-ui-modal>
    </div>

    {{-- Edit Article Category Modal --}}
    <div x-show="editArticleCategoryOpen" x-cloak @keydown.escape.window="editArticleCategoryOpen = false">
        <x-ui-modal size="md">
            <x-slot name="header">Artikel-Kategorie bearbeiten</x-slot>
            <div class="space-y-4">
                <x-ui-input-text name="editCatName" label="Name" wire:model="editCatName" required :errorKey="'editCatName'" />
                <x-ui-input-textarea name="editCatDescription" label="Beschreibung" wire:model="editCatDescription" rows="2" :errorKey="'editCatDescription'" />
                <x-ui-input-select
                    name="editCatParentId"
                    label="Übergeordnete Kategorie"
                    :options="$allArticleCategories"
                    optionValue="id"
                    optionLabel="display_name"
                    :nullable="true"
                    nullLabel="-- Hauptkategorie (oberste Ebene) --"
                    wire:model="editCatParentId"
                    :errorKey="'editCatParentId'"
                />
                <div>
                    <label class="block text-sm font-medium text-[var(--ui-secondary)] mb-1">Farbe</label>
                    <input type="color" wire:model="editCatColor" class="h-10 w-full rounded-lg cursor-pointer border border-[var(--ui-border)]">
                </div>
            </div>
            <x-slot name="footer">
                <div class="flex justify-end gap-2">
                    <x-ui-button type="button" variant="secondary-outline" @click="editArticleCategoryOpen = false">Abbrechen</x-ui-button>
                    <x-ui-button type="button" variant="primary" wire:click="updateArticleCategory" @click="editArticleCategoryOpen = false">Speichern</x-ui-button>
                </div>
            </x-slot>
        </x-ui-modal>
    </div>
</x-ui-page>
