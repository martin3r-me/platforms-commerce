<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class CommerceAttributeSetItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'commerce_attribute_set_items';

    protected $fillable = [
        'user_id',
        'team_id',
        'commerce_attribute_set_id',
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

    public function attributeSet()
    {
        return $this->belongsTo(CommerceAttributeSet::class, 'commerce_attribute_set_id');
    }

    public function articles()
    {
        return $this->belongsToMany(CommerceArticle::class, 'commerce_article_commerce_attribute_set_item', 'commerce_attribute_set_item_id', 'commerce_article_id');
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

