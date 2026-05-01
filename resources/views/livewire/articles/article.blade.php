<x-ui-page>

    <x-slot name="navbar">
        <x-ui-page-navbar title="{{ $article->name }}" />
    </x-slot>

    <x-slot name="actionbar">
        <x-ui-page-actionbar :breadcrumbs="[
            ['label' => 'Commerce', 'href' => route('commerce.index'), 'icon' => 'shopping-bag'],
            ['label' => 'Artikel', 'href' => route('commerce.articles.index')],
            ['label' => $article->name],
        ]" />
    </x-slot>

    <x-ui-page-container spacing="space-y-8">
        <section id="general">
            <x-ui-panel title="Allgemeine Informationen">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <x-ui-input-text
                            name="article.name"
                            label="Name *"
                            wire:model.live="article.name"
                            required
                            :errorKey="'article.name'"
                        />
                        <x-ui-input-textarea
                            name="article.short_description"
                            label="Kurzbeschreibung"
                            wire:model.live="article.short_description"
                            rows="2"
                            :errorKey="'article.short_description'"
                        />
                    </div>
                </div>
            </x-ui-panel>
        </section>
    </x-ui-page-container>
</x-ui-page>
