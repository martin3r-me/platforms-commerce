{{--
    Articles Index View
    Übersicht aller Artikel
--}}

<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Artikel" icon="heroicon-o-rectangle-stack" />
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Commerce', 'href' => route('commerce.index'), 'icon' => 'shopping-bag'],
            ['label' => 'Artikel'],
        ]" />
    </x-slot>

    <x-ui-page-container>
        <div class="space-y-6">
            {{-- Artikel Liste --}}
            <section class="bg-white rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Artikel</h3>
                        <p class="text-[11px] text-gray-500">{{ count($articles) }} Artikel</p>
                    </div>
                </div>
                @if(count($articles) > 0)
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50">
                                <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wide py-2 px-4">Name</th>
                                <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wide py-2 px-4">Kategorie</th>
                                <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wide py-2 px-4">SKU</th>
                                <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wide py-2 px-4">Typ</th>
                                <th class="text-right text-[11px] font-medium text-gray-400 uppercase tracking-wide py-2 px-4">Preis</th>
                                <th class="text-left text-[11px] font-medium text-gray-400 uppercase tracking-wide py-2 px-4">Einheit</th>
                                <th class="text-right text-[11px] font-medium text-gray-400 uppercase tracking-wide py-2 px-4">EK intern</th>
                                <th class="text-right text-[11px] font-medium text-gray-400 uppercase tracking-wide py-2 px-4">Marge</th>
                                <th class="text-right text-[11px] font-medium text-gray-400 uppercase tracking-wide py-2 px-4">Erstellt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($articles as $article)
                                <tr class="border-b border-gray-100 hover:bg-blue-50/50 transition-colors cursor-pointer"
                                    wire:key="article-{{ $article->id }}"
                                    x-on:click="window.Livewire.navigate('{{ route('commerce.articles.show', $article) }}')">
                                    <td class="py-2.5 px-4">
                                        <div class="text-[13px] font-medium text-gray-900">{{ $article->name }}</div>
                                        @if($article->description)
                                            <div class="text-[11px] text-gray-400 truncate max-w-xs">{{ Str::limit($article->description, 60) }}</div>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-4">
                                        @if($article->category)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-700">{{ $article->category->name }}</span>
                                        @else
                                            <span class="text-[13px] text-gray-300">&mdash;</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-4 text-[13px] text-gray-500 font-mono">{{ $article->sku ?? '—' }}</td>
                                    <td class="py-2.5 px-4">
                                        @if($article->articleType)
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[11px] font-medium"
                                                  style="background-color: {{ $article->articleType->color ?? '#e5e7eb' }}20; color: {{ $article->articleType->color ?? '#6b7280' }}">
                                                @if($article->articleType->color)
                                                    <span class="w-2 h-2 rounded-full" style="background-color: {{ $article->articleType->color }}"></span>
                                                @endif
                                                {{ $article->articleType->name }}
                                            </span>
                                        @else
                                            <span class="text-[13px] text-gray-300">&mdash;</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-4 text-right text-[13px] text-gray-900 font-medium">
                                        @if($article->price !== null)
                                            {{ number_format((float) $article->price, 2, ',', '.') }}&nbsp;€
                                        @else
                                            <span class="text-gray-300">&mdash;</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-4 text-[13px] text-gray-700">
                                        @if($article->base_price_unit)
                                            <code class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-700">{{ $article->base_price_unit }}</code>
                                            @if($article->base_price_quantity && (float) $article->base_price_quantity !== 1.0)
                                                <span class="text-[11px] text-gray-400 ml-1">×&nbsp;{{ rtrim(rtrim(number_format((float) $article->base_price_quantity, 2, ',', '.'), '0'), ',') }}</span>
                                            @endif
                                        @else
                                            <span class="text-gray-300">&mdash;</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-4 text-right text-[13px] text-gray-700">
                                        @if($article->internal_cost !== null)
                                            {{ number_format($article->internal_cost, 2, ',', '.') }}&nbsp;€
                                            @if($article->costStandard)
                                                <div class="text-[10px] text-gray-400">{{ $article->costStandard->name }} · {{ rtrim(rtrim(number_format((float) ($article->cost_quantity ?? 1), 2, ',', '.'), '0'), ',') }}{{ $article->cost_unit ?? 'h' }}</div>
                                            @endif
                                        @else
                                            <span class="text-gray-300">&mdash;</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-4 text-right text-[13px]">
                                        @if($article->internal_margin !== null)
                                            @php
                                                $marginColor = $article->internal_margin >= 0 ? 'text-green-700' : 'text-red-600';
                                                $marginPct = ($article->price && (float) $article->price > 0)
                                                    ? round(($article->internal_margin / (float) $article->price) * 100)
                                                    : null;
                                            @endphp
                                            <span class="font-medium {{ $marginColor }}">{{ number_format($article->internal_margin, 2, ',', '.') }}&nbsp;€</span>
                                            @if($marginPct !== null)
                                                <div class="text-[10px] text-gray-400">{{ $marginPct }}&nbsp;%</div>
                                            @endif
                                        @else
                                            <span class="text-gray-300">&mdash;</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-4 text-right text-[11px] text-gray-400">{{ $article->created_at->format('d.m.Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="p-8 text-center">
                        <p class="text-[13px] text-gray-400">Noch keine Artikel vorhanden.</p>
                    </div>
                @endif
            </section>
        </div>
    </x-ui-page-container>

    {{-- Linke Sidebar (Filter) --}}
    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Filter" width="w-80" :defaultOpen="true" storeKey="sidebarOpen" side="left">
            <div class="p-6 space-y-6">
                {{-- Account-Filter (optional) --}}
                @if(count($accounts) > 0)
                    <div>
                        <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-4">Filter</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Account</label>
                                <select wire:model.live="account_id"
                                        class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
                                    <option value="">Alle Accounts</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Kategorien-Filter --}}
                @if(count($categories) > 0)
                    <div>
                        <h3 class="text-[11px] font-medium text-gray-500 uppercase tracking-wide mb-4">Kategorien</h3>
                        <div class="space-y-2">
                            @foreach($categories as $category)
                                <div class="p-2 rounded-md border border-gray-200 bg-gray-50">
                                    <div class="font-medium text-gray-900 text-[13px]">{{ $category->name }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
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
                        <div class="font-medium text-gray-900 text-[13px] truncate">Artikel-Übersicht geladen</div>
                        <div class="text-[11px] text-gray-500">Gerade eben</div>
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>
</x-ui-page>
