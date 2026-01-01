<div class="p-3 bg-white rounded border mb-2">
    <div class="d-flex items-center justify-between">
        <div class="flex-1">
            <input type="text" wire:model="productBoardSlot.name" wire:blur="save" class="font-medium border-0 bg-transparent focus:bg-white focus:border focus:rounded px-2 py-1">
        </div>
        <div class="d-flex gap-2">
            <button wire:click="createProduct" class="px-2 py-1 bg-blue-500 text-white rounded text-sm">+ Produkt</button>
            <button wire:click="deleteSlot" class="px-2 py-1 bg-red-500 text-white rounded text-sm">Löschen</button>
        </div>
    </div>
</div>

