<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommerceProductSlotVariantDimensionValue extends Model
{
    use HasFactory;

    protected $table = 'commerce_product_slot_variant_dimension_values';

    protected $fillable = [
        'commerce_product_slot_variant_id',
        'commerce_product_slot_dimension_value_id',
    ];

    public function variant()
    {
        return $this->belongsTo(CommerceProductSlotVariant::class, 'commerce_product_slot_variant_id');
    }

    public function dimensionValue()
    {
        return $this->belongsTo(CommerceProductSlotDimensionValue::class, 'commerce_product_slot_dimension_value_id');
    }
}

