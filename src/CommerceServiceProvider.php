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
        
        /**
         * Commands registrieren (optional)
         * 
         * Falls das Modul Artisan Commands hat:
         * 
         * if ($this->app->runningInConsole()) {
         *     $this->commands([
         *         \Platform\Commerce\Console\Commands\YourCommand::class,
         *     ]);
         * }
         */
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
}

