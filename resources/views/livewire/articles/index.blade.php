{{--
    Articles Index View
    Übersicht aller Artikel
    
    WICHTIG FÜR LLMs:
    - Zeigt alle Artikel des Teams
    - Account-Filter ist optional (nur wenn Relations-Modul vorhanden)
    - Verwendet moderne UI-Komponenten
--}}

<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Artikel" icon="heroicon-o-rectangle-stack" />
    </x-slot>

    <x-ui-page-container>
        <div class="space-y-6">
            {{-- Header --}}
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-[var(--ui-secondary)]">Artikel</h1>
                    <p class="text-[var(--ui-muted)] mt-1">Verwalte deine Artikel</p>
                </div>
            </div>

            {{-- Artikel Liste --}}
            <x-ui-panel title="Artikel" :subtitle="count($articles) . ' Artikel'">
                <div class="space-y-3">
                    @forelse($articles as $article)
                        <a href="{{ route('commerce.articles.show', $article) }}" class="block p-4 rounded-md border border-[var(--ui-border)] bg-white hover:bg-[var(--ui-muted-5)] transition" wire:navigate>
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-[var(--ui-secondary)]">{{ $article->name }}</h3>
                                    @if($article->description)
                                        <p class="text-sm text-[var(--ui-muted)] mt-1">{{ Str::limit($article->description, 100) }}</p>
                                    @endif
                                    <div class="mt-2 flex items-center gap-4 text-xs text-[var(--ui-muted)]">
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
                        <div class="p-6 text-center text-[var(--ui-muted)] bg-white rounded-md border border-[var(--ui-border)]">
                            <p>Noch keine Artikel vorhanden.</p>
                        </div>
                    @endforelse
                </div>
            </x-ui-panel>
        </div>
    </x-ui-page-container>

    {{-- Linke Sidebar (Filter) --}}
    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Filter" width="w-80" :defaultOpen="true" storeKey="sidebarOpen" side="left">
            <div class="p-6 space-y-6">
                {{-- Account-Filter (optional, nur wenn Relations-Modul vorhanden) --}}
                @if(count($accounts) > 0)
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
                @endif

                {{-- Kategorien-Filter --}}
                @if(count($categories) > 0)
                    <div>
                        <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-4">Kategorien</h3>
                        <div class="space-y-2">
                            @foreach($categories as $category)
                                <div class="p-2 rounded border border-[var(--ui-border)]/60 bg-[var(--ui-muted-5)]">
                                    <div class="font-medium text-[var(--ui-secondary)] text-sm">{{ $category->name }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    {{-- Rechte Sidebar (Aktivitäten) --}}
    <x-slot name="activity">
        <x-ui-page-sidebar title="Aktivitäten" width="w-80" :defaultOpen="false" storeKey="activityOpen" side="right">
            <div class="p-4 space-y-4">
                <div class="text-sm text-[var(--ui-muted)]">Letzte Aktivitäten</div>
                <div class="space-y-3 text-sm">
                    <div class="p-2 rounded border border-[var(--ui-border)]/60 bg-[var(--ui-muted-5)]">
                        <div class="font-medium text-[var(--ui-secondary)] truncate">Artikel-Übersicht geladen</div>
                        <div class="text-[var(--ui-muted)]">Gerade eben</div>
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>
</x-ui-page>
