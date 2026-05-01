<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommerceArticleBatch extends Model
{
    use HasFactory, SoftDeletes, \Platform\ActivityLog\Traits\LogsActivity;

    protected $table = 'commerce_article_batches';

    protected $fillable = [
        'commerce_article_id',
        'commerce_supplier_id',
        'commerce_warehouse_id',
        'status',
        'batch_number',
        'quantity',
        'remaining_quantity',
        'storage_location',
        'manufacture_date',
        'expiry_date',
    ];

    protected $casts = [
        'manufacture_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function article()
    {
        return $this->belongsTo(CommerceArticle::class, 'commerce_article_id');
    }

    public function supplier()
    {
        return $this->belongsTo(CommerceSupplier::class, 'commerce_supplier_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(CommerceWarehouse::class, 'commerce_warehouse_id');
    }
}

