<?php

/**
 * Commerce Module Configuration
 * 
 * Diese Config-Datei definiert die Konfiguration für das Commerce-Modul.
 * 
 * WICHTIG FÜR LLMs:
 * - Alle Routes müssen mit dem Modul-Prefix beginnen (commerce)
 * - Navigation definiert, wie das Modul in der Hauptnavigation erscheint
 * - Sidebar definiert die modul-spezifische Sidebar-Struktur
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

    /**
     * Sidebar-Konfiguration
     * 
     * Definiert die Sidebar-Struktur für das Modul.
     * 
     * Struktur:
     * - 'group': Gruppenname (optional)
     * - 'items': Array von Sidebar-Items
     *   - 'label': Anzeige-Text
     *   - 'route': Route-Name
     *   - 'icon': Heroicon-Name
     * 
     * Alternative: 'dynamic' für dynamische Listen (z.B. aus Datenbank)
     *   - 'model': Model-Klasse
     *   - 'team_based': true/false (nach Team filtern)
     *   - 'order_by': Sortier-Feld
     *   - 'route': Basis-Route (wird mit ID erweitert)
     *   - 'icon': Icon für alle Items
     *   - 'label_key': Feldname für Label
     */
    'sidebar' => [
        [
            'group' => 'Allgemein',
            'items' => [
                [
                    'label' => 'Dashboard',
                    'route' => 'commerce.index',
                    'icon'  => 'heroicon-o-home',
                ],
                [
                    'label' => 'Artikel',
                    'route' => 'commerce.articles.index',
                    'icon'  => 'heroicon-o-rectangle-stack',
                ],
                [
                    'label' => 'Produkte',
                    'route' => 'commerce.products.index',
                    'icon'  => 'heroicon-o-cube',
                ],
                [
                    'label' => 'Attribute',
                    'route' => 'commerce.attributes.index',
                    'icon'  => 'heroicon-o-tag',
                ],
                [
                    'label' => 'Einstellungen',
                    'route' => 'commerce.settings.index',
                    'icon'  => 'heroicon-o-cog-6-tooth',
                ],
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
    'billables' => [],
];

