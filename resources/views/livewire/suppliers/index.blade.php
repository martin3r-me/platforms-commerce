<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Lieferanten" icon="heroicon-o-truck" />
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Commerce', 'href' => route('commerce.index'), 'icon' => 'shopping-bag'],
            ['label' => 'Lieferanten'],
        ]" />
    </x-slot>

    <x-ui-page-container>
        <div class="space-y-6">
            {{-- Create Supplier --}}
            <section class="bg-white rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Neuer Lieferant</h3>
                </div>
                <div class="p-4">
                    <form wire:submit="createSupplier" class="flex items-end gap-4">
                        <div class="flex-1">
                            <label class="block text-[11px] font-medium text-gray-500 mb-1">Name</label>
                            <input type="text"
                                   wire:model="name"
                                   placeholder="z.B. NECTA GmbH"
                                   required
                                   class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                            @error('name') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex-1">
                            <label class="block text-[11px] font-medium text-gray-500 mb-1">Beschreibung</label>
                            <input type="text"
                                   wire:model="description"
                                   placeholder="Optional"
                                   class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                        </div>
                        <div class="w-48">
                            <label class="block text-[11px] font-medium text-gray-500 mb-1">Datenquelle</label>
                            <select wire:model="sourceType"
                                    class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                                <option value="webhook_post">Webhook (POST)</option>
                                <option value="manual">Manuell</option>
                                <option value="pull_get">Pull (GET)</option>
                            </select>
                        </div>
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-[#166EE1] text-white text-[13px] font-medium hover:bg-blue-700 transition-colors whitespace-nowrap">
                            Lieferant erstellen
                        </button>
                    </form>
                </div>
            </section>

            {{-- Supplier List --}}
            <section class="bg-white rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Lieferanten</h3>
                    <p class="text-[11px] text-gray-500">{{ count($suppliers) }} {{ count($suppliers) === 1 ? 'Lieferant' : 'Lieferanten' }}</p>
                </div>

                @if($suppliers->isEmpty())
                    <div class="p-12 text-center">
                        @svg('heroicon-o-truck', 'w-12 h-12 text-gray-300 mx-auto mb-4')
                        <h3 class="text-sm font-semibold text-gray-900 mb-1">Noch keine Lieferanten</h3>
                        <p class="text-[13px] text-gray-500 max-w-md mx-auto">
                            Lieferanten sind Datenquellen für Artikel. Erstelle einen Lieferanten mit Webhook-Anbindung,
                            um automatisch Artikel aus externen Systemen zu importieren.
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-[13px]">
                            <thead>
                                <tr class="border-b border-gray-200 bg-gray-50">
                                    <th class="text-left py-2 px-4 text-[11px] font-medium text-gray-400 uppercase tracking-wide">Name</th>
                                    <th class="text-left py-2 px-4 text-[11px] font-medium text-gray-400 uppercase tracking-wide">Typ</th>
                                    <th class="text-left py-2 px-4 text-[11px] font-medium text-gray-400 uppercase tracking-wide">Status</th>
                                    <th class="text-right py-2 px-4 text-[11px] font-medium text-gray-400 uppercase tracking-wide">Artikel</th>
                                    <th class="text-left py-2 px-4 text-[11px] font-medium text-gray-400 uppercase tracking-wide">Letzter Import</th>
                                    <th class="text-left py-2 px-4 text-[11px] font-medium text-gray-400 uppercase tracking-wide">Erstellt</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($suppliers as $supplier)
                                    @php
                                        $typeColors = [
                                            'manual' => 'bg-slate-100 text-slate-700',
                                            'webhook_post' => 'bg-blue-100 text-blue-700',
                                            'pull_get' => 'bg-purple-100 text-purple-700',
                                        ];
                                        $statusColors = [
                                            'onboarding' => 'bg-yellow-100 text-yellow-700',
                                            'active' => 'bg-green-100 text-green-700',
                                            'paused' => 'bg-orange-100 text-orange-700',
                                            'archived' => 'bg-slate-100 text-slate-600',
                                        ];
                                        $href = $supplier->isOnboarding() && !$supplier->isManual()
                                            ? route('commerce.suppliers.onboarding', $supplier)
                                            : route('commerce.suppliers.show', $supplier);
                                    @endphp
                                    <tr class="border-b border-gray-100 hover:bg-blue-50/30 cursor-pointer transition-colors" onclick="window.location='{{ $href }}'">
                                        <td class="py-2.5 px-4">
                                            <span class="font-medium text-gray-900">{{ $supplier->name }}</span>
                                            @if($supplier->description)
                                                <span class="text-gray-400 ml-1">{{ Str::limit($supplier->description, 40) }}</span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 px-4">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[11px] font-medium {{ $typeColors[$supplier->source_type?->value] ?? 'bg-slate-100 text-slate-700' }}">
                                                {{ $supplier->source_type?->label() ?? 'Unbekannt' }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-4">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[11px] font-medium {{ $statusColors[$supplier->status?->value] ?? 'bg-slate-100 text-slate-600' }}">
                                                {{ $supplier->status?->label() ?? 'Unbekannt' }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-4 text-right text-gray-600">{{ $supplier->articles_count }}</td>
                                        <td class="py-2.5 px-4 text-gray-500">
                                            {{ $supplier->last_import_at ? $supplier->last_import_at->format('d.m.Y H:i') : '-' }}
                                        </td>
                                        <td class="py-2.5 px-4 text-gray-500">{{ $supplier->created_at->format('d.m.Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>
    </x-ui-page-container>

    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Info" width="w-80" :defaultOpen="true" storeKey="sidebarOpen" side="left">
            <div class="p-6 space-y-6">
                <div>
                    <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-4">Navigation</h3>
                    <div class="space-y-1">
                        <a href="{{ route('commerce.index') }}" wire:navigate
                           class="flex items-center gap-2 px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 text-[13px] font-medium hover:bg-gray-50 transition-colors w-full">
                            @svg('heroicon-o-arrow-left', 'w-4 h-4')
                            Zum Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>
</x-ui-page>
