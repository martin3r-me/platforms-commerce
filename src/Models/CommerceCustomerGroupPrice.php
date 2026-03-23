<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class CommerceCustomerGroupPrice extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'commerce_customer_group_prices';

    protected $fillable = [
        'team_id',
        'commerce_customer_group_id',
        'commerce_article_id',
        'commerce_price_list_id',
        'price',
        'discount_percentage',
    ];

    protected $casts = [
        'price' => 'decimal:4',
        'discount_percentage' => 'decimal:2',
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

    public function customerGroup()
    {
        return $this->belongsTo(CommerceCustomerGroup::class, 'commerce_customer_group_id');
    }

    public function article()
    {
        return $this->belongsTo(CommerceArticle::class, 'commerce_article_id');
    }

    public function priceList()
    {
        return $this->belongsTo(CommercePriceList::class, 'commerce_price_list_id');
    }
}
