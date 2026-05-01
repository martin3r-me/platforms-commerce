<?php

namespace Platform\Commerce\Services;

class SupplierDataTypeDetector
{
    /**
     * Detect data type from a single sample value.
     */
    public static function detect(mixed $value): string
    {
        if ($value === null) {
            return 'string';
        }

        if (is_bool($value)) {
            return 'boolean';
        }

        if (is_int($value)) {
            return 'integer';
        }

        if (is_float($value)) {
            return 'decimal';
        }

        if (is_array($value) || is_object($value)) {
            return 'json';
        }

        $str = trim((string) $value);

        if ($str === '') {
            return 'string';
        }

        if (in_array(strtolower($str), ['true', 'false'], true)) {
            return 'boolean';
        }

        if (preg_match('/^-?\d+$/', $str) && strlen($str) <= 18) {
            return 'integer';
        }

        if (is_numeric($str) && str_contains($str, '.')) {
            return 'decimal';
        }

        // German decimal (1.234,56 or 1234,56)
        if (preg_match('/^-?\d{1,3}(?:\.\d{3})*,\d+$/', $str)) {
            return 'german_decimal';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}/', $str)) {
            return 'datetime';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $str)) {
            return 'date';
        }

        if (mb_strlen($str) > 255) {
            return 'text';
        }

        return 'string';
    }

    /**
     * Detect types for all keys in a sample payload.
     */
    public static function detectFromPayload(array $payload): array
    {
        $rows = self::extractRows($payload);
        if (empty($rows)) {
            return [];
        }

        $typesPerKey = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            foreach ($row as $key => $value) {
                if ($value === null || $value === '') {
                    continue;
                }
                $typesPerKey[$key][self::detect($value)] = true;
            }
        }

        $firstRow = is_array($rows[0] ?? null) ? $rows[0] : [];
        $orderedKeys = array_values(array_unique(array_merge(
            array_keys($firstRow),
            array_keys($typesPerKey),
        )));

        $result = [];
        foreach ($orderedKeys as $key) {
            $observed = array_keys($typesPerKey[$key] ?? []);
            $result[$key] = self::reconcileTypes($observed);
        }

        return $result;
    }

    /**
     * Unwrap a payload into a list of row arrays.
     */
    public static function extractRows(array $payload): array
    {
        if (isset($payload[0]) && is_array($payload[0])) {
            return $payload;
        }

        foreach (['data', 'rows', 'items', 'records'] as $wrapper) {
            if (isset($payload[$wrapper]) && is_array($payload[$wrapper])) {
                $inner = $payload[$wrapper];
                if (isset($inner[0]) && is_array($inner[0])) {
                    return $inner;
                }
                return [$inner];
            }
        }

        return [$payload];
    }

    protected static function reconcileTypes(array $types): string
    {
        if (empty($types)) {
            return 'string';
        }
        if (count($types) === 1) {
            return $types[0];
        }

        if (count(array_diff($types, ['integer', 'decimal', 'german_decimal'])) === 0) {
            return in_array('german_decimal', $types, true) ? 'german_decimal' : 'decimal';
        }

        if (count(array_diff($types, ['date', 'datetime'])) === 0) {
            return 'datetime';
        }

        if (count(array_diff($types, ['string', 'text'])) === 0) {
            return 'text';
        }

        return 'string';
    }
}
