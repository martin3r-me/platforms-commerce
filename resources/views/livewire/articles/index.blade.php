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
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Artikel</h3>
                    <p class="text-[11px] text-gray-500">{{ count($articles) }} Artikel</p>
                </div>
                <div class="p-4 space-y-3">
                    @forelse($articles as $article)
                        <a href="{{ route('commerce.articles.show', $article) }}" class="block p-4 rounded-md border border-gray-200 bg-white hover:bg-blue-50/50 transition-colors" wire:navigate>
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="text-[13px] font-semibold text-gray-900">{{ $article->name }}</h3>
                                    @if($article->description)
                                        <p class="text-[13px] text-gray-500 mt-1">{{ Str::limit($article->description, 100) }}</p>
                                    @endif
                                    <div class="mt-2 flex items-center gap-4 text-[11px] text-gray-400">
                                        @if($article->category)
                                            <span>{{ $article->category->name }}</span>
                                        @endif
                                        @if($article->sku)
                                            <span>SKU: {{ $article->sku }}</span>
                                        @endif
                                        <span>{{ $article->created_at->format('d.m.Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="p-6 text-center text-gray-500 bg-white rounded-md border border-gray-200">
                            <p class="text-[13px]">Noch keine Artikel vorhanden.</p>
                        </div>
                    @endforelse
                </div>
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
