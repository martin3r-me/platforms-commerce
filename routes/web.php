<?php

use Platform\Commerce\Livewire\Index;
use Platform\Commerce\Livewire\Articles\Index as ArticlesIndex;
use Platform\Commerce\Livewire\Articles\Article;
use Platform\Commerce\Livewire\Products\Product;
use Platform\Commerce\Livewire\Products\Boards\Index as ProductsBoardsIndex;
use Platform\Commerce\Livewire\Products\Boards\Board;
use Platform\Commerce\Livewire\Attributes\Index as AttributesIndex;
use Platform\Commerce\Livewire\Attributes\AttributeSet;
use Platform\Commerce\Livewire\Settings\Index as SettingsIndex;

Route::get('/', Index::class)->name('commerce.index');
Route::get('/articles', ArticlesIndex::class)->name('commerce.articles.index');

// Model-Binding: Parameter == Modelname in camelCase
Route::get('/articles/{commerceArticle}', Article::class)
    ->name('commerce.articles.show');

Route::get('/products', ProductsBoardsIndex::class)->name('commerce.products.index');

// Model-Binding: Parameter == Modelname in camelCase
Route::get('/products/{commerceProduct}', Product::class)
    ->name('commerce.products.show');

Route::get('/products/boards/{commerceProductBoard}', Board::class)
    ->name('commerce.products.boards.show');

Route::get('/attributes', AttributesIndex::class)->name('commerce.attributes.index');

// Model-Binding: Parameter == Modelname in camelCase
Route::get('/attributes/{commerceAttributeSet}', AttributeSet::class)
    ->name('commerce.attributes.show');

Route::get('/settings', SettingsIndex::class)->name('commerce.settings.index');

