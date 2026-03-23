<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class CommerceArticleAvailability extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'commerce_article_availabilities';

    protected $fillable = [
        'team_id',
        'commerce_article_id',
        'commerce_sales_context_id',
        'is_available',
        'available_from',
        'available_until',
        'max_quantity',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'available_from' => 'datetime',
        'available_until' => 'datetime',
        'max_quantity' => 'decimal:4',
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

    public function article()
    {
        return $this->belongsTo(CommerceArticle::class, 'commerce_article_id');
    }

    public function salesContext()
    {
        return $this->belongsTo(CommerceSalesContext::class, 'commerce_sales_context_id');
    }

    public function team()
    {
        return $this->belongsTo(\App\Models\Team::class, 'team_id');
    }
}
