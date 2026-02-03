<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\Uid\UuidV7;
use Illuminate\Support\Facades\Auth;

class CommerceProduct extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'commerce_products';

    protected $fillable = [
        'user_id',
        'team_id',
        'uuid',
        'name',
        'description',
        'price',
        'commerce_product_board_slot_id',
        'price_deviation_type',
        'price_deviation_value',
        'order',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function (self $model) {
            if (!$model->uuid) {
                do {
                    $uuid = UuidV7::generate();
                } while (self::where('uuid', $uuid)->exists());
                $model->uuid = $uuid;
            }
            if (!$model->user_id && Auth::check()) {
                $model->user_id = Auth::id();
            }
            if (!$model->team_id && Auth::user()) {
                $model->team_id = Auth::user()->currentTeam->id ?? null;
            }
        });
    }

    /**
     * HINWEIS: Activity-, Media- und Account-Beziehungen wurden entfernt.
     * 
     * Später können hier Beziehungen zu:
     * - Brands (Marken)
     * - CRM Contacts (Kontakte)
     * hinzugefügt werden.
     */

    public function slot()
    {
        return $this->belongsTo(CommerceProductBoardSlot::class, 'commerce_product_board_slot_id');
    }

    public function productSlots()
    {
        return $this->belongsToMany(CommerceProductSlot::class, 'commerce_product_commerce_product_slot', 'commerce_product_id', 'commerce_product_slot_id');
    }

    public function productSlotVariants()
    {
        return CommerceProductSlotVariant::whereHas('slot', function ($query) {
            $query->whereHas('products', function ($query) {
                $query->where('commerce_products.id', $this->id);
            });
        })->get();
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

