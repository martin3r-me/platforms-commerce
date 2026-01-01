<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommerceProductPromotion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'commerce_product_promotions';

    protected $fillable = [
        'commerce_product_id',
        'discount_value',
        'discount_percentage',
        'min_cart_value',
        'promotion_start',
        'promotion_end',
    ];

    protected $casts = [
        'promotion_start' => 'datetime',
        'promotion_end' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(CommerceProduct::class, 'commerce_product_id');
    }
}

