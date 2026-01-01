<div class="h-full overflow-y-auto p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ $productSlot->name }}</h1>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-lg border p-4">
            <h3 class="font-semibold mb-4">Slot-Details</h3>
            <form wire:submit.prevent="save">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Name</label>
                        <input type="text" wire:model="productSlot.name" class="w-full border rounded-lg px-3 py-2">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

