<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Einstellungen" />
    </x-slot>

    <x-ui-page-container spacing="space-y-8">
        <!-- Steuerkategorien -->
        <x-ui-panel title="Steuerkategorien">
            <form wire:submit.prevent="save" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <x-ui-input-text 
                        name="name"
                        label="Name"
                        wire:model="name"
                        required
                        :errorKey="'name'"
                    />
                    <x-ui-input-number 
                        name="default_rate"
                        label="Standard-Steuersatz (%)"
                        wire:model="default_rate"
                        min="0"
                        max="100"
                        required
                        :errorKey="'default_rate'"
                    />
                </div>
                <x-ui-button type="submit" variant="primary">
                    Steuerkategorie speichern
                </x-ui-button>
            </form>
        </x-ui-panel>

        <!-- Steuerregeln Matrix -->
        <x-ui-panel title="Steuerregeln Matrix">
            <div class="space-y-2">
                @forelse($matrix as $rule)
                    <livewire:commerce.settings.tax-rule-row :rule="$rule" :key="'rule-'.$rule->id" />
                @empty
                    <div class="text-sm text-gray-500 p-4">Keine Steuerregeln vorhanden.</div>
                @endforelse
            </div>
        </x-ui-panel>
    </x-ui-page-container>
</x-ui-page>
