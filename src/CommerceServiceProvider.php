<?php

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
    public function register(): void
    {
        // Reserve für zukünftige Command-Registrierung
    }

    public function boot(): void
    {
        // Config veröffentlichen & zusammenführen (früh, damit Registrierung Config sieht)
        $this->publishes([
            __DIR__.'/../config/commerce.php' => config_path('commerce.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__.'/../config/commerce.php', 'commerce');

        // Modul-Registrierung (nach mergeConfigFrom), wenn Module-Tabelle existiert
        if (Schema::hasTable('modules')) {
            PlatformCore::registerModule([
                'key'        => 'commerce',
                'title'      => 'Commerce',
                'routing'    => config('commerce.routing', ['mode' => 'path', 'prefix' => 'commerce']),
                'guard'      => config('commerce.guard', 'web'),
                'navigation' => config('commerce.navigation', ['route' => 'commerce.index', 'order' => 40]),
                'sidebar'    => config('commerce.sidebar', []),
                'billables'  => config('commerce.billables', []),
            ]);
        }

        // Routen nur laden, wenn das Modul registriert wurde
        if (PlatformCore::getModule('commerce')) {
            ModuleRouter::group('commerce', function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/guest.php');
            }, requireAuth: false);

            ModuleRouter::group('commerce', function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
            });
        }

        // Migrations, Views, Livewire-Komponenten
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'commerce');
        $this->registerLivewireComponents();
    }

    protected function registerLivewireComponents(): void
    {
        $basePath = __DIR__ . '/Livewire';
        $baseNamespace = 'Platform\\Commerce\\Livewire';
        $prefix = 'commerce';

        if (!is_dir($basePath)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($basePath)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = str_replace($basePath . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $classPath = str_replace(['/', '.php'], ['\\', ''], $relativePath);
            $class = $baseNamespace . '\\' . $classPath;

            if (!class_exists($class)) {
                continue;
            }

            // commerce.article.index aus commerce + article/index.php
            $aliasPath = str_replace(['\\', '/'], '.', Str::kebab(str_replace('.php', '', $relativePath)));
            $alias = $prefix . '.' . $aliasPath;

            Livewire::component($alias, $class);
        }
    }
}

