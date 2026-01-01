<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommerceProductSlotDimensionValue extends Model
{
    use HasFactory;

    protected $table = 'commerce_product_slot_dimension_values';

    protected $fillable = [
        'commerce_product_slot_dimension_id',
        'value',
    ];

    public function dimension()
    {
        return $this->belongsTo(CommerceProductSlotDimension::class, 'commerce_product_slot_dimension_id');
    }
}

