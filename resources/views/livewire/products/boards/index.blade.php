<div class="h-full overflow-y-auto p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Produkt-Boards</h1>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-lg border p-4">
            <h3 class="font-semibold mb-4">Boards</h3>
            <div class="space-y-2">
                @if($this->account)
                    @forelse($this->account->modulesCommerceProductBoards ?? [] as $board)
                        <a href="{{ route('commerce.products.boards.show', $board) }}" class="block p-3 bg-gray-50 rounded border hover:bg-gray-100" wire:navigate>
                            <div class="font-medium">{{ $board->name }}</div>
                        </a>
                    @empty
                        <div class="text-sm text-gray-500">Keine Boards vorhanden.</div>
                    @endforelse
                @endif
            </div>
        </div>
    </div>
</div>

