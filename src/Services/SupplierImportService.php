<?php

namespace Platform\Commerce\Services;

use Platform\Commerce\Models\CommerceArticle;
use Platform\Commerce\Models\CommerceSupplier;
use Platform\Commerce\Models\CommerceSupplierImport;

class SupplierImportService
{
    /**
     * Import rows from a payload into CommerceArticle via field mappings.
     */
    public function importFromPayload(CommerceSupplier $supplier, array|string $payload): CommerceSupplierImport
    {
        $start = microtime(true);

        if (is_string($payload)) {
            $payload = json_decode($payload, true);
            if ($payload === null) {
                return CommerceSupplierImport::create([
                    'commerce_supplier_id' => $supplier->id,
                    'status' => 'error',
                    'rows_received' => 0,
                    'error_log' => ['Invalid JSON payload'],
                    'duration_ms' => (int) ((microtime(true) - $start) * 1000),
                ]);
            }
        }

        $rows = SupplierDataTypeDetector::extractRows($payload);
        $mappings = $supplier->fieldMappings()->where('is_active', true)->whereNotNull('target_field')->get();

        if ($mappings->isEmpty()) {
            return CommerceSupplierImport::create([
                'commerce_supplier_id' => $supplier->id,
                'status' => 'error',
                'rows_received' => count($rows),
                'error_log' => ['No active field mappings configured'],
                'duration_ms' => (int) ((microtime(true) - $start) * 1000),
            ]);
        }

        $naturalKey = $supplier->natural_key ?: 'sku';
        $naturalKeyMapping = $mappings->firstWhere('target_field', $naturalKey);

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            if (!is_array($row)) {
                $skipped++;
                $errors[] = "Row {$index}: not an array";
                continue;
            }

            try {
                $mapped = $this->mapRow($row, $mappings);

                if (empty($mapped)) {
                    $skipped++;
                    $errors[] = "Row {$index}: no mappable fields";
                    continue;
                }

                // Determine the natural key value for upsert
                $naturalKeyValue = $mapped[$naturalKey] ?? null;
                if ($naturalKeyValue === null || $naturalKeyValue === '') {
                    $skipped++;
                    $errors[] = "Row {$index}: missing natural key '{$naturalKey}'";
                    continue;
                }

                // Determine the external_id from the source data
                $externalId = $naturalKeyMapping
                    ? ($row[$naturalKeyMapping->source_key] ?? (string) $naturalKeyValue)
                    : (string) $naturalKeyValue;

                // Ensure team_id is set
                $mapped['team_id'] = $supplier->team_id;
                $mapped['user_id'] = $mapped['user_id'] ?? $supplier->user_id;

                $article = CommerceArticle::where('team_id', $supplier->team_id)
                    ->where($naturalKey, $naturalKeyValue)
                    ->first();

                if ($article) {
                    unset($mapped['team_id'], $mapped['user_id']);
                    $article->update($mapped);
                    $updated++;
                } else {
                    $article = CommerceArticle::create($mapped);
                    $created++;
                }

                // Update pivot
                $pivotData = [
                    'external_id' => (string) $externalId,
                    'last_synced_at' => now(),
                ];

                $supplier->articles()->syncWithoutDetaching([
                    $article->id => $pivotData,
                ]);
            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = "Row {$index}: " . $e->getMessage();
            }
        }

        $durationMs = (int) ((microtime(true) - $start) * 1000);

        $supplier->update(['last_import_at' => now()]);

        return CommerceSupplierImport::create([
            'commerce_supplier_id' => $supplier->id,
            'status' => $skipped === count($rows) ? 'error' : ($errors ? 'partial' : 'success'),
            'rows_received' => count($rows),
            'rows_created' => $created,
            'rows_updated' => $updated,
            'rows_skipped' => $skipped,
            'error_log' => $errors ?: null,
            'duration_ms' => $durationMs,
        ]);
    }

    /**
     * Map a single row using field mappings.
     */
    protected function mapRow(array $row, $mappings): array
    {
        $mapped = [];

        foreach ($mappings as $mapping) {
            if (!array_key_exists($mapping->source_key, $row)) {
                continue;
            }

            $value = $row[$mapping->source_key];
            $value = $mapping->applyTransform($value);
            $mapped[$mapping->target_field] = $value;
        }

        return $mapped;
    }
}
