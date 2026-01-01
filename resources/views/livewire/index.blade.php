<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Commerce Dashboard" />
    </x-slot>

    <x-ui-page-container>
        <!-- Commerce Statistiken -->
        <div class="mb-6">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="grid grid-cols-4 gap-4">
                    <x-ui-dashboard-tile title="Artikel" :count="\Platform\Commerce\Models\CommerceArticle::where('team_id', auth()->user()->currentTeam->id)->count()" icon="rectangle-stack" variant="primary" size="lg" />
                    <x-ui-dashboard-tile title="Produkte" :count="\Platform\Commerce\Models\CommerceProduct::where('team_id', auth()->user()->currentTeam->id)->count()" icon="cube" variant="info" size="lg" />
                    <x-ui-dashboard-tile title="Attribute" :count="\Platform\Commerce\Models\CommerceAttributeSet::where('team_id', auth()->user()->currentTeam->id)->count()" icon="tag" variant="neutral" size="lg" />
                    <x-ui-dashboard-tile title="Kategorien" :count="\Platform\Commerce\Models\CommerceArticleCategory::where('team_id', auth()->user()->currentTeam->id)->count()" icon="squares-2x2" variant="success" size="lg" />
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Schnellzugriff</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('commerce.articles.index') }}" class="p-4 bg-white rounded border hover:bg-gray-50" wire:navigate>
                    <div class="font-medium">Artikel verwalten</div>
                    <div class="text-sm text-gray-500">Artikel anzeigen und bearbeiten</div>
                </a>
                <a href="{{ route('commerce.products.index') }}" class="p-4 bg-white rounded border hover:bg-gray-50" wire:navigate>
                    <div class="font-medium">Produkte verwalten</div>
                    <div class="text-sm text-gray-500">Produkte anzeigen und bearbeiten</div>
                </a>
                <a href="{{ route('commerce.attributes.index') }}" class="p-4 bg-white rounded border hover:bg-gray-50" wire:navigate>
                    <div class="font-medium">Attribute verwalten</div>
                    <div class="text-sm text-gray-500">Attributsets anzeigen und bearbeiten</div>
                </a>
            </div>
        </div>
    </x-ui-page-container>
</x-ui-page>
