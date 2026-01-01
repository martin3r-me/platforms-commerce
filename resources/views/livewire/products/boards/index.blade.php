<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Produkt-Boards" />
    </x-slot>

    <x-ui-page-container>
        <x-ui-panel title="Boards">
            <div class="space-y-2">
                @if($this->account)
                    @forelse($this->account->modulesCommerceProductBoards ?? [] as $board)
                        <a href="{{ route('commerce.products.boards.show', $board) }}" class="block p-3 bg-gray-50 rounded border hover:bg-gray-100" wire:navigate>
                            <div class="font-medium">{{ $board->name }}</div>
                        </a>
                    @empty
                        <div class="text-sm text-gray-500 p-4">Keine Boards vorhanden.</div>
                    @endforelse
                @else
                    <div class="text-sm text-gray-500 p-4">Bitte wählen Sie einen Account aus, um Boards anzuzeigen.</div>
                @endif
            </div>
        </x-ui-panel>
    </x-ui-page-container>
</x-ui-page>
