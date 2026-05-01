<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommerceProductSlotDimension extends Model
{
    use HasFactory, \Platform\ActivityLog\Traits\LogsActivity;

    protected $table = 'commerce_product_slot_dimensions';

    protected $fillable = [
        'commerce_product_slot_id',
        'name',
    ];

    public function values()
    {
        return $this->hasMany(CommerceProductSlotDimensionValue::class, 'commerce_product_slot_dimension_id');
    }
}

