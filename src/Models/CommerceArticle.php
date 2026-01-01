<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class CommerceArticle extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'commerce_articles';

    protected $fillable = [
        'user_id',
        'team_id',
        'modules_relations_account_id',
        'name',
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
        'tags',
        'is_digital',
        'is_physical',
        'short_description',
        'long_description',
        'product_highlights',
        'created_by',
        'updated_by',
        'published_at',
        'archived_at',
    ];

    protected $casts = [
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

    public function activities()
    {
        return $this->morphMany(\App\Models\Activity::class, 'activityable')->latest();
    }

    public function customFields()
    {
        return $this->morphMany(\App\Models\CustomField::class, 'customizable')->orderBy('order');
    }

    public function media()
    {
        return $this->morphToMany(\App\Models\Media::class, 'mediable')
                    ->withTimestamps();
    }

    public function files()
    {
        return $this->morphToMany(\App\Models\Media::class, 'mediable')
            ->whereHas('mimeType', function ($q) {
                return $q->where('mime_type', 'not like', '%image%');
            });
    }

    public function images()
    {
        return $this->morphToMany(\App\Models\Media::class, 'mediable')
            ->whereHas('mimeType', function ($q) {
                return $q->where('mime_type', 'like', '%image%');
            });
    }

    public function hero()
    {
        return $this->morphToMany(\App\Models\Media::class, 'heroable', 'heroables')->withTimestamps();
    }

    public function toolboxes()
    {
        return $this->morphMany(\App\Models\Toolboxes\ToolboxesToolbox::class, 'toolboxable')->orderBy('order');
    }

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

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function team()
    {
        return $this->belongsTo(\App\Models\Team::class, 'team_id');
    }
}

