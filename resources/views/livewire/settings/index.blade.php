{{--
    Commerce Settings View
    Steuerkategorien, Verkaufskontexte und Steuermatrix

    WICHTIG FÜR LLMs:
    - Steuerkategorien: Produktgruppen mit Standard-Steuersätzen
    - Verkaufskontext: Inland, EU, Export etc.
    - Steuermatrix: Verknüpft Kategorien mit Kontexten
--}}

<x-ui-page x-data="{
    selectedTab: 'articletypes',
    editCategoryOpen: false,
    editContextOpen: false,
    editTypeOpen: false,
    confirmDeleteCategory: null,
    confirmDeleteContext: null,
    confirmDeleteType: null,
    scrollToSection(sectionId) {
        const section = document.getElementById(sectionId);
        if (section) {
            section.scrollIntoView({ behavior: 'smooth' });
        }
        this.selectedTab = sectionId;
    }
}" @open-edit-category-modal.window="editCategoryOpen = true" @open-edit-context-modal.window="editContextOpen = true" @open-edit-type-modal.window="editTypeOpen = true">
    <x-slot name="navbar">
        <x-ui-page-navbar title="Einstellungen" icon="heroicon-o-cog-6-tooth" />
    </x-slot>

    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Navigation" width="w-80" :defaultOpen="true" storeKey="sidebarOpen" side="left">
            <div class="p-6 space-y-6">
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-3">Bereiche</h3>
                    <div class="space-y-2">
                        <button @click="scrollToSection('articletypes')"
                                :class="{ 'bg-[var(--ui-primary-light)] text-[var(--ui-primary)]': selectedTab === 'articletypes' }"
                                class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-all">
                            Artikel-Typen
                        </button>
                        <button @click="scrollToSection('tax')"
                                :class="{ 'bg-[var(--ui-primary-light)] text-[var(--ui-primary)]': selectedTab === 'tax' }"
                                class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-all">
                            Steuerkategorien
                        </button>
                        <button @click="scrollToSection('salescontext')"
                                :class="{ 'bg-[var(--ui-primary-light)] text-[var(--ui-primary)]': selectedTab === 'salescontext' }"
                                class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-all">
                            Verkaufskontext
                        </button>
                        <button @click="scrollToSection('taxrules')"
                                :class="{ 'bg-[var(--ui-primary-light)] text-[var(--ui-primary)]': selectedTab === 'taxrules' }"
                                class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition-all">
                            Steuer Matrix
                        </button>
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    <x-ui-page-container spacing="space-y-8">
        {{-- Artikel-Typen --}}
        <section id="articletypes" class="scroll-mt-4">
            <x-ui-panel title="Artikel-Typen">
                <div class="mb-6 bg-[var(--ui-muted-5)] rounded-lg p-4 text-sm text-[var(--ui-muted)]">
                    <p class="font-medium text-[var(--ui-secondary)] mb-2">Über Artikel-Typen</p>
                    <p class="mb-2">Artikel-Typen ermöglichen es Ihnen, Artikel in verschiedene Kategorien einzuteilen (z.B. Food, Non-Food, Dienstleistungen).</p>
                    <ul class="list-disc list-inside space-y-1 text-sm">
                        <li>Erstellen Sie Typen für verschiedene Artikelgruppen</li>
                        <li>Weisen Sie Artikeln den passenden Typ zu</li>
                        <li>Produkte erben den Typ vom zugewiesenen Artikel</li>
                    </ul>
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
                            placeholder="Beschreibung des Artikel-Typs..."
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
                                                <span class="w-4 h-4 rounded-full" style="background-color: {{ $type->color }}"></span>
                                            @endif
                                            <span class="text-sm font-medium text-[var(--ui-secondary)]">{{ $type->name }}</span>
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
                                        <p class="text-sm text-[var(--ui-muted)] mt-1">{{ $type->description }}</p>
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
                                <div class="py-2 text-sm text-[var(--ui-muted)]">Keine Artikel-Typen vorhanden.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </x-ui-panel>
        </section>

        {{-- Steuerkategorien --}}
        <section id="tax" class="scroll-mt-4">
            <x-ui-panel title="Steuerkategorien">
                <div class="mb-6 bg-[var(--ui-muted-5)] rounded-lg p-4 text-sm text-[var(--ui-muted)]">
                    <p class="font-medium text-[var(--ui-secondary)] mb-2">Über Steuerkategorien</p>
                    <p class="mb-2">Steuerkategorien ermöglichen es Ihnen, Produkte oder Dienstleistungen in verschiedene Gruppen einzuteilen, die unterschiedlichen Steuersätzen unterliegen können.</p>
                    <ul class="list-disc list-inside space-y-1 text-sm">
                        <li>Erstellen Sie Kategorien für verschiedene Produktgruppen (z.B. Lebensmittel, Elektronik)</li>
                        <li>Legen Sie Standard-Steuersätze für jede Kategorie fest</li>
                        <li>Weisen Sie später Ihren Produkten die passende Kategorie zu</li>
                    </ul>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Formular --}}
                    <div class="space-y-4">
                        <x-ui-input-text
                            name="name"
                            label="Name"
                            wire:model="name"
                            placeholder="z.B. Ermäßigter Satz, Voller Satz..."
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
                                <div class="py-2 text-sm text-[var(--ui-muted)]">Keine Kategorien vorhanden.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </x-ui-panel>
        </section>

        {{-- Verkaufskontext --}}
        <section id="salescontext" class="scroll-mt-4">
            <x-ui-panel title="Verkaufskontext">
                <div class="mb-6 bg-[var(--ui-muted-5)] rounded-lg p-4 text-sm text-[var(--ui-muted)]">
                    <p class="font-medium text-[var(--ui-secondary)] mb-2">Über Verkaufskontexte</p>
                    <p class="mb-2">Der Verkaufskontext definiert die Umstände eines Verkaufs und beeinflusst die anzuwendenden Steuersätze.</p>
                    <ul class="list-disc list-inside space-y-1 text-sm">
                        <li>Definieren Sie verschiedene Verkaufssituationen (z.B. Inland, EU, Export)</li>
                        <li>Berücksichtigen Sie Standorte von Käufer und Verkäufer</li>
                        <li>Ermöglichen Sie spezielle Kontexte für bestimmte Verkaufsaktionen</li>
                    </ul>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Formular --}}
                    <div class="space-y-4">
                        <x-ui-input-text
                            name="context_name"
                            label="Kontext Name"
                            wire:model="context_name"
                            placeholder="z.B. Inland, EU-Ausland, Drittland..."
                            required
                            :errorKey="'context_name'"
                        />
                        <x-ui-input-textarea
                            name="context_description"
                            label="Kontext Beschreibung"
                            wire:model="context_description"
                            rows="3"
                            placeholder="Beschreibung des Verkaufskontexts..."
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
                                        <p class="text-sm text-[var(--ui-muted)] mt-1">{{ $context->description }}</p>
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
                                <div class="py-2 text-sm text-[var(--ui-muted)]">Keine Kontexte vorhanden.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </x-ui-panel>
        </section>

        {{-- Steuer Matrix --}}
        <section id="taxrules" class="scroll-mt-4">
            <x-ui-panel title="Steuer Matrix">
                <div class="mb-6 bg-[var(--ui-muted-5)] rounded-lg p-4 text-sm text-[var(--ui-muted)]">
                    <p class="font-medium text-[var(--ui-secondary)] mb-2">Über die Steuermatrix</p>
                    <p class="mb-2">Die Steuermatrix verbindet Steuerkategorien mit Verkaufskontexten und bestimmt den anzuwendenden Steuersatz.</p>
                    <ul class="list-disc list-inside space-y-1 text-sm">
                        <li>Definieren Sie Steuersätze für jede Kombination aus Kategorie und Kontext</li>
                        <li>Das System wählt automatisch den korrekten Satz basierend auf diesen Regeln</li>
                        <li>Änderungen werden sofort auf alle relevanten Verkäufe angewendet</li>
                    </ul>
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
                        <p>Keine Steuerregeln vorhanden.</p>
                        <p class="text-sm mt-2">Erstellen Sie zuerst Steuerkategorien und Verkaufskontexte.</p>
                    </div>
                @endif
            </x-ui-panel>
        </section>
    </x-ui-page-container>

    {{-- Edit Category Modal --}}
    <div x-show="editCategoryOpen" x-cloak @keydown.escape.window="editCategoryOpen = false">
        <x-ui-modal size="md">
            <x-slot name="header">
                Steuerkategorie bearbeiten
            </x-slot>

            <div class="space-y-4">
                <x-ui-input-text
                    name="editCategoryName"
                    label="Name"
                    wire:model="editCategoryName"
                    required
                    :errorKey="'editCategoryName'"
                />
                <x-ui-input-number
                    name="editCategoryRate"
                    label="Standard-Steuersatz (%)"
                    wire:model="editCategoryRate"
                    min="0"
                    max="100"
                    step="0.01"
                    required
                    :errorKey="'editCategoryRate'"
                />
            </div>

            <x-slot name="footer">
                <div class="d-flex justify-end gap-2">
                    <x-ui-button type="button" variant="secondary-outline" @click="editCategoryOpen = false">
                        Abbrechen
                    </x-ui-button>
                    <x-ui-button type="button" variant="primary" wire:click="updateCategory" @click="editCategoryOpen = false">
                        Speichern
                    </x-ui-button>
                </div>
            </x-slot>
        </x-ui-modal>
    </div>

    {{-- Edit Context Modal --}}
    <div x-show="editContextOpen" x-cloak @keydown.escape.window="editContextOpen = false">
        <x-ui-modal size="md">
            <x-slot name="header">
                Verkaufskontext bearbeiten
            </x-slot>

            <div class="space-y-4">
                <x-ui-input-text
                    name="editContextName"
                    label="Kontext Name"
                    wire:model="editContextName"
                    required
                    :errorKey="'editContextName'"
                />
                <x-ui-input-textarea
                    name="editContextDescription"
                    label="Kontext Beschreibung"
                    wire:model="editContextDescription"
                    rows="3"
                    :errorKey="'editContextDescription'"
                />
            </div>

            <x-slot name="footer">
                <div class="d-flex justify-end gap-2">
                    <x-ui-button type="button" variant="secondary-outline" @click="editContextOpen = false">
                        Abbrechen
                    </x-ui-button>
                    <x-ui-button type="button" variant="primary" wire:click="updateContext" @click="editContextOpen = false">
                        Speichern
                    </x-ui-button>
                </div>
            </x-slot>
        </x-ui-modal>
    </div>

    {{-- Edit Article Type Modal --}}
    <div x-show="editTypeOpen" x-cloak @keydown.escape.window="editTypeOpen = false">
        <x-ui-modal size="md">
            <x-slot name="header">
                Artikel-Typ bearbeiten
            </x-slot>

            <div class="space-y-4">
                <x-ui-input-text
                    name="editTypeName"
                    label="Name"
                    wire:model="editTypeName"
                    required
                    :errorKey="'editTypeName'"
                />
                <x-ui-input-textarea
                    name="editTypeDescription"
                    label="Beschreibung"
                    wire:model="editTypeDescription"
                    rows="2"
                    :errorKey="'editTypeDescription'"
                />
                <div>
                    <label class="block text-sm font-medium text-[var(--ui-secondary)] mb-1">Farbe</label>
                    <input type="color"
                           wire:model="editTypeColor"
                           class="h-10 w-full rounded-lg cursor-pointer border border-[var(--ui-border)]">
                </div>
            </div>

            <x-slot name="footer">
                <div class="d-flex justify-end gap-2">
                    <x-ui-button type="button" variant="secondary-outline" @click="editTypeOpen = false">
                        Abbrechen
                    </x-ui-button>
                    <x-ui-button type="button" variant="primary" wire:click="updateArticleType" @click="editTypeOpen = false">
                        Speichern
                    </x-ui-button>
                </div>
            </x-slot>
        </x-ui-modal>
    </div>
</x-ui-page>
