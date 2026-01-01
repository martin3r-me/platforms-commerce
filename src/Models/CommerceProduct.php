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

    public function activities()
    {
        return $this->morphMany(\App\Models\Activity::class, 'activityable')->latest();
    }

    public function hero()
    {
        return $this->morphToMany(\App\Models\Media::class, 'heroable', 'heroables')->withTimestamps();
    }

    public function contextItems()
    {
        return $this->morphMany(\App\Models\ContextItem::class, 'item');
    }

    public function customFields()
    {
        return $this->morphMany(\App\Models\CustomField::class, 'customizable')->orderBy('order');
    }

    public function media()
    {
        return $this->morphToMany(\App\Models\Media::class, 'mediable')
                    ->withTimestamps();
    }

    public function files()
    {
        return $this->morphToMany(\App\Models\Media::class, 'mediable')
            ->whereHas('mimeType', function ($q) {
                return $q->where('mime_type', 'not like', '%image%');
            });
    }

    public function images()
    {
        return $this->morphToMany(\App\Models\Media::class, 'mediable')
            ->whereHas('mimeType', function ($q) {
                return $q->where('mime_type', 'like', '%image%');
            });
    }

    public function apiRequests()
    {
        return $this->morphMany(\App\Models\ApiRequest::class, 'requestable');
    }

    public function toolboxes()
    {
        return $this->morphMany(\App\Models\Toolboxes\ToolboxesToolbox::class, 'toolboxable')->orderBy('order');
    }

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

