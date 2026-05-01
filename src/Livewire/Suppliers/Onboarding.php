<?php

namespace Platform\Commerce\Livewire\Suppliers;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Commerce\Models\CommerceSupplier;
use Platform\Commerce\Models\CommerceSupplierFieldMapping;
use Platform\Commerce\Models\CommerceSupplierImport;
use Platform\Commerce\Enums\SupplierStatus;
use Platform\Commerce\Services\SupplierDataTypeDetector;
use Platform\Commerce\Services\SupplierImportService;

class Onboarding extends Component
{
    public CommerceSupplier $commerceSupplier;

    public array $fields = [];
    public string $naturalKey = 'sku';
    public bool $activating = false;

    // Delete state
    public bool $showDeleteModal = false;

    /**
     * Mappable article fields for the target dropdown.
     */
    public const TARGET_FIELDS = [
        'name' => 'Name',
        'description' => 'Beschreibung',
        'short_description' => 'Kurzbeschreibung',
        'sku' => 'SKU',
        'gtin' => 'GTIN',
        'ean' => 'EAN',
        'upc' => 'UPC',
        'isbn' => 'ISBN',
        'price' => 'Preis',
        'weight' => 'Gewicht',
        'width' => 'Breite',
        'height' => 'Höhe',
        'depth' => 'Tiefe',
        'volume' => 'Volumen',
        'color' => 'Farbe',
        'status' => 'Status',
        'tax_class' => 'Steuerklasse',
        'is_available' => 'Verfügbar',
        'stock_level' => 'Bestand',
        'manufacturer_part_number' => 'Herstellernummer',
        'country_of_origin' => 'Herkunftsland',
        'lead_time_days' => 'Lieferzeit (Tage)',
        'base_price_quantity' => 'Grundpreis-Menge',
        'base_price_unit' => 'Grundpreis-Einheit',
        'procurement_type' => 'Beschaffungstyp',
    ];

    /**
     * Auto-suggest mapping from common source key patterns to target fields.
     */
    protected const AUTO_SUGGEST = [
        'name' => 'name',
        'bezeichnung' => 'name',
        'artikelbezeichnung' => 'name',
        'article_name' => 'name',
        'title' => 'name',
        'titel' => 'name',
        'description' => 'description',
        'beschreibung' => 'description',
        'sku' => 'sku',
        'artikelnummer' => 'sku',
        'article_number' => 'sku',
        'ean' => 'ean',
        'gtin' => 'gtin',
        'upc' => 'upc',
        'isbn' => 'isbn',
        'price' => 'price',
        'preis' => 'price',
        'vk' => 'price',
        'vk_preis' => 'price',
        'weight' => 'weight',
        'gewicht' => 'weight',
        'color' => 'color',
        'farbe' => 'color',
        'status' => 'status',
        'width' => 'width',
        'breite' => 'width',
        'height' => 'height',
        'hoehe' => 'height',
        'höhe' => 'height',
        'depth' => 'depth',
        'tiefe' => 'depth',
        'volume' => 'volume',
        'volumen' => 'volume',
    ];

    public function mount(CommerceSupplier $commerceSupplier): void
    {
        $user = Auth::user();
        abort_unless($commerceSupplier->team_id === $user->currentTeam->id, 403);
        abort_unless($commerceSupplier->isOnboarding(), 404);

        $this->commerceSupplier = $commerceSupplier;
        $this->naturalKey = $commerceSupplier->natural_key ?: 'sku';
        $this->buildFieldsFromSample();
    }

    public function openDeleteModal(): void
    {
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
    }

    public function deleteSupplier(): void
    {
        $this->commerceSupplier->fieldMappings()->delete();
        $this->commerceSupplier->imports()->delete();
        $this->commerceSupplier->delete();

        $this->redirect(route('commerce.suppliers.index'));
    }

    public function buildFieldsFromSample(): void
    {
        $sample = $this->commerceSupplier->sample_payload;

        if (!$sample) {
            $this->fields = [];
            return;
        }

        $detectedTypes = SupplierDataTypeDetector::detectFromPayload($sample);

        $this->fields = [];
        $position = 0;
        foreach ($detectedTypes as $key => $type) {
            $transform = '';
            if ($type === 'german_decimal') {
                $type = 'decimal';
                $transform = 'cast_german_decimal';
            }

            $suggestedTarget = $this->autoSuggestTarget($key);

            $this->fields[] = [
                'source_key' => $key,
                'target_field' => $suggestedTarget,
                'label' => $this->humanizeKey($key),
                'data_type' => $type,
                'transform' => $transform,
                'selected' => true,
                'position' => $position++,
            ];
        }
    }

    public function refreshSample(): void
    {
        $this->commerceSupplier->refresh();
        $this->buildFieldsFromSample();
    }

    public function activate(): void
    {
        $this->activating = true;

        $selectedFields = collect($this->fields)->where('selected', true);

        if ($selectedFields->isEmpty()) {
            $this->addError('fields', 'Mindestens ein Feld muss ausgewählt sein.');
            $this->activating = false;
            return;
        }

        // At least one field must have a target_field
        $mappedFields = $selectedFields->filter(fn ($f) => !empty($f['target_field']));
        if ($mappedFields->isEmpty()) {
            $this->addError('fields', 'Mindestens ein Feld muss einem Zielfeld zugeordnet sein.');
            $this->activating = false;
            return;
        }

        // Natural key must be among the mapped target fields
        if (!$mappedFields->contains(fn ($f) => $f['target_field'] === $this->naturalKey)) {
            $this->addError('naturalKey', 'Das Natural Key Feld muss unter den gemappten Zielfeldern vorhanden sein.');
            $this->activating = false;
            return;
        }

        // Save natural key to supplier
        $this->commerceSupplier->update(['natural_key' => $this->naturalKey]);

        // Create field mappings
        foreach ($selectedFields as $field) {
            CommerceSupplierFieldMapping::create([
                'commerce_supplier_id' => $this->commerceSupplier->id,
                'source_key' => $field['source_key'],
                'target_field' => $field['target_field'] ?: null,
                'label' => $field['label'],
                'data_type' => $field['data_type'],
                'transform' => !empty($field['transform']) ? $field['transform'] : null,
                'position' => $field['position'],
                'is_active' => true,
            ]);
        }

        // Set status to active
        $this->commerceSupplier->update(['status' => SupplierStatus::Active]);

        // Import pending payloads
        $this->importPendingPayloads();

        $this->redirect(route('commerce.suppliers.show', $this->commerceSupplier));
    }

    protected function importPendingPayloads(): void
    {
        $pendingImports = CommerceSupplierImport::where('commerce_supplier_id', $this->commerceSupplier->id)
            ->where('status', 'pending')
            ->whereNotNull('raw_payload')
            ->get();

        $importService = app(SupplierImportService::class);

        foreach ($pendingImports as $pending) {
            $payload = json_decode($pending->raw_payload, true);
            if ($payload) {
                $importService->importFromPayload($this->commerceSupplier, $payload);
            }
            $pending->update(['status' => 'processing']);
        }
    }

    protected function autoSuggestTarget(string $sourceKey): ?string
    {
        $normalized = mb_strtolower(trim($sourceKey));
        $normalized = str_replace(['-', ' ', '.'], '_', $normalized);

        return self::AUTO_SUGGEST[$normalized] ?? null;
    }

    protected function humanizeKey(string $key): string
    {
        return \Illuminate\Support\Str::of($key)
            ->replace(['_', '-', '.'], ' ')
            ->title()
            ->toString();
    }

    public function getWebhookUrlProperty(): string
    {
        return url('/api/commerce/suppliers/ingest/' . $this->commerceSupplier->endpoint_token);
    }

    public function getHasSampleProperty(): bool
    {
        return !empty($this->commerceSupplier->sample_payload);
    }

    public function getSampleRowProperty(): ?array
    {
        $sample = $this->commerceSupplier->sample_payload;
        if (!$sample) {
            return null;
        }

        if (isset($sample[0]) && is_array($sample[0])) {
            return $sample[0];
        }

        foreach (['data', 'rows', 'items', 'records'] as $wrapper) {
            if (isset($sample[$wrapper]) && is_array($sample[$wrapper])) {
                $inner = $sample[$wrapper];
                return (isset($inner[0]) && is_array($inner[0])) ? $inner[0] : $inner;
            }
        }

        return $sample;
    }

    public function render()
    {
        return view('commerce::livewire.suppliers.onboarding')
            ->layout('platform::layouts.app');
    }
}
