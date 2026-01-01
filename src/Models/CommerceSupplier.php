<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommerceSupplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'commerce_suppliers';

    protected $fillable = [
        'user_id',
        'team_id',
        'name',
        'description',
    ];

    public function articles()
    {
        return $this->belongsToMany(
            CommerceArticle::class,
            'commerce_article_supplier',
            'supplier_id',
            'article_id'
        )->withTimestamps();
    }

    public function batches()
    {
        return $this->hasMany(CommerceArticleBatch::class, 'commerce_supplier_id');
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

