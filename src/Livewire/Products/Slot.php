<?php

namespace Platform\Commerce\Livewire\Products;

use Livewire\Component;
use Platform\Commerce\Models\CommerceProductSlot;
use Platform\Commerce\Models\CommerceProductSlotDimension;
use Platform\Commerce\Models\CommerceProductSlotDimensionValue;
use Platform\Commerce\Models\CommerceSlotDependency;

class Slot extends Component
{
    public $productSlot;
    public $product;
    public $dimensionValues = [];
    public $newDimensionName = '';
    public $variantArticles = [];
    public $articles;
    public $account;
    public $dependencies = [];
    public $availableVariants = [];
    public $selectedVariant = null;

    public function mount($productSlot)
    {
        foreach ($productSlot->dimensions as $dimension) {
            $this->dimensionValues[$dimension->id] = '';
        }

        foreach ($productSlot->variants as $variant) {
            $this->variantArticles[$variant->id] = $variant->commerce_article_id ?? '';
        }

        // Account und Articles werden später gesetzt, wenn product verfügbar ist
        $this->account = null;
        $this->articles = [];

        $this->productSlot = $productSlot;
        $this->productSlot->required = (bool) $this->productSlot->required;
        $this->productSlot->multi_select = (bool) $this->productSlot->multi_select;
        $this->productSlot->active = (bool) $this->productSlot->active;

        $this->dependencies = $productSlot->dependencies()->with('variant')->get();
        $this->updateAvailableVariants();
    }

    protected function rules()
    {
        return [
            'newDimensionName' => 'required|string|max:255',
            'dimensionValues.*' => 'nullable|string|max:255',
            'variantArticles.*' => 'nullable|integer',
            'productSlot.min_selection' => 'nullable|integer',
            'productSlot.max_selection' => 'nullable|integer',
            'productSlot.name' => 'required|string|max:255',
            'productSlot.description' => 'nullable|string',
            'productSlot.required' => 'required|boolean',
            'productSlot.multi_select' => 'required|boolean',
            'productSlot.active' => 'required|boolean',
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
        $this->productSlot->save();
    }

    public function updateAvailableVariants()
    {
        $assignedVariantIds = $this->dependencies->pluck('commerce_product_slot_variant_id')->toArray();

        $this->availableVariants = $this->product
            ->productSlotVariants()
            ->whereNotIn('id', $assignedVariantIds)
            ->get();
    }

    public function addDimension($slotId)
    {
        $this->validate([
            'newDimensionName' => 'required|string|max:255',
        ]);

        CommerceProductSlotDimension::create([
            'commerce_product_slot_id' => $slotId,
            'name' => $this->newDimensionName,
        ]);

        $this->newDimensionName = '';
        $this->rebuildVariantsMatrix();
        $this->productSlot = $this->productSlot->refresh();
    }

    public function removeDimension($dimensionId)
    {
        $dimension = CommerceProductSlotDimension::findOrFail($dimensionId);

        $this->productSlot->variants()
            ->whereHas('dimensionValues.dimensionValue', function ($query) use ($dimensionId) {
                $query->where('commerce_product_slot_dimension_id', $dimensionId);
            })->delete();

        $dimension->values()->delete();
        $dimension->delete();

        $this->rebuildVariantsMatrix();
        $this->productSlot = $this->productSlot->refresh();
    }

    public function addDimensionValue($dimensionId)
    {
        $this->validate([
            "dimensionValues.$dimensionId" => 'required|string|max:255',
        ]);

        CommerceProductSlotDimensionValue::create([
            'commerce_product_slot_dimension_id' => $dimensionId,
            'value' => $this->dimensionValues[$dimensionId],
        ]);

        $this->dimensionValues[$dimensionId] = '';
        $this->rebuildVariantsMatrix();
        $this->productSlot = $this->productSlot->refresh();
    }

    public function removeDimensionValue($valueId)
    {
        $value = CommerceProductSlotDimensionValue::findOrFail($valueId);

        $this->productSlot->variants()
            ->whereHas('dimensionValues', function ($query) use ($valueId) {
                $query->where('commerce_product_slot_dimension_value_id', $valueId);
            })->delete();

        $value->delete();
        $this->rebuildVariantsMatrix();
        $this->productSlot = $this->productSlot->refresh();
    }

    public function rebuildVariantsMatrix()
    {
        $backup = $this->backupCurrentVariants();
        $dimensions = $this->productSlot->dimensions()->with('values')->get();

        if ($dimensions->isEmpty()) {
            $this->productSlot->variants()->delete();
            return;
        }

        $dimensionValues = $dimensions->map(function ($dimension) {
            return $dimension->values->pluck('id')->toArray();
        });

        $combinations = $this->generateCombinations($dimensionValues->toArray());
        $this->productSlot->variants()->delete();

        foreach ($combinations as $combination) {
            $variant = $this->productSlot->variants()->create();

            foreach ($combination as $valueId) {
                $variant->dimensionValues()->create([
                    'commerce_product_slot_dimension_value_id' => $valueId,
                ]);
            }

            $backupMatch = collect($backup)->firstWhere('combination', $combination);
            if ($backupMatch) {
                $variant->commerce_article_id = $backupMatch['article_id'];
                $variant->save();
            }
        }
    }

    protected function backupCurrentVariants()
    {
        return $this->productSlot->variants()->with('dimensionValues.dimensionValue')->get()->map(function ($variant) {
            $combination = $variant->dimensionValues->map(function ($dimensionValue) {
                return optional($dimensionValue->dimensionValue)->id;
            })->filter()->sort()->values()->toArray();

            return [
                'combination' => $combination,
                'article_id' => $variant->commerce_article_id,
            ];
        });
    }

    protected function generateCombinations(array $arrays)
    {
        $result = [[]];

        foreach ($arrays as $array) {
            $append = [];
            foreach ($result as $product) {
                foreach ($array as $item) {
                    $append[] = array_merge($product, [$item]);
                }
            }
            $result = $append;
        }

        return $result;
    }

    public function addDependency($variantId)
    {
        if ($this->dependencies->contains('commerce_product_slot_variant_id', $variantId)) {
            return;
        }

        CommerceSlotDependency::create([
            'commerce_product_slot_id' => $this->productSlot->id,
            'commerce_product_slot_variant_id' => $variantId,
        ]);

        $this->dependencies = $this->productSlot->dependencies()->with('variant')->get();
        $this->selectedVariant = null;
        $this->updateAvailableVariants();
    }

    public function removeDependency($dependencyId)
    {
        CommerceSlotDependency::find($dependencyId)->delete();
        $this->dependencies = $this->productSlot->dependencies()->with('variant')->get();
        $this->updateAvailableVariants();
    }

    public function render()
    {
        return view('commerce::livewire.products.slot');
    }
}

