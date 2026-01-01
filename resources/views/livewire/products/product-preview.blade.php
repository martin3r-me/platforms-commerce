<div class="p-3 bg-white rounded border">
    <div class="font-medium">{{ $product->name }}</div>
    @if($product->description)
        <div class="text-sm text-gray-500">{{ Str::limit($product->description, 100) }}</div>
    @endif
</div>

