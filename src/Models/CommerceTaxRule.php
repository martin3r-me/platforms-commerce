<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CommerceTaxRule extends Model
{
    use HasFactory;

    protected $table = 'commerce_tax_rules';

    protected $fillable = [
        'commerce_sales_context_id',
        'commerce_tax_category_id',
        'tax_rate',
        'valid_from',
        'valid_until',
        'team_id',
        'user_id'
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

    public function taxCategory()
    {
        return $this->belongsTo(CommerceTaxCategory::class, 'commerce_tax_category_id');
    }

    public function salesContext()
    {
        return $this->belongsTo(CommerceSalesContext::class, 'commerce_sales_context_id');
    }
}

