<div class="h-full overflow-y-auto p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ $board->name }}</h1>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-lg border p-4">
            <h3 class="font-semibold mb-4">Board-Slots</h3>
            <div class="space-y-2">
                @forelse($board->productBoardSlots as $slot)
                    <div class="p-3 bg-gray-50 rounded border">
                        <div class="font-medium">{{ $slot->name ?? 'Slot #' . $slot->id }}</div>
                        <div class="text-xs text-gray-500">{{ $slot->products->count() }} Produkte</div>
                    </div>
                @empty
                    <div class="text-sm text-gray-500">Keine Slots vorhanden.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

