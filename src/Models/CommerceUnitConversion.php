<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CommerceUnitConversion extends Model
{
    use HasFactory;

    protected $table = 'commerce_unit_conversions';

    protected $fillable = [
        'team_id',
        'from_unit_id',
        'to_unit_id',
        'factor',
    ];

    protected $casts = [
        'factor' => 'decimal:8',
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

    public function fromUnit()
    {
        return $this->belongsTo(CommerceUnit::class, 'from_unit_id');
    }

    public function toUnit()
    {
        return $this->belongsTo(CommerceUnit::class, 'to_unit_id');
    }
}
