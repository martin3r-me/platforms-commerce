<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class CommercePriceTier extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'commerce_price_tiers';

    protected $fillable = [
        'team_id',
        'commerce_price_list_id',
        'commerce_article_id',
        'min_quantity',
        'max_quantity',
        'price',
        'discount_percentage',
    ];

    protected $casts = [
        'min_quantity' => 'decimal:4',
        'max_quantity' => 'decimal:4',
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

    public function priceList()
    {
        return $this->belongsTo(CommercePriceList::class, 'commerce_price_list_id');
    }

    public function article()
    {
        return $this->belongsTo(CommerceArticle::class, 'commerce_article_id');
    }
}
