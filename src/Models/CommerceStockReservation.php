<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CommerceStockReservation extends Model
{
    use HasFactory;

    protected $table = 'commerce_stock_reservations';

    protected $fillable = [
        'team_id',
        'user_id',
        'commerce_article_id',
        'commerce_warehouse_id',
        'quantity',
        'reference_type',
        'reference_id',
        'expires_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'expires_at' => 'datetime',
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

    public function warehouse()
    {
        return $this->belongsTo(CommerceWarehouse::class, 'commerce_warehouse_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
