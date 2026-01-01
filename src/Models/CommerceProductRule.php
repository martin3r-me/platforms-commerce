<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommerceProductRule extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'commerce_product_rules';

    protected $fillable = [
        'commerce_product_id',
        'max_quantity_per_order',
        'min_order_value',
        'sale_period_start',
        'sale_period_end',
    ];

    protected $casts = [
        'sale_period_start' => 'datetime',
        'sale_period_end' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(CommerceProduct::class, 'commerce_product_id');
    }
}

