<?php

/**
 * Commerce Module Configuration
 * 
 * Diese Config-Datei definiert die Konfiguration für das Commerce-Modul.
 * 
 * WICHTIG FÜR LLMs:
 * - Alle Routes müssen mit dem Modul-Prefix beginnen (commerce)
 * - Navigation definiert, wie das Modul in der Hauptnavigation erscheint
 * - Billables definiert Billing-Regeln (optional)
 * 
 * @see Platform\Core\PlatformCore::registerModule() für Details zur Modul-Registrierung
 */

return [
    /**
     * Routing-Konfiguration
     * 
     * 'mode': 'path' = /commerce/... (Standard)
     *         'subdomain' = commerce.domain.com/... (Alternative)
     * 'prefix': URL-Präfix für alle Routes
     */
    'routing' => [
        'mode' => env('COMMERCE_MODE', 'path'),
        'prefix' => 'commerce',
    ],
    
    /**
     * Guard für Authentication
     * Standard: 'web'
     */
    'guard' => 'web',

    /**
     * Navigation-Konfiguration
     * 
     * Definiert, wie das Modul in der Hauptnavigation erscheint.
     * 'route': Route-Name für den Link
     * 'icon': Heroicon-Name (ohne heroicon-o- Präfix)
     * 'order': Sortier-Reihenfolge (niedrigere Zahlen = weiter oben)
     */
    'navigation' => [
        'route' => 'commerce.index',
        'icon'  => 'heroicon-o-shopping-bag',
        'order' => 40,
    ],

    'sidebar' => [
        [
            'group' => 'Navigation',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'commerce.index', 'icon' => 'heroicon-o-home'],
                ['label' => 'Artikel', 'route' => 'commerce.articles.index', 'icon' => 'heroicon-o-rectangle-stack'],
                ['label' => 'Produkte', 'route' => 'commerce.products.index', 'icon' => 'heroicon-o-cube'],
                ['label' => 'Boards', 'route' => 'commerce.products.boards.index', 'icon' => 'heroicon-o-view-columns'],
                ['label' => 'Lieferanten', 'route' => 'commerce.suppliers.index', 'icon' => 'heroicon-o-truck'],
                ['label' => 'Kataloge', 'route' => 'commerce.catalogs.index', 'icon' => 'heroicon-o-book-open'],
                ['label' => 'Attribute', 'route' => 'commerce.attributes.index', 'icon' => 'heroicon-o-tag'],
                ['label' => 'Einstellungen', 'route' => 'commerce.settings.index', 'icon' => 'heroicon-o-cog-6-tooth'],
            ],
        ],
    ],

    /**
     * Billables-Konfiguration (optional)
     * 
     * Definiert Billing-Regeln für das Modul.
     * Wird verwendet, um Nutzungsgebühren zu berechnen.
     * 
     * Struktur:
     * - 'model': Model-Klasse, die gebillt wird
     * - 'type': Billing-Typ (z.B. 'per_item', 'per_usage')
     * - 'label': Anzeige-Name
     * - 'description': Beschreibung
     * - 'pricing': Array von Preisregeln
     * - 'billing_period': Abrechnungszeitraum (daily, monthly, etc.)
     * 
     * @see Platform\Core\Billing für Details
     */
    'billables' => [
        [
            'model' => \Platform\Commerce\Models\CommerceArticle::class,
            'type' => 'per_item',
            'label' => 'Artikel',
            'description' => 'Jeder angelegte Artikel verursacht tägliche Kosten nach Nutzung.',
            'pricing' => [
                ['cost_per_day' => 0.0025, 'start_date' => '2025-01-01', 'end_date' => null]
            ],
            'free_quota' => null,
            'min_cost' => null,
            'max_cost' => null,
            'billing_period' => 'daily',
            'start_date' => '2026-01-01',
            'end_date' => null,
            'trial_period_days' => 0,
            'discount_percent' => 0,
            'exempt_team_ids' => [],
            'priority' => 100,
            'active' => true,
        ],
        [
            'model' => \Platform\Commerce\Models\CommerceProduct::class,
            'type' => 'per_item',
            'label' => 'Produkt',
            'description' => 'Jedes angelegte Produkt verursacht tägliche Kosten nach Nutzung.',
            'pricing' => [
                ['cost_per_day' => 0.005, 'start_date' => '2025-01-01', 'end_date' => null]
            ],
            'free_quota' => null,
            'min_cost' => null,
            'max_cost' => null,
            'billing_period' => 'daily',
            'start_date' => '2026-01-01',
            'end_date' => null,
            'trial_period_days' => 0,
            'discount_percent' => 0,
            'exempt_team_ids' => [],
            'priority' => 100,
            'active' => true,
        ],
    ],
];

