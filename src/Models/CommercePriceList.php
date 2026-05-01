<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Platform\Commerce\Enums\PriceType;

class CommercePriceList extends Model
{
    use HasFactory, SoftDeletes, \Platform\ActivityLog\Traits\LogsActivity;

    protected $table = 'commerce_price_lists';

    protected $fillable = [
        'team_id',
        'user_id',
        'name',
        'description',
        'price_type',
        'priority',
        'is_active',
        'valid_from',
        'valid_until',
    ];

    protected $casts = [
        'price_type' => PriceType::class,
        'is_active' => 'boolean',
        'priority' => 'integer',
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

    public function tiers()
    {
        return $this->hasMany(CommercePriceTier::class, 'commerce_price_list_id');
    }

    public function articlePrices()
    {
        return $this->hasMany(CommerceArticlePrice::class, 'commerce_price_list_id');
    }

    public function customerGroupPrices()
    {
        return $this->hasMany(CommerceCustomerGroupPrice::class, 'commerce_price_list_id');
    }

    public function creator()
    {
        return $this->belongsTo(\Platform\Core\Models\User::class, 'user_id');
    }

    public function team()
    {
        return $this->belongsTo(\App\Models\Team::class, 'team_id');
    }
}
