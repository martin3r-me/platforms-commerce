<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Platform\Commerce\Enums\ChannelType;

class CommerceSalesContext extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'commerce_sales_contexts';

    protected $fillable = [
        'name',
        'description',
        'valid_from',
        'valid_until',
        'channel_type',
        'priority',
        'user_id',
        'team_id',
        'is_default',
        'settings',
    ];

    protected $casts = [
        'channel_type' => ChannelType::class,
        'priority' => 'integer',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_default' => 'boolean',
        'settings' => 'array',
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

    public function articlePrices()
    {
        return $this->hasMany(CommerceArticlePrice::class, 'commerce_sales_context_id');
    }

    public function sales()
    {
        return $this->hasMany(CommerceSale::class, 'commerce_sales_context_id');
    }

    public function articleAvailabilities()
    {
        return $this->hasMany(CommerceArticleAvailability::class, 'commerce_sales_context_id');
    }
}

