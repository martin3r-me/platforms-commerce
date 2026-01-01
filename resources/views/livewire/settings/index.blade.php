<div class="h-full overflow-y-auto p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Einstellungen</h1>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-lg border p-4">
            <h3 class="font-semibold mb-4">Steuerkategorien</h3>
            <form wire:submit.prevent="save">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Name</label>
                        <input type="text" wire:model="name" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Standard-Steuersatz (%)</label>
                        <input type="number" wire:model="default_rate" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg">Speichern</button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-lg border p-4">
            <h3 class="font-semibold mb-4">Steuerregeln Matrix</h3>
            <div class="space-y-2">
                @forelse($matrix as $rule)
                    <livewire:commerce.settings.tax-rule-row :rule="$rule" :key="'rule-'.$rule->id" />
                @empty
                    <div class="text-sm text-gray-500">Keine Steuerregeln vorhanden.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

