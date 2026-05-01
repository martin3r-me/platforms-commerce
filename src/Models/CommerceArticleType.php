<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class CommerceArticleType extends Model
{
    use HasFactory, SoftDeletes, \Platform\ActivityLog\Traits\LogsActivity;

    protected $table = 'commerce_article_types';

    protected $fillable = [
        'name',
        'description',
        'color',
        'active',
        'user_id',
        'team_id',
    ];

    protected $casts = [
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

    public function articles()
    {
        return $this->hasMany(CommerceArticle::class, 'commerce_article_type_id');
    }
}
