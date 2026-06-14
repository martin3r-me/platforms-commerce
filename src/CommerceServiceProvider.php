<?php

/**
 * Commerce Service Provider
 * 
 * Dieser Service Provider folgt dem Template-Pattern für Platform-Module.
 * 
 * WICHTIG FÜR LLMs:
 * - Config wird in register() geladen (Laravel Best Practice)
 * - Modul-Registrierung erfolgt in boot()
 * - Routes werden nur geladen, wenn Modul registriert ist
 * - Livewire-Komponenten werden automatisch registriert
 * 
 * @see Platform\Core\PlatformCore für Modul-Registrierung
 * @see Platform\Core\Routing\ModuleRouter für Route-Registrierung
 */

namespace Platform\Commerce;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Platform\Core\PlatformCore;
use Platform\Core\Routing\ModuleRouter;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class CommerceServiceProvider extends ServiceProvider
{
    /**
     * Register Services
     * 
     * Wird VOR boot() aufgerufen.
     * Hier sollten nur leichte Registrierungen erfolgen.
     * 
     * LARAVEL BEST PRACTICE:
     * - Config sollte hier geladen werden (mergeConfigFrom)
     * - Commands können hier registriert werden
     */
    public function register(): void
    {
        /**
         * Config laden
         *
         * mergeConfigFrom lädt die Config aus dem Package-Verzeichnis
         * und merged sie mit der Config aus config/ (falls vorhanden).
         *
         * WICHTIG: Muss in register() sein, nicht in boot()!
         */
        $this->mergeConfigFrom(__DIR__.'/../config/commerce.php', 'commerce');

        // Catalog-Contracts ueberschreiben (Core-Null → Commerce-Implementierung)
        $this->app->singleton(
            \Platform\Core\Contracts\CatalogArticleSearchProviderInterface::class,
            \Platform\Commerce\Services\CoreCatalogArticleSearchProvider::class
        );
        $this->app->singleton(
            \Platform\Core\Contracts\CatalogArticleResolverInterface::class,
            \Platform\Commerce\Services\CoreCatalogArticleResolver::class
        );
        $this->app->singleton(
            \Platform\Core\Contracts\CatalogArticleProcurementMapProviderInterface::class,
            \Platform\Commerce\Services\CoreCatalogArticleProcurementMapProvider::class
        );
        $this->app->singleton(
            \Platform\Core\Contracts\CatalogListProviderInterface::class,
            \Platform\Commerce\Services\CoreCatalogListProvider::class
        );
        $this->app->singleton(
            \Platform\Core\Contracts\CatalogArticleCategoryListProviderInterface::class,
            \Platform\Commerce\Services\CoreCatalogArticleCategoryListProvider::class
        );
    }

    /**
     * Boot Services
     * 
     * Wird NACH register() aufgerufen.
     * Hier erfolgt die eigentliche Modul-Registrierung.
     * 
     * REIHENFOLGE IST WICHTIG:
     * 1. Config prüfen (bereits in register() geladen)
     * 2. Modul bei PlatformCore registrieren
     * 3. Routes laden (nur wenn Modul registriert)
     * 4. Migrationen, Views, Livewire registrieren
     */
    public function boot(): void
    {
        Relation::morphMap([
            'commerce_product' => \Platform\Commerce\Models\CommerceProduct::class,
            'commerce_article' => \Platform\Commerce\Models\CommerceArticle::class,
            'commerce_sale'    => \Platform\Commerce\Models\CommerceSale::class,
            'commerce_catalog' => \Platform\Commerce\Models\CommerceCatalog::class,
        ]);

        /**
         * SCHRITT 1: Modul-Registrierung prüfen
         * 
         * Prüft ob:
         * - Config vorhanden ist
         * - modules-Tabelle existiert (für Datenbank-Registrierung)
         * 
         * Nur wenn beide Bedingungen erfüllt, wird das Modul registriert.
         */
        if (
            config()->has('commerce.routing') &&
            config()->has('commerce.navigation') &&
            Schema::hasTable('modules')
        ) {
            /**
             * Modul bei PlatformCore registrieren
             * 
             * Dies registriert das Modul in:
             * - Der Modul-Registry (für Navigation, Sidebar)
             * - Der Datenbank (modules-Tabelle)
             * 
             * Die Config wird automatisch aus config/commerce.php geladen.
             */
            PlatformCore::registerModule([
                'key'        => 'commerce', // Eindeutiger Schlüssel
                'title'      => 'Commerce', // Anzeige-Name
                'group'      => 'sales',
                'routing'    => config('commerce.routing'),
                'guard'      => config('commerce.guard'),
                'navigation' => config('commerce.navigation'),
                'sidebar'    => config('commerce.sidebar'),
                'billables'  => config('commerce.billables', []), // Optional: Billing-Konfiguration
            ]);
        }

        /**
         * SCHRITT 2: Routes laden
         * 
         * Routes werden nur geladen, wenn das Modul erfolgreich registriert wurde.
         * 
         * ModuleRouter::group() erstellt automatisch:
         * - Route-Prefix (aus Config)
         * - Middleware (web, auth, etc.)
         * - Domain-Handling (für Subdomain-Modus)
         */
        if (PlatformCore::getModule('commerce')) {
            /**
             * Guest-Routes (öffentlich, ohne Auth)
             * 
             * Für öffentliche Commerce-Seiten (z.B. Produktkatalog)
             */
            ModuleRouter::group('commerce', function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/guest.php');
            }, requireAuth: false);
            
            /**
             * Web-Routes (authentifiziert)
             *
             * Für authentifizierte Commerce-Funktionen
             */
            ModuleRouter::group('commerce', function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
            });

            /**
             * API-Routes (Token-basiert, keine Session/Auth)
             *
             * Für Webhook-Endpoints (Supplier Ingest)
             */
            ModuleRouter::apiGroup('commerce', function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
            }, requireAuth: false);
        }

        /**
         * SCHRITT 3: Migrationen laden
         * 
         * Lädt alle Migrationen aus database/migrations/
         * Wird automatisch bei `php artisan migrate` ausgeführt.
         */
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        /**
         * SCHRITT 4: Config veröffentlichen
         * 
         * Ermöglicht es, die Config in config/commerce.php zu überschreiben.
         * 
         * Publizieren mit:
         * php artisan vendor:publish --tag=config --provider="Platform\Commerce\CommerceServiceProvider"
         * 
         * WICHTIG: mergeConfigFrom funktioniert auch OHNE Publizierung!
         */
        $this->publishes([
            __DIR__.'/../config/commerce.php' => config_path('commerce.php'),
        ], 'config');

        /**
         * SCHRITT 5: Views laden
         * 
         * Registriert Views unter dem Namespace 'commerce'
         * 
         * Verwendung in Views:
         * @return view('commerce::livewire.index')
         */
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'commerce');
        
        /**
         * SCHRITT 6: Livewire Components registrieren
         *
         * Registriert alle Livewire-Komponenten automatisch.
         *
         * Pattern:
         * - Datei: src/Livewire/Articles/Index.php
         * - Alias: commerce.articles.index
         *
         * Verwendung:
         * <livewire:commerce.articles.index />
         */
        $this->registerLivewireComponents();

        /**
         * SCHRITT 7: LLM Tools registrieren
         *
         * Registriert alle LLM-Tools für AI-Agenten.
         */
        $this->registerTools();
    }

    /**
     * Registriert alle Livewire-Komponenten automatisch
     * 
     * Scant das src/Livewire/ Verzeichnis rekursiv und registriert
     * alle PHP-Dateien als Livewire-Komponenten.
     * 
     * NAMING CONVENTION:
     * - Datei: src/Livewire/Articles/Index.php
     * - Namespace: Platform\Commerce\Livewire\Articles\Index
     * - Alias: commerce.articles.index
     * 
     * - Datei: src/Livewire/Products/Show.php
     * - Namespace: Platform\Commerce\Livewire\Products\Show
     * - Alias: commerce.products.show
     * 
     * @return void
     */
    protected function registerLivewireComponents(): void
    {
        $basePath = __DIR__ . '/Livewire';
        $baseNamespace = 'Platform\\Commerce\\Livewire';
        $prefix = 'commerce';

        // Prüfe ob Verzeichnis existiert
        if (!is_dir($basePath)) {
            return;
        }

        // Rekursiv alle PHP-Dateien durchsuchen
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($basePath)
        );

        foreach ($iterator as $file) {
            // Nur PHP-Dateien verarbeiten
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            // Relativen Pfad extrahieren
            $relativePath = str_replace($basePath . DIRECTORY_SEPARATOR, '', $file->getPathname());
            
            // Klassenpfad generieren (z.B. Articles/Index -> Articles\Index)
            $classPath = str_replace(['/', '.php'], ['\\', ''], $relativePath);
            $class = $baseNamespace . '\\' . $classPath;

            // Prüfe ob Klasse existiert
            if (!class_exists($class)) {
                continue;
            }

            // Alias generieren (z.B. Articles/Index -> articles.index)
            // WICHTIG: Str::kebab() muss auf jedes Segment einzeln angewendet werden,
            // nicht auf den gesamten Pfad (sonst wird Settings/TaxRuleRow zu settings/-tax-rule-row)
            $pathWithoutExtension = str_replace('.php', '', $relativePath);
            $segments = preg_split('/[\\\\\/]/', $pathWithoutExtension);
            $kebabSegments = array_map(fn($s) => Str::kebab($s), $segments);
            $alias = $prefix . '.' . implode('.', $kebabSegments);

            // Livewire-Komponente registrieren
            Livewire::component($alias, $class);
        }
    }

    /**
     * Registriert LLM-Tools für AI-Agenten
     *
     * Diese Tools ermöglichen AI-Agenten die Verwaltung von Commerce-Daten
     * wie Artikel-Typen über die ToolRegistry.
     *
     * @return void
     */
    protected function registerTools(): void
    {
        try {
            $registry = resolve(\Platform\Core\Tools\ToolRegistry::class);

            // Article Types Tools
            $registry->register(new \Platform\Commerce\Tools\ListArticleTypesTool());
            $registry->register(new \Platform\Commerce\Tools\CreateArticleTypeTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateArticleTypeTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteArticleTypeTool());

            // Articles Tools (Basiseinheiten mit Preis/SKU)
            $registry->register(new \Platform\Commerce\Tools\ListArticlesTool());
            $registry->register(new \Platform\Commerce\Tools\CreateArticleTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateArticleTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteArticleTool());

            // Article Categories
            $registry->register(new \Platform\Commerce\Tools\ListArticleCategoriesTool());
            $registry->register(new \Platform\Commerce\Tools\CreateArticleCategoryTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateArticleCategoryTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteArticleCategoryTool());

            // Article Prices
            $registry->register(new \Platform\Commerce\Tools\ListArticlePricesTool());
            $registry->register(new \Platform\Commerce\Tools\CreateArticlePriceTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateArticlePriceTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteArticlePriceTool());

            // Article Net Prices
            $registry->register(new \Platform\Commerce\Tools\ListArticleNetPricesTool());
            $registry->register(new \Platform\Commerce\Tools\CreateArticleNetPriceTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateArticleNetPriceTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteArticleNetPriceTool());

            // Manufacturers
            $registry->register(new \Platform\Commerce\Tools\ListManufacturersTool());
            $registry->register(new \Platform\Commerce\Tools\CreateManufacturerTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateManufacturerTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteManufacturerTool());

            // Suppliers
            $registry->register(new \Platform\Commerce\Tools\ListSuppliersTool());
            $registry->register(new \Platform\Commerce\Tools\CreateSupplierTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateSupplierTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteSupplierTool());

            // Article ↔ Supplier (Pivot with purchase price, validity, preferred)
            $registry->register(new \Platform\Commerce\Tools\ListArticleSuppliersTool());
            $registry->register(new \Platform\Commerce\Tools\CreateArticleSupplierTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateArticleSupplierTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteArticleSupplierTool());

            // Cost Standards (interne Personalkostensätze pro Skill-Level)
            $registry->register(new \Platform\Commerce\Tools\ListCostStandardsTool());
            $registry->register(new \Platform\Commerce\Tools\CreateCostStandardTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateCostStandardTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteCostStandardTool());

            // Tax Categories
            $registry->register(new \Platform\Commerce\Tools\ListTaxCategoriesTool());
            $registry->register(new \Platform\Commerce\Tools\CreateTaxCategoryTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateTaxCategoryTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteTaxCategoryTool());

            // Tax Rules
            $registry->register(new \Platform\Commerce\Tools\ListTaxRulesTool());
            $registry->register(new \Platform\Commerce\Tools\CreateTaxRuleTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateTaxRuleTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteTaxRuleTool());

            // Sales Contexts
            $registry->register(new \Platform\Commerce\Tools\ListSalesContextsTool());
            $registry->register(new \Platform\Commerce\Tools\CreateSalesContextTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateSalesContextTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteSalesContextTool());

            // Sales
            $registry->register(new \Platform\Commerce\Tools\ListSalesTool());
            $registry->register(new \Platform\Commerce\Tools\CreateSaleTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateSaleTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteSaleTool());

            // Sale Items
            $registry->register(new \Platform\Commerce\Tools\ListSaleItemsTool());
            $registry->register(new \Platform\Commerce\Tools\CreateSaleItemTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateSaleItemTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteSaleItemTool());

            // Product Rules
            $registry->register(new \Platform\Commerce\Tools\ListProductRulesTool());
            $registry->register(new \Platform\Commerce\Tools\CreateProductRuleTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateProductRuleTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteProductRuleTool());

            // Product Promotions
            $registry->register(new \Platform\Commerce\Tools\ListProductPromotionsTool());
            $registry->register(new \Platform\Commerce\Tools\CreateProductPromotionTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateProductPromotionTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteProductPromotionTool());

            // Product Boards
            $registry->register(new \Platform\Commerce\Tools\ListProductBoardsTool());
            $registry->register(new \Platform\Commerce\Tools\CreateProductBoardTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateProductBoardTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteProductBoardTool());

            // Product Board Slots
            $registry->register(new \Platform\Commerce\Tools\ListProductBoardSlotsTool());
            $registry->register(new \Platform\Commerce\Tools\CreateProductBoardSlotTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateProductBoardSlotTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteProductBoardSlotTool());

            // Attribute Sets
            $registry->register(new \Platform\Commerce\Tools\ListAttributeSetsTool());
            $registry->register(new \Platform\Commerce\Tools\CreateAttributeSetTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateAttributeSetTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteAttributeSetTool());

            // Attribute Set Items
            $registry->register(new \Platform\Commerce\Tools\ListAttributeSetItemsTool());
            $registry->register(new \Platform\Commerce\Tools\CreateAttributeSetItemTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateAttributeSetItemTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteAttributeSetItemTool());

            // Products
            $registry->register(new \Platform\Commerce\Tools\ListProductsTool());
            $registry->register(new \Platform\Commerce\Tools\CreateProductTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateProductTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteProductTool());

            // Product-Slot Attachments
            $registry->register(new \Platform\Commerce\Tools\ListProductSlotAttachmentsTool());
            $registry->register(new \Platform\Commerce\Tools\AttachProductSlotTool());
            $registry->register(new \Platform\Commerce\Tools\DetachProductSlotTool());

            // Product Slots
            $registry->register(new \Platform\Commerce\Tools\ListProductSlotsTool());
            $registry->register(new \Platform\Commerce\Tools\CreateProductSlotTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateProductSlotTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteProductSlotTool());

            // Product Slot Dimensions
            $registry->register(new \Platform\Commerce\Tools\ListProductSlotDimensionsTool());
            $registry->register(new \Platform\Commerce\Tools\CreateProductSlotDimensionTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateProductSlotDimensionTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteProductSlotDimensionTool());

            // Product Slot Dimension Values
            $registry->register(new \Platform\Commerce\Tools\ListProductSlotDimensionValuesTool());
            $registry->register(new \Platform\Commerce\Tools\CreateProductSlotDimensionValueTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateProductSlotDimensionValueTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteProductSlotDimensionValueTool());

            // Product Slot Variants
            $registry->register(new \Platform\Commerce\Tools\ListProductSlotVariantsTool());
            $registry->register(new \Platform\Commerce\Tools\CreateProductSlotVariantTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateProductSlotVariantTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteProductSlotVariantTool());

            // --- Phase 2: Price System ---

            // Price Lists
            $registry->register(new \Platform\Commerce\Tools\ListPriceListsTool());
            $registry->register(new \Platform\Commerce\Tools\CreatePriceListTool());
            $registry->register(new \Platform\Commerce\Tools\UpdatePriceListTool());
            $registry->register(new \Platform\Commerce\Tools\DeletePriceListTool());

            // Price Tiers
            $registry->register(new \Platform\Commerce\Tools\ListPriceTiersTool());
            $registry->register(new \Platform\Commerce\Tools\CreatePriceTierTool());
            $registry->register(new \Platform\Commerce\Tools\UpdatePriceTierTool());
            $registry->register(new \Platform\Commerce\Tools\DeletePriceTierTool());

            // Customer Groups
            $registry->register(new \Platform\Commerce\Tools\ListCustomerGroupsTool());
            $registry->register(new \Platform\Commerce\Tools\CreateCustomerGroupTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateCustomerGroupTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteCustomerGroupTool());

            // Customer Group Prices
            $registry->register(new \Platform\Commerce\Tools\ListCustomerGroupPricesTool());
            $registry->register(new \Platform\Commerce\Tools\CreateCustomerGroupPriceTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateCustomerGroupPriceTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteCustomerGroupPriceTool());

            // Price Resolver
            $registry->register(new \Platform\Commerce\Tools\ResolvePriceTool());

            // --- Phase 3: Inventory ---

            // Warehouses
            $registry->register(new \Platform\Commerce\Tools\ListWarehousesTool());
            $registry->register(new \Platform\Commerce\Tools\CreateWarehouseTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateWarehouseTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteWarehouseTool());

            // Stock Levels (read-only)
            $registry->register(new \Platform\Commerce\Tools\ListStockLevelsTool());

            // Stock Movements
            $registry->register(new \Platform\Commerce\Tools\ListStockMovementsTool());
            $registry->register(new \Platform\Commerce\Tools\CreateStockMovementTool());

            // Stock Reservations
            $registry->register(new \Platform\Commerce\Tools\ListStockReservationsTool());
            $registry->register(new \Platform\Commerce\Tools\CreateStockReservationTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteStockReservationTool());

            // Stock Transfer
            $registry->register(new \Platform\Commerce\Tools\TransferStockTool());

            // --- Phase 4: Units ---

            // Units
            $registry->register(new \Platform\Commerce\Tools\ListUnitsTool());
            $registry->register(new \Platform\Commerce\Tools\CreateUnitTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateUnitTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteUnitTool());

            // Unit Conversions
            $registry->register(new \Platform\Commerce\Tools\ListUnitConversionsTool());
            $registry->register(new \Platform\Commerce\Tools\CreateUnitConversionTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateUnitConversionTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteUnitConversionTool());

            // Unit Converter
            $registry->register(new \Platform\Commerce\Tools\ConvertUnitTool());

            // --- Phase 5: Rule Engine ---
            $registry->register(new \Platform\Commerce\Tools\EvaluateRulesTool());

            // --- Phase 6: Article Availability ---
            $registry->register(new \Platform\Commerce\Tools\ListArticleAvailabilitiesTool());
            $registry->register(new \Platform\Commerce\Tools\CreateArticleAvailabilityTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateArticleAvailabilityTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteArticleAvailabilityTool());
            $registry->register(new \Platform\Commerce\Tools\CheckAvailabilityTool());

            // --- Phase 7: Catalogs ---
            $registry->register(new \Platform\Commerce\Tools\ListCatalogsTool());
            $registry->register(new \Platform\Commerce\Tools\CreateCatalogTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateCatalogTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteCatalogTool());

            // Catalog Sections
            $registry->register(new \Platform\Commerce\Tools\ListCatalogSectionsTool());
            $registry->register(new \Platform\Commerce\Tools\CreateCatalogSectionTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateCatalogSectionTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteCatalogSectionTool());

            // Catalog Section Board Attachments
            $registry->register(new \Platform\Commerce\Tools\AttachCatalogSectionBoardTool());
            $registry->register(new \Platform\Commerce\Tools\DetachCatalogSectionBoardTool());

            // --- Supplier Stream System ---

            // Supplier Field Mappings
            $registry->register(new \Platform\Commerce\Tools\ListSupplierFieldMappingsTool());
            $registry->register(new \Platform\Commerce\Tools\CreateSupplierFieldMappingTool());
            $registry->register(new \Platform\Commerce\Tools\UpdateSupplierFieldMappingTool());
            $registry->register(new \Platform\Commerce\Tools\DeleteSupplierFieldMappingTool());

            // Supplier Imports
            $registry->register(new \Platform\Commerce\Tools\ListSupplierImportsTool());
            $registry->register(new \Platform\Commerce\Tools\TriggerSupplierImportTool());
        } catch (\Throwable $e) {
            \Log::warning('Commerce: Tool-Registrierung fehlgeschlagen', ['error' => $e->getMessage()]);
        }
    }
}

