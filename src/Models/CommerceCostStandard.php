<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Interner Personalkostensatz pro Skill-Level / Rolle.
 *
 * Beispiel: "Senior" mit cost_per_hour=95, cost_per_day=760 (= 95×8). Pro
 * Article wird der Kostensatz × cost_quantity zum internen Einkaufspreis,
 * der für Margen-Berechnungen herangezogen wird.
 */
class CommerceCostStandard extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'commerce_cost_standards';

    protected $fillable = [
        'team_id',
        'user_id',
        'name',
        'description',
        'cost_per_hour',
        'cost_per_day',
        'valid_from',
        'valid_until',
        'is_active',
        'color',
        'sort_order',
    ];

    protected $casts = [
        'cost_per_hour' => 'decimal:4',
        'cost_per_day'  => 'decimal:4',
        'valid_from'    => 'date',
        'valid_until'   => 'date',
        'is_active'     => 'boolean',
    ];

    public function articles()
    {
        return $this->hasMany(CommerceArticle::class, 'cost_standard_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function team()
    {
        return $this->belongsTo(\Platform\Core\Models\Team::class);
    }
}
