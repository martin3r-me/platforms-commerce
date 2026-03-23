<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class CommerceWarehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'commerce_warehouses';

    protected $fillable = [
        'team_id',
        'user_id',
        'name',
        'description',
        'address',
        'city',
        'postal_code',
        'country',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
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

    public function stockLevels()
    {
        return $this->hasMany(CommerceStockLevel::class, 'commerce_warehouse_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(CommerceStockMovement::class, 'commerce_warehouse_id');
    }

    public function stockReservations()
    {
        return $this->hasMany(CommerceStockReservation::class, 'commerce_warehouse_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function team()
    {
        return $this->belongsTo(\App\Models\Team::class, 'team_id');
    }
}
