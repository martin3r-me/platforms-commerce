{{--
    Matrix Row View
    Zeile in der Varianten-Matrix für Artikelzuweisung
--}}

<div class="bg-white rounded-lg border border-gray-200 p-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Varianteninformationen --}}
        <div>
            <div class="flex items-center gap-2 mb-2">
                @svg('heroicon-s-tag', 'h-4 w-4 text-gray-400')
                <span class="text-[13px] font-medium text-gray-900">Variante #{{ $variant->id }}</span>
            </div>
        </div>

        {{-- Dimensionen und Werte --}}
        <div>
            <div class="flex items-center gap-2 mb-2">
                @svg('heroicon-s-squares-2x2', 'h-4 w-4 text-gray-400')
                <span class="text-[13px] font-medium text-gray-900">Dimensionen</span>
            </div>
            <div class="space-y-1.5">
                @foreach ($variant->dimensionValues as $dimensionValue)
                    @if ($dimensionValue->dimensionValue && $dimensionValue->dimensionValue->dimension)
                        <div wire:key="{{ $variant->id }}-dimension-value-{{ $dimensionValue->id }}"
                             class="flex items-center text-[13px]">
                            <span class="text-gray-500">{{ $dimensionValue->dimensionValue->dimension->name }}:</span>
                            <span class="ml-2 font-medium text-gray-900">{{ $dimensionValue->dimensionValue->value }}</span>
                        </div>
                    @else
                        <div class="text-[13px] text-red-500 bg-red-50 px-3 py-1 rounded">
                            Ungültige Dimension
                        </div>
                    @endif
                @endforeach

                @if($variant->dimensionValues->isEmpty())
                    <div class="text-[13px] text-gray-400">Keine Dimensionen</div>
                @endif
            </div>
        </div>

        {{-- Artikelzuweisung --}}
        <div>
            <div class="flex items-center gap-2 mb-2">
                @svg('heroicon-s-link', 'h-4 w-4 text-gray-400')
                <label for="article-{{ $variant->id }}" class="text-[11px] font-medium text-gray-500">Artikel zuweisen</label>
            </div>
            <select
                wire:model.live="variant.commerce_article_id"
                id="article-{{ $variant->id }}"
                class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]"
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
                        <span class="inline-flex items-center gap-1.5 px-1.5 py-0.5 rounded-full text-[11px] font-medium"
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
