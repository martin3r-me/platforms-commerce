<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CommerceProductSlot extends Model
{
    use HasFactory;

    protected $table = 'commerce_product_slots';

    protected $fillable = [
        'team_id',
        'user_id',
        'name',
        'description',
        'required',
        'multi_select',
        'order',
        'min_selection',
        'max_selection',
        'active',
    ];

    protected $casts = [
        'required' => 'boolean',
        'multi_select' => 'boolean',
        'active' => 'boolean',
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

    public function products()
    {
        return $this->belongsToMany(CommerceProduct::class, 'commerce_product_commerce_product_slot', 'commerce_product_slot_id', 'commerce_product_id');
    }

    public function dependencies()
    {
        return $this->hasMany(CommerceSlotDependency::class, 'commerce_product_slot_id');
    }

    public function dimensions()
    {
        return $this->hasMany(CommerceProductSlotDimension::class, 'commerce_product_slot_id');
    }

    public function variants()
    {
        return $this->hasMany(CommerceProductSlotVariant::class, 'commerce_product_slot_id');
    }
}

