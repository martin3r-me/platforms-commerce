{{--
    Products Index View
    Übersicht aller Produkte als Liste
--}}

<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Produkte" icon="heroicon-o-cube" />
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Commerce', 'href' => route('commerce.index'), 'icon' => 'shopping-bag'],
            ['label' => 'Produkte'],
        ]" />
    </x-slot>

    <x-ui-page-container>
        <div class="space-y-6">
            {{-- Info-Box --}}
            <div class="flex items-start gap-3 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3">
                @svg('heroicon-o-information-circle', 'w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0')
                <p class="text-[13px] text-blue-800">Produkte sind Zusammenstellungen von Artikeln. Organisiere sie auf Boards und versehe sie mit konfigurierbaren Slots.</p>
            </div>

            {{-- Create Product Form --}}
            <section class="bg-white rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Neues Produkt erstellen</h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <label class="block text-[11px] font-medium text-gray-500 mb-1">Name</label>
                            <input type="text"
                                   wire:model="name"
                                   placeholder="Produktname eingeben..."
                                   required
                                   class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                            @error('name') <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex items-end">
                            <button wire:click="createProduct"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-[#166EE1] text-white text-[13px] font-medium hover:bg-blue-700 transition-colors">
                                Produkt erstellen
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Produkte Liste --}}
            <section class="bg-white rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Produkte</h3>
                        <p class="text-[11px] text-gray-500">{{ count($products) }} Produkt(e)</p>
                    </div>
                </div>
                @if(count($products) > 0)
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50">
                                <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wide py-2 px-4">Name</th>
                                <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wide py-2 px-4">Artikel</th>
                                <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wide py-2 px-4">Board</th>
                                <th class="text-center text-[11px] font-medium text-gray-400 uppercase tracking-wide py-2 px-4">Slots</th>
                                <th class="text-right text-[11px] font-medium text-gray-400 uppercase tracking-wide py-2 px-4">Preisabw.</th>
                                <th class="text-right text-[11px] font-medium text-gray-400 uppercase tracking-wide py-2 px-4">Erstellt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr class="border-b border-gray-100 hover:bg-blue-50/50 transition-colors cursor-pointer"
                                    wire:key="product-{{ $product->id }}"
                                    x-on:click="window.Livewire.navigate('{{ route('commerce.products.show', $product) }}')">
                                    <td class="py-2.5 px-4">
                                        <div class="flex items-center gap-2">
                                            @if($product->color)
                                                <span class="w-3 h-3 rounded-full border border-gray-200 flex-shrink-0" style="background-color: {{ $product->color }}"></span>
                                            @endif
                                            <div>
                                                <div class="text-[13px] font-medium text-gray-900">{{ $product->name }}</div>
                                                @if($product->description)
                                                    <div class="text-[11px] text-gray-400 truncate max-w-xs">{{ Str::limit($product->description, 60) }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-2.5 px-4">
                                        @if($product->article)
                                            <a href="{{ route('commerce.articles.show', $product->article) }}"
                                               wire:navigate
                                               x-on:click.stop
                                               class="text-[13px] text-[#166EE1] hover:underline">{{ $product->article->name }}</a>
                                        @else
                                            <span class="text-[13px] text-gray-300">&mdash;</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-4">
                                        @if($product->slot && $product->slot->board)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-700">
                                                {{ $product->slot->board->name }}
                                            </span>
                                        @else
                                            <span class="text-[13px] text-gray-300">&mdash;</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-4 text-center text-[13px] text-gray-700">
                                        {{ $product->productSlots->count() }}
                                    </td>
                                    <td class="py-2.5 px-4 text-right text-[13px] text-gray-700">
                                        @if($product->price_deviation_value)
                                            {{ number_format($product->price_deviation_value, 2, ',', '.') }}{{ $product->price_deviation_type === 'relative' ? '%' : '€' }}
                                        @else
                                            <span class="text-gray-300">&mdash;</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-4 text-right text-[11px] text-gray-400">{{ $product->created_at->format('d.m.Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="p-12 text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-50 mb-4">
                            @svg('heroicon-o-cube', 'w-6 h-6 text-[#166EE1]')
                        </div>
                        <h3 class="text-[13px] font-medium text-gray-900 mb-1">Noch keine Produkte vorhanden</h3>
                        <p class="text-[13px] text-gray-500">Erstelle dein erstes Produkt, um loszulegen.</p>
                    </div>
                @endif
            </section>
        </div>
    </x-ui-page-container>

    {{-- Linke Sidebar --}}
    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Navigation" width="w-80" :defaultOpen="true" storeKey="sidebarOpen" side="left">
            <div class="p-6 space-y-6">
                <div>
                    <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-3">Navigation</h3>
                    <div class="space-y-1">
                        <a href="{{ route('commerce.index') }}" wire:navigate
                           class="flex items-center gap-2 px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 text-[13px] font-medium hover:bg-gray-50 transition-colors w-full">
                            @svg('heroicon-o-home', 'w-4 h-4')
                            Dashboard
                        </a>
                        <a href="{{ route('commerce.articles.index') }}" wire:navigate
                           class="flex items-center gap-2 px-3 py-1.5 rounded-md border border-gray-300 bg-white text-gray-700 text-[13px] font-medium hover:bg-gray-50 transition-colors w-full">
                            @svg('heroicon-o-rectangle-stack', 'w-4 h-4')
                            Artikel
                        </a>
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    {{-- Rechte Sidebar --}}
    <x-slot name="activity">
        <x-ui-page-sidebar title="Aktivitäten" width="w-80" :defaultOpen="false" storeKey="activityOpen" side="right">
            <div class="p-4 space-y-4">
                <div class="text-[13px] text-gray-500">Letzte Aktivitäten</div>
                <div class="space-y-2">
                    <div class="p-2 rounded-md border border-gray-200 bg-gray-50">
                        <div class="font-medium text-gray-900 text-[13px] truncate">Produkte-Übersicht geladen</div>
                        <div class="text-[11px] text-gray-500">Gerade eben</div>
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>
</x-ui-page>
