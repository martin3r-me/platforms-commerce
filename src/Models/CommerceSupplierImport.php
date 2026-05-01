<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Model;

class CommerceSupplierImport extends Model
{
    protected $table = 'commerce_supplier_imports';

    protected $fillable = [
        'commerce_supplier_id',
        'status',
        'rows_received',
        'rows_created',
        'rows_updated',
        'rows_skipped',
        'error_log',
        'duration_ms',
        'raw_payload',
    ];

    protected $casts = [
        'error_log' => 'array',
        'rows_received' => 'integer',
        'rows_created' => 'integer',
        'rows_updated' => 'integer',
        'rows_skipped' => 'integer',
        'duration_ms' => 'integer',
    ];

    public function supplier()
    {
        return $this->belongsTo(CommerceSupplier::class, 'commerce_supplier_id');
    }
}
