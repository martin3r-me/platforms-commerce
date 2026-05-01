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
        'parent_id',
        'sort_order',
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

    public function articles()
    {
        return $this->hasMany(CommerceArticle::class, 'category_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Recursive children (all descendants).
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get the full path (breadcrumb) for this category.
     */
    public function getPathAttribute(): string
    {
        $parts = [$this->name];
        $current = $this;

        while ($current->parent) {
            $current = $current->parent;
            array_unshift($parts, $current->name);
        }

        return implode(' > ', $parts);
    }

    /**
     * Check if this category is a root category (no parent).
     */
    public function getIsRootAttribute(): bool
    {
        return $this->parent_id === null;
    }

    /**
     * Get depth level (0 = root).
     */
    public function getDepthAttribute(): int
    {
        $depth = 0;
        $current = $this;

        while ($current->parent_id !== null) {
            $depth++;
            $current = $current->parent;
        }

        return $depth;
    }
}
