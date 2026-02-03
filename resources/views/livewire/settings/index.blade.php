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
    scrollToSection(sectionId) {
        const section = document.getElementById(sectionId);
        if (section) {
            section.scrollIntoView({ behavior: 'smooth' });
        }
        this.selectedTab = sectionId;
    }
}">
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
                            @forelse($matrix->pluck('taxCategory')->unique() as $category)
                                <div class="py-2 flex items-center justify-between">
                                    <span class="text-sm text-[var(--ui-secondary)]">{{ $category->name }}</span>
                                    <span class="text-sm font-medium text-[var(--ui-secondary)]">{{ $category->default_rate }}%</span>
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
                            @forelse($matrix->pluck('salesContext')->unique() as $context)
                                <div class="py-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-[var(--ui-secondary)]">{{ $context->name }}</span>
                                    </div>
                                    @if($context->description)
                                        <p class="text-sm text-[var(--ui-muted)] mt-1">{{ $context->description }}</p>
                                    @endif
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
</x-ui-page>
