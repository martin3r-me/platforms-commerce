<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class CommerceAttributeSet extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'commerce_attribute_sets';

    protected $fillable = [
        'user_id',
        'team_id',
        'name',
        'color',
        'is_required',
        'is_multiselect',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_multiselect' => 'boolean',
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

    public function attributeSetItems()
    {
        return $this->hasMany(CommerceAttributeSetItem::class, 'commerce_attribute_set_id')->orderBy('name');
    }

    public function articles()
    {
        return $this->belongsToMany(CommerceArticle::class, 'commerce_article_commerce_attribute_set', 'commerce_attribute_set_id', 'commerce_article_id');
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

