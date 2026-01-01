<div class="h-full overflow-y-auto p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Attribute</h1>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-lg border p-4">
            <h3 class="font-semibold mb-4">Attributsets</h3>
            <div class="space-y-2">
                @forelse($this->attributeSets as $set)
                    <a href="{{ route('commerce.attributes.show', $set) }}" class="block p-3 bg-gray-50 rounded border hover:bg-gray-100" wire:navigate>
                        <div class="font-medium">{{ $set->name }}</div>
                    </a>
                @empty
                    <div class="text-sm text-gray-500">Keine Attributsets vorhanden.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

