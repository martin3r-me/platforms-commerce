<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class CommerceProductBoard extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'commerce_product_boards';

    protected $fillable = [
        'user_id',
        'team_id',
        'name',
        'description',
        'color',
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

    public function productBoardSlots()
    {
        return $this->hasMany(CommerceProductBoardSlot::class, 'commerce_product_board_id')->orderBy('order');
    }

    public function account()
    {
        return $this->belongsTo(\App\Models\Modules\Relations\ModulesRelationsAccount::class, 'modules_relations_account_id');
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

