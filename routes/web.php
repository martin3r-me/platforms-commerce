<?php

/**
 * Commerce Web Routes
 * 
 * Diese Datei definiert alle authentifizierten Web-Routes für das Commerce-Modul.
 * 
 * WICHTIG FÜR LLMs:
 * - Routes werden automatisch mit dem Modul-Prefix versehen (aus Config)
 * - Middleware wird automatisch hinzugefügt (web, auth, etc.)
 * - Route-Namen sollten mit dem Modul-Prefix beginnen (commerce.*)
 * - Model-Binding: Parameter-Name muss dem Model-Namen in camelCase entsprechen
 * 
 * BEISPIEL:
 * Route::get('/articles/{commerceArticle}', Article::class)
 * 
 * Wird zu: /commerce/articles/{commerceArticle}
 * Model-Binding: {commerceArticle} wird automatisch zu CommerceArticle Model
 * 
 * @see Platform\Core\Routing\ModuleRouter für Details
 */

use Platform\Commerce\Livewire\Index;
use Platform\Commerce\Livewire\Articles\Index as ArticlesIndex;
use Platform\Commerce\Livewire\Articles\Article;
use Platform\Commerce\Livewire\Products\Index as ProductsIndex;
use Platform\Commerce\Livewire\Products\Product;
use Platform\Commerce\Livewire\Products\Boards\Index as BoardsIndex;
use Platform\Commerce\Livewire\Products\Boards\Board;
use Platform\Commerce\Livewire\Attributes\Index as AttributesIndex;
use Platform\Commerce\Livewire\Attributes\AttributeSet;
use Platform\Commerce\Livewire\Catalogs\Index as CatalogsIndex;
use Platform\Commerce\Livewire\Catalogs\Catalog;
use Platform\Commerce\Livewire\Settings\Index as SettingsIndex;

/**
 * Dashboard Route
 * 
 * Hauptübersicht des Commerce-Moduls
 */
Route::get('/', Index::class)->name('commerce.index');

/**
 * Artikel Routes
 * 
 * Verwaltung von Artikeln (CommerceArticle)
 */
Route::get('/articles', ArticlesIndex::class)->name('commerce.articles.index');

/**
 * Artikel Detail Route
 * 
 * Model-Binding: {commerceArticle} wird automatisch zu CommerceArticle Model
 * Der Parameter-Name muss dem Model-Namen in camelCase entsprechen.
 */
Route::get('/articles/{commerceArticle}', Article::class)
    ->name('commerce.articles.show');

/**
 * Produkt Routes
 *
 * Verwaltung von Produkten (CommerceProduct)
 */
Route::get('/products', ProductsIndex::class)->name('commerce.products.index');

/**
 * Board Routes (vor Produkt-Detail, damit /products/boards nicht als {commerceProduct} gematcht wird)
 *
 * Kanban-Ansicht für Produkte, organisiert in Boards mit Slots
 */
Route::get('/products/boards', BoardsIndex::class)->name('commerce.products.boards.index');
Route::get('/products/boards/{commerceProductBoard}', Board::class)
    ->name('commerce.products.boards.show');

/**
 * Produkt Detail Route
 *
 * Model-Binding: {commerceProduct} wird automatisch zu CommerceProduct Model
 */
Route::get('/products/{commerceProduct}', Product::class)
    ->name('commerce.products.show');

/**
 * Attribute Routes
 * 
 * Verwaltung von Attributsets (CommerceAttributeSet)
 */
Route::get('/attributes', AttributesIndex::class)->name('commerce.attributes.index');

/**
 * Attributset Detail Route
 * 
 * Model-Binding: {commerceAttributeSet} wird automatisch zu CommerceAttributeSet Model
 */
Route::get('/attributes/{commerceAttributeSet}', AttributeSet::class)
    ->name('commerce.attributes.show');

/**
 * Katalog Routes
 */
Route::get('/catalogs', CatalogsIndex::class)->name('commerce.catalogs.index');
Route::get('/catalogs/{commerceCatalog}', Catalog::class)->name('commerce.catalogs.show');

/**
 * Einstellungen Route
 * 
 * Commerce-Modul Einstellungen
 */
Route::get('/settings', SettingsIndex::class)->name('commerce.settings.index');

