<div class="h-full overflow-y-auto p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ $article->name }}</h1>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-lg border p-4">
            <h3 class="font-semibold mb-4">Artikel-Details</h3>
            <form wire:submit.prevent="save">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Name</label>
                        <input type="text" wire:model="article.name" class="w-full border rounded-lg px-3 py-2">
                        @error('article.name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Beschreibung</label>
                        <textarea wire:model="article.description" class="w-full border rounded-lg px-3 py-2" rows="3"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Kategorie</label>
                        <select wire:model="article.category_id" class="w-full border rounded-lg px-3 py-2">
                            <option value="">Keine Kategorie</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

