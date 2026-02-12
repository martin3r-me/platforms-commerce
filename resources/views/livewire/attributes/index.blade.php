{{--
    Attributes Index View
    Attributsets verwalten

    WICHTIG FÜR LLMs:
    - Zeigt alle Attributsets des Teams
    - Ermöglicht das Anlegen neuer Attributsets
    - Verwendet moderne UI-Komponenten
--}}

<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Attribute" icon="heroicon-o-tag" />
    </x-slot>

    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Navigation" width="w-80" :defaultOpen="true" storeKey="sidebarOpen" side="left">
            <div class="p-6 space-y-6">
                {{-- Navigation --}}
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-3">Navigation</h3>
                    <div class="space-y-2">
                        <x-ui-button variant="secondary-outline" size="sm" :href="route('commerce.index')" wire:navigate class="w-full">
                            <span class="flex items-center gap-2">
                                @svg('heroicon-o-arrow-left', 'w-4 h-4')
                                Commerce Dashboard
                            </span>
                        </x-ui-button>
                    </div>
                </div>

                {{-- Quick Stats --}}
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-3">Statistiken</h3>
                    <div class="space-y-3">
                        <div class="p-3 bg-[var(--ui-muted-5)] rounded-lg border border-[var(--ui-border)]/40">
                            <div class="text-xs text-[var(--ui-muted)]">Attributsets</div>
                            <div class="text-lg font-bold text-[var(--ui-secondary)]">{{ $this->attributeSets->count() }}</div>
                        </div>
                    </div>
                </div>

                {{-- Info --}}
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-3">Info</h3>
                    <div class="text-sm text-[var(--ui-muted)] space-y-2">
                        <p>Attributsets ermöglichen es, Artikel mit zusätzlichen Eigenschaften zu versehen.</p>
                        <p>Beispiele: Farbe, Größe, Material</p>
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    <x-slot name="activity">
        <x-ui-page-sidebar title="Aktivitäten" width="w-80" :defaultOpen="false" storeKey="activityOpen" side="right">
            <div class="p-4 space-y-4">
                <div class="text-sm text-[var(--ui-muted)]">Letzte Aktivitäten</div>
                <div class="space-y-3 text-sm">
                    <div class="p-2 rounded border border-[var(--ui-border)]/60 bg-[var(--ui-muted-5)]">
                        <div class="font-medium text-[var(--ui-secondary)] truncate">Attribute-Übersicht geladen</div>
                        <div class="text-[var(--ui-muted)]">Gerade eben</div>
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    <x-ui-page-container spacing="space-y-8">
        {{-- Neues Attributset anlegen --}}
        <x-ui-panel title="Neues Attributset anlegen">
            <div class="mb-6 bg-[var(--ui-muted-5)] rounded-lg p-4 text-sm text-[var(--ui-muted)]">
                <p class="font-medium text-[var(--ui-secondary)] mb-2">Über Attributsets</p>
                <p class="mb-2">Attributsets sind Gruppen von Eigenschaften, die Sie Ihren Artikeln zuweisen können.</p>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    <li>Erstellen Sie z.B. ein Attributset "Farbe" mit Items wie "Rot", "Blau", "Grün"</li>
                    <li>Oder ein Attributset "Größe" mit Items wie "S", "M", "L", "XL"</li>
                    <li>Mehrfachauswahl erlaubt die Auswahl mehrerer Items pro Artikel</li>
                </ul>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Formular --}}
                <div class="space-y-4">
                    <x-ui-input-text
                        name="name"
                        label="Name"
                        wire:model="name"
                        placeholder="z.B. Farbe, Größe, Material..."
                        required
                        :errorKey="'name'"
                    />
                    <div>
                        <label class="block text-sm font-medium text-[var(--ui-secondary)] mb-1">Farbe</label>
                        <input type="color"
                               wire:model="color"
                               class="w-full h-10 bg-[var(--ui-muted-5)] border-0 rounded-lg px-3 text-sm ring-1 ring-[var(--ui-border)] focus:ring-2 focus:ring-[var(--ui-primary)]">
                    </div>
                    <div class="flex flex-col gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox"
                                   wire:model="is_multiselect"
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-[var(--ui-muted-10)] peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-[var(--ui-primary-light)] rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-[var(--ui-border)] after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[var(--ui-primary)]"></div>
                            <span class="ml-3 text-sm font-medium text-[var(--ui-secondary)]">Mehrfachauswahl</span>
                        </label>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox"
                                   wire:model="is_required"
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-[var(--ui-muted-10)] peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-[var(--ui-primary-light)] rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-[var(--ui-border)] after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[var(--ui-primary)]"></div>
                            <span class="ml-3 text-sm font-medium text-[var(--ui-secondary)]">Pflichtfeld</span>
                        </label>
                    </div>
                    <x-ui-button wire:click="createAttributeSet" variant="primary">
                        Attributset anlegen
                    </x-ui-button>
                </div>

                {{-- Vorschau --}}
                <div class="bg-[var(--ui-muted-5)] rounded-lg p-4">
                    <h3 class="text-sm font-medium text-[var(--ui-secondary)] mb-3">Vorschau</h3>
                    <div class="p-4 bg-white rounded-lg border border-[var(--ui-border)]">
                        <div class="flex items-center gap-3">
                            @if($color)
                                <div class="w-6 h-6 rounded-full" style="background-color: {{ $color }};"></div>
                            @else
                                <div class="w-6 h-6 rounded-full bg-[var(--ui-muted-10)]"></div>
                            @endif
                            <div>
                                <div class="font-medium text-[var(--ui-secondary)]">{{ $name ?: 'Attributset Name' }}</div>
                                <div class="text-xs text-[var(--ui-muted)]">
                                    @if($is_multiselect) Mehrfachauswahl @else Einzelauswahl @endif
                                    @if($is_required) &bull; Pflichtfeld @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui-panel>

        {{-- Bestehende Attributsets --}}
        <x-ui-panel title="Bestehende Attributsets" :subtitle="$this->attributeSets->count() . ' Attributset(s)'">
            @if($this->attributeSets->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($this->attributeSets as $set)
                        <a href="{{ route('commerce.attributes.show', $set) }}"
                           class="block p-4 rounded-lg border border-[var(--ui-border)] bg-white hover:bg-[var(--ui-muted-5)] hover:border-[var(--ui-primary-light)] transition-all group"
                           wire:navigate>
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full flex-shrink-0"
                                     style="background-color: {{ $set->color ?? 'var(--ui-muted-10)' }};"></div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-[var(--ui-secondary)] group-hover:text-[var(--ui-primary)] truncate">
                                        {{ $set->name }}
                                    </div>
                                    <div class="text-xs text-[var(--ui-muted)] mt-1">
                                        {{ $set->attributeSetItems->count() }} Item(s)
                                        @if($set->is_multiselect)
                                            &bull; Mehrfachauswahl
                                        @endif
                                        @if($set->is_required)
                                            &bull; Pflicht
                                        @endif
                                    </div>
                                </div>
                                <x-heroicon-o-chevron-right class="w-5 h-5 text-[var(--ui-muted)] group-hover:text-[var(--ui-primary)] flex-shrink-0"/>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 text-[var(--ui-muted)]">
                    <x-heroicon-o-tag class="w-12 h-12 mx-auto mb-4 text-[var(--ui-muted-20)]"/>
                    <p>Keine Attributsets vorhanden.</p>
                    <p class="text-sm mt-2">Erstellen Sie Ihr erstes Attributset mit dem Formular oben.</p>
                </div>
            @endif
        </x-ui-panel>
    </x-ui-page-container>
</x-ui-page>
