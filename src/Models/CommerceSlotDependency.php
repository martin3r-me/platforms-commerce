<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommerceSlotDependency extends Model
{
    use HasFactory;

    protected $table = 'commerce_slot_dependencies';

    protected $fillable = [
        'commerce_product_slot_id',
        'commerce_product_slot_variant_id',
    ];

    public function slot()
    {
        return $this->belongsTo(CommerceProductSlot::class, 'commerce_product_slot_id');
    }

    public function variant()
    {
        return $this->belongsTo(CommerceProductSlotVariant::class, 'commerce_product_slot_variant_id');
    }
}

