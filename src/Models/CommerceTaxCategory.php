<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class CommerceTaxCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'commerce_tax_categories';

    protected $fillable = [
        'name',
        'default_rate',
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

    public function articles()
    {
        return $this->hasMany(CommerceArticle::class, 'commerce_tax_category_id');
    }

    public function articlePrices()
    {
        return $this->hasMany(CommerceArticlePrice::class, 'commerce_tax_category_id');
    }
}

