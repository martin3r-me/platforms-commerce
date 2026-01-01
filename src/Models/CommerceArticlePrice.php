<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class CommerceArticlePrice extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'commerce_article_prices';

    protected $fillable = [
        'commerce_article_id',
        'commerce_sales_context_id',
        'commerce_tax_category_id',
        'net_price',
        'gross_price',
        'tax_rate',
        'valid_from',
        'valid_until',
        'user_id',
        'team_id'
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
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

    public function article()
    {
        return $this->belongsTo(CommerceArticle::class, 'commerce_article_id');
    }

    public function salesContext()
    {
        return $this->belongsTo(CommerceSalesContext::class, 'commerce_sales_context_id');
    }

    public function taxCategory()
    {
        return $this->belongsTo(CommerceTaxCategory::class, 'commerce_tax_category_id');
    }
}

