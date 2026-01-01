<div class="p-2 border-b">
    <div class="d-flex items-center gap-4">
        <div class="flex-1">{{ $variant->variant_name ?? 'Variant #' . $variant->id }}</div>
        <div class="w-64">
            <select wire:model="variant.commerce_article_id" class="w-full border rounded px-2 py-1">
                <option value="">Kein Artikel</option>
                @foreach($articles as $article)
                    <option value="{{ $article->id }}">{{ $article->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

