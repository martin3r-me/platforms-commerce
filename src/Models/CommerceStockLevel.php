<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CommerceStockLevel extends Model
{
    use HasFactory, \Platform\ActivityLog\Traits\LogsActivity;

    protected $table = 'commerce_stock_levels';

    protected $fillable = [
        'team_id',
        'commerce_article_id',
        'commerce_warehouse_id',
        'quantity',
        'reserved_quantity',
        'minimum_quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'reserved_quantity' => 'decimal:4',
        'minimum_quantity' => 'decimal:4',
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

    public function getAvailableQuantityAttribute(): float
    {
        return (float)$this->quantity - (float)$this->reserved_quantity;
    }

    public function article()
    {
        return $this->belongsTo(CommerceArticle::class, 'commerce_article_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(CommerceWarehouse::class, 'commerce_warehouse_id');
    }
}
