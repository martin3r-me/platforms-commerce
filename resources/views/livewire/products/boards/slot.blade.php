<div class="p-4 bg-gray-50 rounded border">
    <div class="font-medium mb-2">{{ $productBoardSlot->name ?? 'Slot' }}</div>
    <div class="space-y-2">
        @forelse($productBoardSlot->products as $product)
            <livewire:commerce.products.product-preview :product="$product" :key="'product-'.$product->id" />
        @empty
            <div class="text-sm text-gray-500">Keine Produkte</div>
        @endforelse
    </div>
</div>

