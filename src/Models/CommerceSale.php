<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class CommerceSale extends Model
{
    use HasFactory, SoftDeletes, \Platform\ActivityLog\Traits\LogsActivity;

    protected $table = 'commerce_sales';

    protected $fillable = [
        'user_id',
        'team_id',
        'commerce_sales_context_id',
        'total_amount',
        'paid_at',
        'status',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'status' => \Platform\Commerce\Enums\SaleStatus::class,
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

    public function creator()
    {
        return $this->belongsTo(\Platform\Core\Models\User::class, 'user_id');
    }

    public function team()
    {
        return $this->belongsTo(\App\Models\Team::class, 'team_id');
    }

    public function items()
    {
        return $this->hasMany(CommerceSaleItem::class, 'commerce_sale_id');
    }

    public function salesContext()
    {
        return $this->belongsTo(CommerceSalesContext::class, 'commerce_sales_context_id');
    }
}

