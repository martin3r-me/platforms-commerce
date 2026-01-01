<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Artikel" />
    </x-slot>

    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Filter" width="w-80" :defaultOpen="true" storeKey="sidebarOpen" side="left">
            <div class="p-6 space-y-6">
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-4">Filter</h3>
                    <div class="space-y-3">
                        <x-ui-input-select
                            name="account_id"
                            label="Account"
                            wire:model.live="account_id"
                            :options="$accounts"
                            optionValue="id"
                            optionLabel="name"
                            :nullable="true"
                            nullLabel="Alle Accounts"
                            size="sm"
                        />
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    <x-ui-page-container>
        @if($this->account)
            <x-ui-panel title="Artikel für {{ $this->account->name }}">
                <div class="space-y-2">
                    @forelse($this->account->modulesCommerceArticles ?? [] as $article)
                        <a href="{{ route('commerce.articles.show', $article) }}" class="block p-3 bg-gray-50 rounded border hover:bg-gray-100" wire:navigate>
                            <div class="font-medium">{{ $article->name }}</div>
                            @if($article->category)
                                <div class="text-xs text-gray-500">{{ $article->category->name }}</div>
                            @endif
                        </a>
                    @empty
                        <div class="text-sm text-gray-500 p-4">Keine Artikel vorhanden.</div>
                    @endforelse
                </div>
            </x-ui-panel>
        @else
            <x-ui-panel title="Artikel">
                <div class="text-sm text-gray-500 p-4">Bitte wählen Sie einen Account aus, um Artikel anzuzeigen.</div>
            </x-ui-panel>
        @endif
    </x-ui-page-container>
</x-ui-page>
