<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Pivot-Model für die n:m-Verknüpfung zwischen Artikeln und Lieferanten.
 *
 * Trägt zusätzlich zu article_id/supplier_id einen Einkaufspreis (Make-or-Buy),
 * Validity-Range und einen is_preferred-Flag für den bevorzugten Lieferanten,
 * wenn mehrere Quellen für denselben Artikel existieren.
 */
class CommerceArticleSupplier extends Model
{
    protected $table = 'commerce_article_supplier';

    protected $fillable = [
        'article_id',
        'supplier_id',
        'external_id',
        'purchase_price',
        'purchase_currency',
        'valid_from',
        'valid_until',
        'is_preferred',
        'last_synced_at',
    ];

    protected $casts = [
        'purchase_price'   => 'decimal:4',
        'valid_from'       => 'date',
        'valid_until'      => 'date',
        'is_preferred'     => 'boolean',
        'last_synced_at'   => 'datetime',
    ];

    public function article()
    {
        return $this->belongsTo(CommerceArticle::class, 'article_id');
    }

    public function supplier()
    {
        return $this->belongsTo(CommerceSupplier::class, 'supplier_id');
    }
}
