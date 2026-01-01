<div class="h-full overflow-y-auto p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ $attributeSet->name }}</h1>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-lg border p-4">
            <h3 class="font-semibold mb-4">Attribut-Set Details</h3>
            <form wire:submit.prevent="save">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Name</label>
                        <input type="text" wire:model="attributeSet.name" class="w-full border rounded-lg px-3 py-2">
                    </div>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-lg border p-4">
            <h3 class="font-semibold mb-4">Items</h3>
            <div class="space-y-2">
                @forelse($items as $item)
                    <div class="p-3 bg-gray-50 rounded border">
                        <div class="font-medium">{{ $item->name }}</div>
                    </div>
                @empty
                    <div class="text-sm text-gray-500">Keine Items vorhanden.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

