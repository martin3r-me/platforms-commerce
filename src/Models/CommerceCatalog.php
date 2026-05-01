<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Platform\Commerce\Enums\CatalogStatus;

class CommerceCatalog extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'commerce_catalogs';

    protected $fillable = [
        'team_id',
        'user_id',
        'name',
        'description',
        'slug',
        'status',
        'valid_from',
        'valid_until',
        'cover_image',
        'metadata',
    ];

    protected $casts = [
        'status' => CatalogStatus::class,
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'metadata' => 'array',
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

    public function sections()
    {
        return $this->hasMany(CommerceCatalogSection::class, 'commerce_catalog_id')->orderBy('sort_order');
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
