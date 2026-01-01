<?php

return [
    'routing' => [
        'mode' => env('COMMERCE_MODE', 'path'),
        'prefix' => 'commerce',
    ],
    'guard' => 'web',

    'navigation' => [
        'route' => 'commerce.index',
        'icon'  => 'heroicon-o-shopping-bag',
        'order' => 40,
    ],

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
    // Billables vorerst leer für Commerce
    'billables' => []
];

