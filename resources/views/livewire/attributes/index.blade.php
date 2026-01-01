<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Attribute" />
    </x-slot>

    <x-ui-page-container>
        <x-ui-panel title="Attributsets">
            <div class="space-y-2">
                @forelse($this->attributeSets as $set)
                    <a href="{{ route('commerce.attributes.show', $set) }}" class="block p-3 bg-gray-50 rounded border hover:bg-gray-100" wire:navigate>
                        <div class="font-medium">{{ $set->name }}</div>
                    </a>
                @empty
                    <div class="text-sm text-gray-500">Keine Attributsets vorhanden.</div>
                @endforelse
            </div>
        </x-ui-panel>
    </x-ui-page-container>
</x-ui-page>
