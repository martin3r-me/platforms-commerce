<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\Uid\UuidV7;
use Illuminate\Support\Facades\Auth;

class CommerceProductBoardSlot extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'commerce_product_board_slots';

    protected $fillable = [
        'user_id',
        'team_id',
        'commerce_product_board_id',
        'uuid',
        'name',
        'order',
        'description',
        'color',
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
            if (!$model->team_id && Auth::check()) {
                $model->team_id = Auth::user()->currentTeam->id ?? null;
            }
        });
    }

    public function board()
    {
        return $this->belongsTo(CommerceProductBoard::class, 'commerce_product_board_id');
    }

    public function products()
    {
        return $this->hasMany(CommerceProduct::class, 'commerce_product_board_slot_id')->orderBy('order');
    }

    public function board()
    {
        return $this->belongsTo(CommerceProductBoard::class, 'commerce_product_board_id');
    }

    public function apiRequests()
    {
        return $this->morphMany(\App\Models\ApiRequest::class, 'requestable');
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

