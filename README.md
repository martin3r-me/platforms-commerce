# Commerce Modul

Commerce-Modul für die Platform zur Verwaltung von Produkten, Artikeln, Attributen und Einstellungen.

## 📋 Übersicht

Das Commerce-Modul bietet eine vollständige E-Commerce-Lösung mit:
- Artikelverwaltung mit umfangreichen Metadaten
- Produktverwaltung mit Boards und Slots
- Attributsets und Attribut-Items
- Steuerkategorien und Steuerregeln
- Verkaufs-Kontexte
- Hersteller und Lieferanten

## 🚀 Installation

Das Modul wird automatisch über den Service Provider geladen, wenn es in der `composer.json` registriert ist.

## 📁 Struktur

```
commerce/
├── composer.json              # Package-Definition
├── config/
│   └── commerce.php          # Modul-Konfiguration
├── database/
│   └── migrations/           # Datenbank-Migrationen
├── resources/
│   └── views/
│       └── livewire/         # Blade Templates
├── routes/
│   ├── web.php              # Authentifizierte Routes
│   └── guest.php            # Öffentliche Routes
└── src/
    ├── CommerceServiceProvider.php  # Service Provider
    ├── Livewire/            # Livewire Komponenten
    ├── Models/              # Eloquent Modelle
    └── Services/            # Business Logic Services
```

## 🔧 Wichtige Komponenten

### Service Provider

Der `CommerceServiceProvider` folgt dem Template-Pattern:
- **register()**: Config wird hier geladen (Laravel Best Practice)
- **boot()**: Modul-Registrierung, Routes, Views, Livewire

### Routes

- **Web-Routes** (`routes/web.php`): Authentifizierte Commerce-Funktionen
- **Guest-Routes** (`routes/guest.php`): Öffentliche Commerce-Seiten (z.B. Produktkatalog)

### Models

- `CommerceArticle` - Artikel mit Preisen, Kategorien, etc.
- `CommerceProduct` - Produkte mit Boards und Slots
- `CommerceAttributeSet` - Attributsets für Produkte
- `CommerceTaxRule` - Steuerregeln
- Weitere Models für Hersteller, Lieferanten, etc.

### Livewire Components

- `Index` - Hauptübersicht
- `Articles/Index` - Artikel-Übersicht
- `Articles/Article` - Artikel-Detail
- `Products/Product` - Produkt-Detail
- `Products/Boards/Board` - Produkt-Board
- `Attributes/Index` - Attribute-Übersicht
- `Attributes/AttributeSet` - Attributset-Detail
- `Settings/Index` - Einstellungen

## 📝 Verwendung

### Routes

```php
// Dashboard
Route::get('/', Index::class)->name('commerce.index');

// Artikel
Route::get('/articles', ArticlesIndex::class)->name('commerce.articles.index');
Route::get('/articles/{commerceArticle}', Article::class)->name('commerce.articles.show');

// Produkte
Route::get('/products', ProductsBoardsIndex::class)->name('commerce.products.index');
Route::get('/products/{commerceProduct}', Product::class)->name('commerce.products.show');
```

### Models

```php
use Platform\Commerce\Models\CommerceArticle;

$article = CommerceArticle::where('team_id', $team->id)->first();
```

## 🎯 Best Practices

1. **Team-basiert**: Alle Daten sind team-basiert
2. **Activity Logging**: Models nutzen `LogsActivity` Trait
3. **UUIDs**: Alle Models verwenden UUIDs
4. **Policies**: Authorization über Policies

## 📚 Weitere Ressourcen

- Siehe `platform/modules/module-template` für Template-Pattern
- Siehe `platform/modules/hcm` für komplexere Beispiele
- Siehe `platform/core/src/PlatformCore.php` für Modul-Registrierung

