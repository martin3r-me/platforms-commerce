{{--
    Attributes Index View
    Attributsets verwalten
--}}

<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Attribute" icon="heroicon-o-tag" />
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Commerce', 'href' => route('commerce.index'), 'icon' => 'shopping-bag'],
            ['label' => 'Attribute'],
        ]" />
    </x-slot>

    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Navigation" width="w-80" :defaultOpen="true" storeKey="sidebarOpen" side="left">
            <div class="p-6 space-y-6">
                <div>
                    <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-3">Navigation</h3>
                    <div class="space-y-1">
                        <a href="{{ route('commerce.index') }}" wire:navigate
                           class="flex items-center gap-2 px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 text-[13px] font-medium hover:bg-gray-50 transition-colors w-full">
                            @svg('heroicon-o-arrow-left', 'w-4 h-4')
                            Commerce Dashboard
                        </a>
                    </div>
                </div>

                <div>
                    <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-3">Statistiken</h3>
                    <div class="space-y-2">
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="text-[11px] text-gray-500">Attributsets</div>
                            <div class="text-lg font-bold text-gray-900">{{ $this->attributeSets->count() }}</div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-3">Info</h3>
                    <div class="text-[13px] text-gray-500 space-y-2">
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
                <div class="text-[13px] text-gray-500">Letzte Aktivitäten</div>
                <div class="space-y-2">
                    <div class="p-2 rounded-md border border-gray-200 bg-gray-50">
                        <div class="font-medium text-gray-900 text-[13px] truncate">Attribute-Übersicht geladen</div>
                        <div class="text-[11px] text-gray-500">Gerade eben</div>
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    <x-ui-page-container spacing="space-y-8">
        {{-- Neues Attributset anlegen --}}
        <section class="bg-white rounded-lg border border-gray-200">
            <div class="px-4 py-3 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900">Neues Attributset anlegen</h3>
            </div>
            <div class="p-4">
                <div class="mb-6 p-3 rounded-md bg-blue-50 border border-blue-200 text-[13px] text-blue-800">
                    <p class="font-medium text-blue-900 mb-2">Über Attributsets</p>
                    <p class="mb-2">Attributsets sind Gruppen von Eigenschaften, die Sie Ihren Artikeln zuweisen können.</p>
                    <ul class="list-disc list-inside space-y-1 text-[13px]">
                        <li>Erstellen Sie z.B. ein Attributset "Farbe" mit Items wie "Rot", "Blau", "Grün"</li>
                        <li>Oder ein Attributset "Größe" mit Items wie "S", "M", "L", "XL"</li>
                        <li>Mehrfachauswahl erlaubt die Auswahl mehrerer Items pro Artikel</li>
                    </ul>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Formular --}}
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[11px] font-medium text-gray-500 mb-1">Name</label>
                            <input type="text"
                                   wire:model="name"
                                   placeholder="z.B. Farbe, Größe, Material..."
                                   required
                                   class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                            @error('name') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-gray-500 mb-1">Farbe</label>
                            <input type="color"
                                   wire:model="color"
                                   class="w-full h-10 bg-white border border-gray-300 rounded-md px-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                        </div>
                        <div class="flex flex-col gap-3">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox"
                                       wire:model="is_multiselect"
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-[#166EE1]/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#166EE1]"></div>
                                <span class="ml-3 text-[13px] font-medium text-gray-900">Mehrfachauswahl</span>
                            </label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox"
                                       wire:model="is_required"
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-[#166EE1]/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#166EE1]"></div>
                                <span class="ml-3 text-[13px] font-medium text-gray-900">Pflichtfeld</span>
                            </label>
                        </div>
                        <button wire:click="createAttributeSet"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-[#166EE1] text-white text-[13px] font-medium hover:bg-blue-700 transition-colors">
                            Attributset anlegen
                        </button>
                    </div>

                    {{-- Vorschau --}}
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-[13px] font-medium text-gray-900 mb-3">Vorschau</h3>
                        <div class="p-4 bg-white rounded-lg border border-gray-200">
                            <div class="flex items-center gap-3">
                                @if($color)
                                    <div class="w-6 h-6 rounded-full" style="background-color: {{ $color }};"></div>
                                @else
                                    <div class="w-6 h-6 rounded-full bg-gray-200"></div>
                                @endif
                                <div>
                                    <div class="font-medium text-gray-900 text-[13px]">{{ $name ?: 'Attributset Name' }}</div>
                                    <div class="text-[11px] text-gray-500">
                                        @if($is_multiselect) Mehrfachauswahl @else Einzelauswahl @endif
                                        @if($is_required) &bull; Pflichtfeld @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Bestehende Attributsets --}}
        <section class="bg-white rounded-lg border border-gray-200">
            <div class="px-4 py-3 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900">Bestehende Attributsets</h3>
                <p class="text-[11px] text-gray-500">{{ $this->attributeSets->count() }} Attributset(s)</p>
            </div>
            <div class="p-4">
                @if($this->attributeSets->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($this->attributeSets as $set)
                            <a href="{{ route('commerce.attributes.show', $set) }}"
                               class="block p-4 rounded-lg border border-gray-200 bg-white hover:bg-blue-50/50 hover:border-[#166EE1]/30 transition-all group"
                               wire:navigate>
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full flex-shrink-0"
                                         style="background-color: {{ $set->color ?? '#e5e7eb' }};"></div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium text-gray-900 group-hover:text-[#166EE1] text-[13px] truncate">
                                            {{ $set->name }}
                                        </div>
                                        <div class="text-[11px] text-gray-500 mt-1">
                                            {{ $set->attributeSetItems->count() }} Item(s)
                                            @if($set->is_multiselect)
                                                &bull; Mehrfachauswahl
                                            @endif
                                            @if($set->is_required)
                                                &bull; Pflicht
                                            @endif
                                        </div>
                                    </div>
                                    <x-heroicon-o-chevron-right class="w-5 h-5 text-gray-400 group-hover:text-[#166EE1] flex-shrink-0"/>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12 text-gray-500">
                        <x-heroicon-o-tag class="w-12 h-12 mx-auto mb-4 text-gray-300"/>
                        <p class="text-[13px]">Keine Attributsets vorhanden.</p>
                        <p class="text-[13px] mt-2">Erstellen Sie Ihr erstes Attributset mit dem Formular oben.</p>
                    </div>
                @endif
            </div>
        </section>
    </x-ui-page-container>
</x-ui-page>
