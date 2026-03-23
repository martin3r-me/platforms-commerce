<?php

namespace Platform\Commerce\Services;

use Platform\Commerce\Models\CommerceUnit;
use Platform\Commerce\Models\CommerceUnitConversion;
use Platform\Commerce\Models\CommerceArticle;

class UnitConverter
{
    /**
     * Convert a quantity from one unit to another.
     */
    public function convert(float $quantity, int $fromUnitId, int $toUnitId, ?int $teamId = null): array
    {
        if ($fromUnitId === $toUnitId) {
            return ['quantity' => $quantity, 'from_unit_id' => $fromUnitId, 'to_unit_id' => $toUnitId, 'factor' => 1.0];
        }

        // Try direct conversion
        $direct = CommerceUnitConversion::where('from_unit_id', $fromUnitId)
            ->where('to_unit_id', $toUnitId)
            ->first();

        if ($direct) {
            return [
                'quantity' => $quantity * (float)$direct->factor,
                'from_unit_id' => $fromUnitId,
                'to_unit_id' => $toUnitId,
                'factor' => (float)$direct->factor,
            ];
        }

        // Try reverse conversion
        $reverse = CommerceUnitConversion::where('from_unit_id', $toUnitId)
            ->where('to_unit_id', $fromUnitId)
            ->first();

        if ($reverse && (float)$reverse->factor != 0) {
            $factor = 1.0 / (float)$reverse->factor;
            return [
                'quantity' => $quantity * $factor,
                'from_unit_id' => $fromUnitId,
                'to_unit_id' => $toUnitId,
                'factor' => $factor,
            ];
        }

        // Try via base unit
        $fromUnit = CommerceUnit::find($fromUnitId);
        $toUnit = CommerceUnit::find($toUnitId);

        if ($fromUnit && $toUnit && $fromUnit->base_unit_id && $toUnit->base_unit_id
            && $fromUnit->base_unit_id === $toUnit->base_unit_id
            && $fromUnit->factor_to_base && $toUnit->factor_to_base && (float)$toUnit->factor_to_base != 0) {
            $factor = (float)$fromUnit->factor_to_base / (float)$toUnit->factor_to_base;
            return [
                'quantity' => $quantity * $factor,
                'from_unit_id' => $fromUnitId,
                'to_unit_id' => $toUnitId,
                'factor' => $factor,
            ];
        }

        throw new \RuntimeException("No conversion path found from unit {$fromUnitId} to unit {$toUnitId}.");
    }

    /**
     * Get the sales quantity expressed in storage units for an article.
     */
    public function getSalesQuantityInStorageUnit(int $articleId, float $salesQuantity): array
    {
        $article = CommerceArticle::findOrFail($articleId);

        if ($article->sales_to_storage_factor) {
            return [
                'sales_quantity' => $salesQuantity,
                'storage_quantity' => $salesQuantity * (float)$article->sales_to_storage_factor,
                'factor' => (float)$article->sales_to_storage_factor,
                'source' => 'article_factor',
            ];
        }

        if ($article->commerce_sales_unit_id && $article->commerce_storage_unit_id) {
            $result = $this->convert($salesQuantity, $article->commerce_sales_unit_id, $article->commerce_storage_unit_id);
            return [
                'sales_quantity' => $salesQuantity,
                'storage_quantity' => $result['quantity'],
                'factor' => $result['factor'],
                'source' => 'unit_conversion',
            ];
        }

        return [
            'sales_quantity' => $salesQuantity,
            'storage_quantity' => $salesQuantity,
            'factor' => 1.0,
            'source' => 'default',
        ];
    }
}
