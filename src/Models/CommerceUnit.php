<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Platform\Commerce\Enums\UnitType;

class CommerceUnit extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'commerce_units';

    protected $fillable = [
        'team_id',
        'user_id',
        'name',
        'symbol',
        'type',
        'is_base_unit',
        'base_unit_id',
        'factor_to_base',
    ];

    protected $casts = [
        'type' => UnitType::class,
        'is_base_unit' => 'boolean',
        'factor_to_base' => 'decimal:8',
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

    public function baseUnit()
    {
        return $this->belongsTo(self::class, 'base_unit_id');
    }

    public function derivedUnits()
    {
        return $this->hasMany(self::class, 'base_unit_id');
    }

    public function conversionsFrom()
    {
        return $this->hasMany(CommerceUnitConversion::class, 'from_unit_id');
    }

    public function conversionsTo()
    {
        return $this->hasMany(CommerceUnitConversion::class, 'to_unit_id');
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
