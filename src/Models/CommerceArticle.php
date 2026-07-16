<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class CommerceArticle extends Model
{
    use HasFactory, SoftDeletes, \Platform\ActivityLog\Traits\LogsActivity;

    protected $table = 'commerce_articles';

    protected $fillable = [
        'user_id',
        'team_id',
        'name',
        'status',
        'description',
        'color',
        'sku',
        'gtin',
        'ean',
        'upc',
        'isbn',
        'commerce_manufacturer_id',
        'manufacturer_part_number',
        'country_of_origin',
        'hs_code',
        'price',
        'base_price_quantity',
        'base_price_unit',
        'tax_class',
        'weight',
        'width',
        'height',
        'depth',
        'volume',
        'is_fragile',
        'shipping_class',
        'lead_time_days',
        'is_available',
        'stock_level',
        'stock_alert_threshold',
        'backorder_allowed',
        'is_hazardous',
        'expiry_date',
        'storage_temperature',
        'recyclable',
        'category_id',
        'commerce_tax_category_id',
        'revenue_account',
        'cost_standard_id',
        'cost_quantity',
        'cost_unit',
        'commerce_article_type_id',
        'commerce_sales_unit_id',
        'commerce_storage_unit_id',
        'sales_to_storage_factor',
        'tags',
        'is_digital',
        'is_physical',
        'short_description',
        'procurement_type',
        'long_description',
        'product_highlights',
        'created_by',
        'updated_by',
        'published_at',
        'archived_at',
    ];

    protected $casts = [
        'status' => \Platform\Commerce\Enums\ArticleStatus::class,
        'tags' => 'array',
        'product_highlights' => 'array',
        'is_fragile' => 'boolean',
        'is_available' => 'boolean',
        'backorder_allowed' => 'boolean',
        'is_hazardous' => 'boolean',
        'recyclable' => 'boolean',
        'is_digital' => 'boolean',
        'is_physical' => 'boolean',
        'expiry_date' => 'date',
        'published_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function (self $model) {
            if (!$model->team_id && Auth::check()) {
                $model->team_id = Auth::user()->currentTeam->id ?? null;
            }
        });
    }

    public function attributeSets()
    {
        return $this->belongsToMany(
            CommerceAttributeSet::class,
            'commerce_article_commerce_attribute_set',
            'commerce_article_id',
            'commerce_attribute_set_id'
        );
    }

    public function attributeSetItems()
    {
        return $this->belongsToMany(
            CommerceAttributeSetItem::class,
            'commerce_article_commerce_attribute_set_item',
            'commerce_article_id',
            'commerce_attribute_set_item_id'
        );
    }

    /**
     * HINWEIS: Activity-, Media- und Account-Beziehungen wurden entfernt.
     * 
     * Später können hier Beziehungen zu:
     * - Brands (Marken)
     * - CRM Contacts (Kontakte)
     * hinzugefügt werden.
     */

    public function manufacturer()
    {
        return $this->belongsTo(CommerceManufacturer::class, 'commerce_manufacturer_id');
    }

    public function articleNetPrices()
    {
        return $this->hasMany(CommerceArticleNetPrice::class, 'commerce_article_id');
    }

    public function articlePrices()
    {
        return $this->hasMany(CommerceArticlePrice::class, 'commerce_article_id');
    }

    public function category()
    {
        return $this->belongsTo(CommerceArticleCategory::class, 'category_id');
    }

    public function taxCategory()
    {
        return $this->belongsTo(CommerceTaxCategory::class, 'commerce_tax_category_id');
    }

    public function articleType()
    {
        return $this->belongsTo(CommerceArticleType::class, 'commerce_article_type_id');
    }

    public function costStandard()
    {
        return $this->belongsTo(CommerceCostStandard::class, 'cost_standard_id');
    }

    /**
     * Berechneter interner Einkaufspreis = Kostensatz × Menge.
     *
     * Nutzt cost_per_hour wenn cost_unit='h', sonst cost_per_day. Null wenn
     * kein Kostensatz oder Menge fehlt.
     */
    public function getInternalCostAttribute(): ?float
    {
        $standard = $this->costStandard;
        $quantity = $this->cost_quantity !== null ? (float) $this->cost_quantity : null;

        if (!$standard || $quantity === null) {
            return null;
        }

        $rate = $this->cost_unit === 'd'
            ? $standard->cost_per_day
            : $standard->cost_per_hour;

        return $rate !== null ? (float) $rate * $quantity : null;
    }

    /**
     * Marge = Verkaufspreis − interne Kosten. Null wenn interner Kostensatz fehlt.
     */
    public function getInternalMarginAttribute(): ?float
    {
        $internal = $this->internal_cost;
        if ($internal === null || $this->price === null) {
            return null;
        }
        return (float) $this->price - $internal;
    }

    /**
     * Effektives Erlöskonto (Fibu-/Sachkonto) für die Verbuchung von Umsätzen.
     *
     * Vorrang hat das Override am Artikel (revenue_account); ist dort nichts
     * gepflegt, greift das Standard-Erlöskonto der Steuerkategorie. Null wenn
     * weder Artikel noch Steuerkategorie ein Konto gesetzt haben.
     */
    public function getEffectiveRevenueAccountAttribute(): ?string
    {
        $own = $this->revenue_account !== null ? trim((string) $this->revenue_account) : '';
        if ($own !== '') {
            return $own;
        }

        $fromCategory = $this->taxCategory?->revenue_account;
        $fromCategory = $fromCategory !== null ? trim((string) $fromCategory) : '';

        return $fromCategory !== '' ? $fromCategory : null;
    }

    public function products()
    {
        return $this->hasMany(CommerceProduct::class, 'commerce_article_id');
    }

    public function suppliers()
    {
        return $this->belongsToMany(
            CommerceSupplier::class,
            'commerce_article_supplier',
            'article_id',
            'supplier_id'
        )->withPivot([
            'id',
            'external_id',
            'purchase_price',
            'purchase_currency',
            'valid_from',
            'valid_until',
            'is_preferred',
            'last_synced_at',
        ])->withTimestamps();
    }

    public function articleSuppliers()
    {
        return $this->hasMany(CommerceArticleSupplier::class, 'article_id');
    }

    public function creator()
    {
        return $this->belongsTo(\Platform\Core\Models\User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\Platform\Core\Models\User::class, 'updated_by');
    }

    public function team()
    {
        return $this->belongsTo(\App\Models\Team::class, 'team_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', \Platform\Commerce\Enums\ArticleStatus::Published);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', \Platform\Commerce\Enums\ArticleStatus::Draft);
    }

    public function scopeArchived($query)
    {
        return $query->where('status', \Platform\Commerce\Enums\ArticleStatus::Archived);
    }

    public function stockLevels()
    {
        return $this->hasMany(CommerceStockLevel::class, 'commerce_article_id');
    }

    public function salesUnit()
    {
        return $this->belongsTo(CommerceUnit::class, 'commerce_sales_unit_id');
    }

    public function storageUnit()
    {
        return $this->belongsTo(CommerceUnit::class, 'commerce_storage_unit_id');
    }

    public function availabilities()
    {
        return $this->hasMany(CommerceArticleAvailability::class, 'commerce_article_id');
    }
}

