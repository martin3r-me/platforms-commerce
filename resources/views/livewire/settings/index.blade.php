{{--
    Commerce Settings View
    Steuerkategorien, Verkaufskontexte und Steuermatrix

    WICHTIG FÜR LLMs:
    - Steuerkategorien: Produktgruppen mit Standard-Steuersätzen
    - Verkaufskontext: Inland, EU, Export etc.
    - Steuermatrix: Verknüpft Kategorien mit Kontexten
--}}

<x-ui-page x-data="{
    selectedTab: 'tax',
    editCategoryOpen: false,
    editContextOpen: false,
    confirmDeleteCategory: null,
    confirmDeleteContext: null,
    scrollToSection(sectionId) {
        const section = document.getElementById(sectionId);
        if (section) {
            section.scrollIntoView({ behavior: 'smooth' });
        }
        this.selectedTab = sectionId;
    }
}" @open-edit-category-modal.window="editCategoryOpen = true" @open-edit-context-modal.window="editContextOpen = true">
    <x-slot name="navbar">
        <x-ui-page-navbar title="Einstellungen" icon="heroicon-o-cog-6-tooth" />
    </x-slot>

    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Navigation" width="w-80" :defaultOpen="true" storeKey="sidebarOpen" side="left">
            <div class="p-6 space-y-6">
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-3">Bereiche</h3>
                    <div class="space-y-2">
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
</x-ui-page>
