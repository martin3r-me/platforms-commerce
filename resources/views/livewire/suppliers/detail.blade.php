<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="" />
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Commerce', 'href' => route('commerce.index'), 'icon' => 'shopping-bag'],
            ['label' => 'Lieferanten', 'href' => route('commerce.suppliers.index')],
            ['label' => $commerceSupplier->name],
        ]" />
    </x-slot>

    <x-ui-page-container>
        <div class="space-y-6">
            {{-- Header --}}
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-gray-900">{{ $commerceSupplier->name }}</h1>
                    @if($commerceSupplier->description)
                        <p class="text-[13px] text-gray-500 mt-1">{{ $commerceSupplier->description }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <button
                        wire:click="toggleStatus"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 text-[13px] font-medium hover:bg-gray-50 transition-colors"
                    >
                        @if($commerceSupplier->status?->value === 'active')
                            @svg('heroicon-o-pause', 'w-4 h-4')
                            Pausieren
                        @elseif($commerceSupplier->status?->value === 'paused')
                            @svg('heroicon-o-play', 'w-4 h-4')
                            Aktivieren
                        @endif
                    </button>
                    <button
                        wire:click="openDeleteModal"
                        class="p-1.5 rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                        title="Lieferant löschen"
                    >
                        @svg('heroicon-o-trash', 'w-4 h-4')
                    </button>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="border-b border-gray-200">
                <nav class="flex gap-6">
                    @foreach(['overview' => 'Übersicht', 'mappings' => 'Feldmappings', 'articles' => 'Artikel', 'imports' => 'Imports'] as $tab => $label)
                        <button
                            wire:click="setTab('{{ $tab }}')"
                            class="pb-2.5 text-[13px] font-medium border-b-2 transition-colors {{ $activeTab === $tab ? 'border-[#166EE1] text-[#166EE1]' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </nav>
            </div>

            {{-- Tab: Overview --}}
            @if($activeTab === 'overview')
                <section class="bg-white rounded-lg border border-gray-200">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Lieferanten-Info</h3>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 text-[13px]">
                            <div>
                                <span class="text-[11px] text-gray-400">Quelle</span>
                                @php
                                    $typeColors = [
                                        'manual' => 'bg-slate-100 text-slate-700',
                                        'webhook_post' => 'bg-blue-100 text-blue-700',
                                        'pull_get' => 'bg-purple-100 text-purple-700',
                                    ];
                                @endphp
                                <div>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[11px] font-medium {{ $typeColors[$commerceSupplier->source_type?->value] ?? 'bg-slate-100 text-slate-700' }}">
                                        {{ $commerceSupplier->source_type?->label() }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <span class="text-[11px] text-gray-400">Status</span>
                                @php
                                    $statusColors = [
                                        'onboarding' => 'bg-yellow-100 text-yellow-700',
                                        'active' => 'bg-green-100 text-green-700',
                                        'paused' => 'bg-orange-100 text-orange-700',
                                        'archived' => 'bg-slate-100 text-slate-600',
                                    ];
                                @endphp
                                <div>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[11px] font-medium {{ $statusColors[$commerceSupplier->status?->value] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ $commerceSupplier->status?->label() }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <span class="text-[11px] text-gray-400">Natural Key</span>
                                <div class="font-medium text-gray-900 font-mono">{{ $commerceSupplier->natural_key }}</div>
                            </div>
                            <div>
                                <span class="text-[11px] text-gray-400">Artikel</span>
                                <div class="font-medium text-gray-900">{{ $commerceSupplier->articles_count }}</div>
                            </div>
                            <div>
                                <span class="text-[11px] text-gray-400">Letzter Import</span>
                                <div class="font-medium text-gray-900">{{ $commerceSupplier->last_import_at ? $commerceSupplier->last_import_at->format('d.m.Y H:i') : '-' }}</div>
                            </div>
                            <div>
                                <span class="text-[11px] text-gray-400">Erstellt</span>
                                <div class="font-medium text-gray-900">{{ $commerceSupplier->created_at->format('d.m.Y H:i') }}</div>
                            </div>
                        </div>

                        @if($commerceSupplier->isWebhook())
                            <div class="pt-4 mt-4 border-t border-gray-200">
                                <label class="block text-[11px] text-gray-400 mb-1">Webhook-URL</label>
                                <div x-data="{ copied: false }" class="relative">
                                    <div class="flex items-center gap-2 p-2 rounded-md bg-gray-900 border border-gray-700">
                                        <code class="flex-1 text-[13px] text-gray-100 break-all select-all font-mono">{{ $this->webhookUrl }}</code>
                                        <button
                                            @click="navigator.clipboard.writeText('{{ $this->webhookUrl }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                            class="shrink-0 p-1.5 rounded-md text-gray-400 hover:text-white transition-colors"
                                            title="URL kopieren"
                                        >
                                            <template x-if="!copied">
                                                @svg('heroicon-o-clipboard-document', 'w-4 h-4')
                                            </template>
                                            <template x-if="copied">
                                                @svg('heroicon-o-check', 'w-4 h-4 text-green-400')
                                            </template>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </section>
            @endif

            {{-- Tab: Field Mappings --}}
            @if($activeTab === 'mappings')
                <section class="bg-white rounded-lg border border-gray-200">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Feldmappings</h3>
                        <p class="text-[11px] text-gray-500">{{ count($fieldMappings) }} Mappings konfiguriert</p>
                    </div>
                    @if($fieldMappings->isEmpty())
                        <div class="p-6 text-center text-gray-500">
                            <p class="text-[13px]">Keine Feldmappings vorhanden.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-[13px]">
                                <thead>
                                    <tr class="border-b border-gray-200 bg-gray-50">
                                        <th class="text-left py-2 px-4 text-[11px] font-medium text-gray-400 uppercase tracking-wide">Source Key</th>
                                        <th class="text-center py-2 px-4 text-[11px] font-medium text-gray-400 uppercase tracking-wide"></th>
                                        <th class="text-left py-2 px-4 text-[11px] font-medium text-gray-400 uppercase tracking-wide">Zielfeld</th>
                                        <th class="text-left py-2 px-4 text-[11px] font-medium text-gray-400 uppercase tracking-wide">Typ</th>
                                        <th class="text-left py-2 px-4 text-[11px] font-medium text-gray-400 uppercase tracking-wide">Transform</th>
                                        <th class="text-center py-2 px-4 text-[11px] font-medium text-gray-400 uppercase tracking-wide">Aktiv</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($fieldMappings as $mapping)
                                        <tr class="border-b border-gray-100">
                                            <td class="py-2 px-4 font-mono text-gray-700">{{ $mapping->source_key }}</td>
                                            <td class="py-2 px-4 text-center text-gray-400">→</td>
                                            <td class="py-2 px-4 font-mono text-gray-900 font-medium">{{ $mapping->target_field ?? '-' }}</td>
                                            <td class="py-2 px-4">
                                                @php
                                                    $typeColors = [
                                                        'string' => 'text-blue-600 bg-blue-50',
                                                        'integer' => 'text-orange-600 bg-orange-50',
                                                        'decimal' => 'text-orange-600 bg-orange-50',
                                                        'boolean' => 'text-purple-600 bg-purple-50',
                                                        'date' => 'text-green-600 bg-green-50',
                                                        'datetime' => 'text-teal-600 bg-teal-50',
                                                    ];
                                                @endphp
                                                <span class="px-1.5 py-0.5 rounded text-[11px] font-medium {{ $typeColors[$mapping->data_type] ?? 'text-gray-600 bg-gray-50' }}">
                                                    {{ $mapping->data_type }}
                                                </span>
                                            </td>
                                            <td class="py-2 px-4 text-gray-500">{{ $mapping->transform ?? '-' }}</td>
                                            <td class="py-2 px-4 text-center">
                                                @if($mapping->is_active)
                                                    @svg('heroicon-o-check-circle', 'w-4 h-4 text-green-500 inline')
                                                @else
                                                    @svg('heroicon-o-x-circle', 'w-4 h-4 text-gray-300 inline')
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>
            @endif

            {{-- Tab: Articles --}}
            @if($activeTab === 'articles')
                <section class="bg-white rounded-lg border border-gray-200">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Verknüpfte Artikel</h3>
                        <p class="text-[11px] text-gray-500">{{ count($articles) }} Artikel</p>
                    </div>
                    @if($articles->isEmpty())
                        <div class="p-6 text-center text-gray-500">
                            <p class="text-[13px]">Noch keine Artikel importiert.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-[13px]">
                                <thead>
                                    <tr class="border-b border-gray-200 bg-gray-50">
                                        <th class="text-left py-2 px-4 text-[11px] font-medium text-gray-400 uppercase tracking-wide">Name</th>
                                        <th class="text-left py-2 px-4 text-[11px] font-medium text-gray-400 uppercase tracking-wide">SKU</th>
                                        <th class="text-left py-2 px-4 text-[11px] font-medium text-gray-400 uppercase tracking-wide">External ID</th>
                                        <th class="text-left py-2 px-4 text-[11px] font-medium text-gray-400 uppercase tracking-wide">Letzter Sync</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($articles as $article)
                                        <tr class="border-b border-gray-100 hover:bg-blue-50/30 cursor-pointer transition-colors" onclick="window.location='{{ route('commerce.articles.show', $article) }}'">
                                            <td class="py-2 px-4 font-medium text-gray-900">{{ $article->name }}</td>
                                            <td class="py-2 px-4 font-mono text-gray-600">{{ $article->sku ?? '-' }}</td>
                                            <td class="py-2 px-4 font-mono text-gray-500">{{ $article->pivot->external_id ?? '-' }}</td>
                                            <td class="py-2 px-4 text-gray-500">
                                                {{ $article->pivot->last_synced_at ? \Carbon\Carbon::parse($article->pivot->last_synced_at)->format('d.m.Y H:i') : '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>
            @endif

            {{-- Tab: Imports --}}
            @if($activeTab === 'imports')
                <section class="bg-white rounded-lg border border-gray-200">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Import-Log</h3>
                        <p class="text-[11px] text-gray-500">{{ count($imports) }} Imports</p>
                    </div>
                    @if($imports->isEmpty())
                        <div class="p-6 text-center text-gray-500">
                            <p class="text-[13px]">Noch keine Imports durchgeführt.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-[13px]">
                                <thead>
                                    <tr class="border-b border-gray-200 bg-gray-50">
                                        <th class="text-left py-2 px-4 text-[11px] font-medium text-gray-400 uppercase tracking-wide">Status</th>
                                        <th class="text-right py-2 px-4 text-[11px] font-medium text-gray-400 uppercase tracking-wide">Empfangen</th>
                                        <th class="text-right py-2 px-4 text-[11px] font-medium text-gray-400 uppercase tracking-wide">Erstellt</th>
                                        <th class="text-right py-2 px-4 text-[11px] font-medium text-gray-400 uppercase tracking-wide">Aktualisiert</th>
                                        <th class="text-right py-2 px-4 text-[11px] font-medium text-gray-400 uppercase tracking-wide">Übersprungen</th>
                                        <th class="text-right py-2 px-4 text-[11px] font-medium text-gray-400 uppercase tracking-wide">Dauer</th>
                                        <th class="text-left py-2 px-4 text-[11px] font-medium text-gray-400 uppercase tracking-wide">Zeitpunkt</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($imports as $import)
                                        @php
                                            $importStatusColors = [
                                                'success' => 'bg-green-100 text-green-700',
                                                'partial' => 'bg-yellow-100 text-yellow-700',
                                                'error' => 'bg-red-100 text-red-700',
                                                'pending' => 'bg-slate-100 text-slate-600',
                                                'processing' => 'bg-blue-100 text-blue-700',
                                            ];
                                        @endphp
                                        <tr class="border-b border-gray-100">
                                            <td class="py-2 px-4">
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[11px] font-medium {{ $importStatusColors[$import->status] ?? 'bg-slate-100 text-slate-600' }}">
                                                    {{ ucfirst($import->status) }}
                                                </span>
                                            </td>
                                            <td class="py-2 px-4 text-right text-gray-600">{{ $import->rows_received }}</td>
                                            <td class="py-2 px-4 text-right text-green-600">{{ $import->rows_created }}</td>
                                            <td class="py-2 px-4 text-right text-blue-600">{{ $import->rows_updated }}</td>
                                            <td class="py-2 px-4 text-right text-orange-600">{{ $import->rows_skipped }}</td>
                                            <td class="py-2 px-4 text-right text-gray-500">{{ $import->duration_ms ? $import->duration_ms . 'ms' : '-' }}</td>
                                            <td class="py-2 px-4 text-gray-500">{{ $import->created_at->format('d.m.Y H:i') }}</td>
                                        </tr>
                                        @if($import->error_log)
                                            <tr class="border-b border-gray-100 bg-red-50/30">
                                                <td colspan="7" class="py-2 px-4">
                                                    <div class="text-[11px] text-red-600 font-mono">
                                                        @foreach(array_slice($import->error_log, 0, 5) as $err)
                                                            <div>{{ $err }}</div>
                                                        @endforeach
                                                        @if(count($import->error_log) > 5)
                                                            <div class="text-gray-400">... und {{ count($import->error_log) - 5 }} weitere</div>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>
            @endif
        </div>
    </x-ui-page-container>

    {{-- Delete Modal --}}
    <x-ui-modal wire:model="showDeleteModal" title="Lieferant löschen">
        <div class="p-4">
            <div class="p-3 rounded-md bg-amber-50 border border-amber-200 text-[13px] text-amber-800">
                @svg('heroicon-o-exclamation-triangle', 'w-4 h-4 inline -mt-0.5 mr-1')
                <strong>{{ $commerceSupplier->name }}</strong> und alle zugehörigen Mappings und Imports werden unwiderruflich gelöscht.
                Verknüpfte Artikel bleiben erhalten.
            </div>
        </div>

        <div class="px-4 py-3 border-t border-gray-200 flex justify-end gap-3">
            <button wire:click="cancelDelete" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 text-[13px] font-medium hover:bg-gray-50 transition-colors">Abbrechen</button>
            <button wire:click="deleteSupplier" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-red-600 text-white text-[13px] font-medium hover:bg-red-700 transition-colors">
                @svg('heroicon-o-trash', 'w-4 h-4')
                Endgültig löschen
            </button>
        </div>
    </x-ui-modal>

    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Info" width="w-80" :defaultOpen="true" storeKey="sidebarOpen" side="left">
            <div class="p-6 space-y-6">
                <div>
                    <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-4">Navigation</h3>
                    <div class="space-y-1">
                        <a href="{{ route('commerce.suppliers.index') }}" wire:navigate
                           class="flex items-center gap-2 px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 text-[13px] font-medium hover:bg-gray-50 transition-colors w-full">
                            @svg('heroicon-o-arrow-left', 'w-4 h-4')
                            Alle Lieferanten
                        </a>
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>
</x-ui-page>
