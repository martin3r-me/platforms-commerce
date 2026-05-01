<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Platform\Commerce\Enums\RuleType;

class CommerceProductRule extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'commerce_product_rules';

    protected $fillable = [
        'team_id',
        'user_id',
        'name',
        'rule_type',
        'is_active',
        'priority',
        'conditions',
        'actions',
        'applies_to_type',
        'applies_to_id',
        'valid_from',
        'valid_until',
        'commerce_product_id',
        'max_quantity_per_order',
        'min_order_value',
        'sale_period_start',
        'sale_period_end',
    ];

    protected $casts = [
        'rule_type' => RuleType::class,
        'is_active' => 'boolean',
        'conditions' => 'array',
        'actions' => 'array',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'sale_period_start' => 'datetime',
        'sale_period_end' => 'datetime',
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

    public function product()
    {
        return $this->belongsTo(CommerceProduct::class, 'commerce_product_id');
    }

    public function team()
    {
        return $this->belongsTo(\App\Models\Team::class, 'team_id');
    }

    public function creator()
    {
        return $this->belongsTo(\Platform\Core\Models\User::class, 'user_id');
    }
}

