<?php

namespace Platform\Commerce\Models;

use Illuminate\Database\Eloquent\Model;

class CommerceSupplierFieldMapping extends Model
{
    protected $table = 'commerce_supplier_field_mappings';

    protected $fillable = [
        'commerce_supplier_id',
        'source_key',
        'target_field',
        'label',
        'data_type',
        'transform',
        'position',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'position' => 'integer',
    ];

    public function supplier()
    {
        return $this->belongsTo(CommerceSupplier::class, 'commerce_supplier_id');
    }

    /**
     * Apply the configured transform to a raw value.
     */
    public function applyTransform(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($this->transform) {
            'trim' => is_string($value) ? trim($value) : $value,
            'lowercase' => is_string($value) ? mb_strtolower($value) : $value,
            'uppercase' => is_string($value) ? mb_strtoupper($value) : $value,
            'cast_german_decimal' => self::castGermanDecimal($value),
            'strip_tags' => is_string($value) ? strip_tags($value) : $value,
            'to_integer' => (int) $value,
            'to_boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'url_decode' => is_string($value) ? urldecode($value) : $value,
            default => $value,
        };
    }

    protected static function castGermanDecimal(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $str = (string) $value;
        // Remove thousand separators (dots), replace comma with dot
        $str = str_replace('.', '', $str);
        $str = str_replace(',', '.', $str);

        return is_numeric($str) ? (float) $str : null;
    }
}
