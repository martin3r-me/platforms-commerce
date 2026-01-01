<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class CommerceArticleCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'commerce_article_categories';

    protected $fillable = [
        'name',
        'description',
        'color',
        'team_id',
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
        return $this->hasMany(CommerceArticle::class, 'category_id');
    }
}

