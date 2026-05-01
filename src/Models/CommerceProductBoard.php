<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class CommerceProductBoard extends Model
{
    use HasFactory, SoftDeletes, \Platform\ActivityLog\Traits\LogsActivity;

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

    /**
     * Account-Beziehung (optional)
     * 
     * HINWEIS: Diese Beziehung ist optional, da modules_relations_account_id nullable ist.
     * Falls kein Relations-Modul vorhanden ist, wird diese Beziehung nicht verwendet.
     * 
     * Um zu aktivieren, wenn Relations-Modul vorhanden:
     * 
     * public function account()
     * {
     *     return $this->belongsTo(\App\Models\Modules\Relations\ModulesRelationsAccount::class, 'modules_relations_account_id');
     * }
     */

    public function creator()
    {
        return $this->belongsTo(\Platform\Core\Models\User::class, 'user_id');
    }

    public function team()
    {
        return $this->belongsTo(\App\Models\Team::class, 'team_id');
    }
}

