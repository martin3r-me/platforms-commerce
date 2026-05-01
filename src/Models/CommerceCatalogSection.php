<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class CommerceCatalogSection extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'commerce_catalog_sections';

    protected $fillable = [
        'team_id',
        'user_id',
        'commerce_catalog_id',
        'name',
        'description',
        'sort_order',
        'color',
        'icon',
    ];

    protected $casts = [
        'sort_order' => 'integer',
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

    public function catalog()
    {
        return $this->belongsTo(CommerceCatalog::class, 'commerce_catalog_id');
    }

    public function productBoards()
    {
        return $this->belongsToMany(
            CommerceProductBoard::class,
            'commerce_catalog_section_boards',
            'commerce_catalog_section_id',
            'commerce_product_board_id'
        )->withPivot('sort_order')->withTimestamps()->orderByPivot('sort_order');
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
