<div class="h-full overflow-y-auto p-6">
    <div class="mb-6">
        <div class="d-flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Artikel</h1>
                <p class="text-gray-600">Verwalten Sie Ihr Produktsortiment</p>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <div class="d-flex gap-4">
            <select wire:model.live="account_id" class="w-64 bg-white border rounded-lg px-3 py-2">
                <option value="">Account wählen...</option>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                @endforeach
            </select>
        </div>

        @if($this->account)
            <div class="bg-white rounded-lg border">
                <div class="p-4">
                    <h3 class="font-semibold mb-4">Artikel für {{ $this->account->name }}</h3>
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
                </div>
            </div>
        @endif
    </div>
</div>

