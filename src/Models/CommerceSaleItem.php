<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommerceSaleItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'commerce_sale_items';

    protected $fillable = [
        'commerce_sale_id',
        'commerce_product_id',
        'commerce_article_batch_id',
        'quantity',
        'price',
    ];

    public function sale()
    {
        return $this->belongsTo(CommerceSale::class, 'commerce_sale_id');
    }

    public function product()
    {
        return $this->belongsTo(CommerceProduct::class, 'commerce_product_id');
    }

    public function batch()
    {
        return $this->belongsTo(CommerceArticleBatch::class, 'commerce_article_batch_id');
    }
}

