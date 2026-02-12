{{--
    Matrix Row View
    Zeile in der Varianten-Matrix für Artikelzuweisung
--}}

<div class="bg-white rounded-lg shadow-sm ring-1 ring-[var(--ui-border)] p-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Varianteninformationen --}}
        <div>
            <div class="flex items-center gap-2 mb-2">
                @svg('heroicon-s-tag', 'h-4 w-4 text-[var(--ui-muted)]')
                <span class="text-sm font-medium text-[var(--ui-secondary)]">Variante #{{ $variant->id }}</span>
            </div>
        </div>

        {{-- Dimensionen und Werte --}}
        <div>
            <div class="flex items-center gap-2 mb-2">
                @svg('heroicon-s-squares-2x2', 'h-4 w-4 text-[var(--ui-muted)]')
                <span class="text-sm font-medium text-[var(--ui-secondary)]">Dimensionen</span>
            </div>
            <div class="space-y-1.5">
                @foreach ($variant->dimensionValues as $dimensionValue)
                    @if ($dimensionValue->dimensionValue && $dimensionValue->dimensionValue->dimension)
                        <div wire:key="{{ $variant->id }}-dimension-value-{{ $dimensionValue->id }}"
                             class="flex items-center text-sm">
                            <span class="text-[var(--ui-muted)]">{{ $dimensionValue->dimensionValue->dimension->name }}:</span>
                            <span class="ml-2 font-medium text-[var(--ui-secondary)]">{{ $dimensionValue->dimensionValue->value }}</span>
                        </div>
                    @else
                        <div class="text-sm text-red-500 bg-red-50 px-3 py-1 rounded">
                            Ungültige Dimension
                        </div>
                    @endif
                @endforeach

                @if($variant->dimensionValues->isEmpty())
                    <div class="text-sm text-[var(--ui-muted)]">Keine Dimensionen</div>
                @endif
            </div>
        </div>

        {{-- Artikelzuweisung --}}
        <div>
            <div class="flex items-center gap-2 mb-2">
                @svg('heroicon-s-link', 'h-4 w-4 text-[var(--ui-muted)]')
                <label for="article-{{ $variant->id }}" class="text-sm font-medium text-[var(--ui-secondary)]">Artikel zuweisen</label>
            </div>
            <select
                wire:model.live="variant.commerce_article_id"
                id="article-{{ $variant->id }}"
                class="w-full bg-[var(--ui-muted-5)] border-0 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[var(--ui-primary)]"
            >
                <option value="">Bitte Artikel auswählen</option>
                @if($articles)
                    @foreach($articles as $article)
                        <option wire:key="select-article-{{ $article->id }}-for-variant-{{ $variant->id }}"
                                value="{{ $article->id }}">
                            {{ $article->name }}@if($article->articleType) ({{ $article->articleType->name }})@endif
                        </option>
                    @endforeach
                @endif
            </select>
            @if($variant->commerce_article_id && $articles)
                @php
                    $selectedArticle = $articles->find($variant->commerce_article_id);
                @endphp
                @if($selectedArticle && $selectedArticle->articleType)
                    <div class="mt-2">
                        <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-xs font-medium"
                              style="background-color: {{ $selectedArticle->articleType->color ?? '#e5e7eb' }}20; color: {{ $selectedArticle->articleType->color ?? '#6b7280' }}">
                            @if($selectedArticle->articleType->color)
                                <span class="w-2 h-2 rounded-full" style="background-color: {{ $selectedArticle->articleType->color }}"></span>
                            @endif
                            {{ $selectedArticle->articleType->name }}
                        </span>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
