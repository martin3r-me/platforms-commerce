<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommerceProductSlotVariant extends Model
{
    use HasFactory;

    protected $table = 'commerce_product_slot_variants';

    protected $fillable = [
        'commerce_product_slot_id',
        'commerce_article_id',
    ];

    public function getVariantNameAttribute()
    {
        return $this->dimensionValues->map(function ($dimensionValue) {
            return $dimensionValue->dimensionValue->dimension->name . ': ' . $dimensionValue->dimensionValue->value;
        })->implode(', ');
    }

    public function dimensionValues()
    {
        return $this->hasMany(CommerceProductSlotVariantDimensionValue::class, 'commerce_product_slot_variant_id');
    }

    public function article()
    {
        return $this->belongsTo(CommerceArticle::class, 'commerce_article_id');
    }

    public function slot()
    {
        return $this->belongsTo(CommerceProductSlot::class, 'commerce_product_slot_id');
    }
}

