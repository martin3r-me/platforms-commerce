<?php

namespace Platform\Commerce\Livewire\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Commerce\Models\CommerceTaxCategory;
use Platform\Commerce\Models\CommerceSalesContext;
use Platform\Commerce\Models\CommerceTaxRule;
use Platform\Commerce\Models\CommerceArticleType;
use Platform\Commerce\Models\CommerceArticleCategory;
use Platform\Commerce\Models\CommerceUnit;
use Platform\Commerce\Models\CommerceUnitConversion;
use Platform\Commerce\Models\CommerceCostStandard;
use Platform\Commerce\Services\TaxRuleManager;

class Index extends Component
{
    // Tax Category fields
    public $name;
    public $default_rate;

    // Sales Context fields
    public $context_name;
    public $context_description;

    // Article Type fields
    public $type_name;
    public $type_description;
    public $type_color;

    // Article Category fields
    public $cat_name;
    public $cat_description;
    public $cat_color;
    public $cat_parent_id;

    // Edit fields
    public $editCategoryId;
    public $editCategoryName;
    public $editCategoryRate;

    public $editContextId;
    public $editContextName;
    public $editContextDescription;

    public $editTypeId;
    public $editTypeName;
    public $editTypeDescription;
    public $editTypeColor;

    public $editCatId;
    public $editCatName;
    public $editCatDescription;
    public $editCatColor;
    public $editCatParentId;

    // Unit fields
    public $unit_name;
    public $unit_symbol;
    public $unit_type = 'piece';

    public $editUnitId;
    public $editUnitName;
    public $editUnitSymbol;
    public $editUnitType;

    // Unit Conversion fields
    public $conv_from_unit_id;
    public $conv_to_unit_id;
    public $conv_factor;

    // Cost Standard fields
    public $cs_name;
    public $cs_cost_per_hour;
    public $cs_cost_per_day;
    public $cs_color;

    public $editCsId;
    public $editCsName;
    public $editCsCostPerHour;
    public $editCsCostPerDay;
    public $editCsColor;
    public $editCsActive = true;

    // Data
    public $matrix = [];

    public function mount()
    {
        $this->loadMatrix();
    }

    protected function loadMatrix()
    {
        $teamId = Auth::user()->currentTeam->id;
        $this->matrix = CommerceTaxRule::with(['taxCategory', 'salesContext'])
            ->where('team_id', $teamId)
            ->orderBy('commerce_sales_context_id')
            ->get();
    }

    protected function getTeamCategories()
    {
        return CommerceTaxCategory::where('team_id', Auth::user()->currentTeam->id)
            ->orderBy('name')
            ->get();
    }

    protected function getTeamContexts()
    {
        return CommerceSalesContext::where('team_id', Auth::user()->currentTeam->id)
            ->orderBy('name')
            ->get();
    }

    protected function getTeamArticleTypes()
    {
        return CommerceArticleType::where('team_id', Auth::user()->currentTeam->id)
            ->withCount('articles')
            ->orderBy('name')
            ->get();
    }

    protected function getTeamArticleCategories()
    {
        return CommerceArticleCategory::where('team_id', Auth::user()->currentTeam->id)
            ->whereNull('parent_id')
            ->with('descendants')
            ->withCount('articles')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    protected function getAllArticleCategories()
    {
        $categories = CommerceArticleCategory::where('team_id', Auth::user()->currentTeam->id)
            ->with('parent')
            ->orderBy('name')
            ->get();

        // Append path for display in dropdowns
        return $categories->map(function ($cat) {
            $cat->display_name = $cat->path;
            return $cat;
        })->sortBy('display_name')->values();
    }

    // ── Tax Category CRUD ──

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'default_rate' => 'required|numeric|min:0|max:100',
        ]);

        CommerceTaxCategory::create([
            'name' => $this->name,
            'default_rate' => $this->default_rate,
            'valid_from' => now(),
            'user_id' => Auth::user()->id,
            'team_id' => Auth::user()->currentTeam->id,
        ]);

        (new TaxRuleManager())->updateTaxRules();
        $this->loadMatrix();
        $this->reset(['name', 'default_rate']);
        $this->dispatch('notify', type: 'success', message: 'Steuerkategorie wurde angelegt.');
    }

    public function editCategory($id)
    {
        $category = CommerceTaxCategory::find($id);
        if ($category) {
            $this->editCategoryId = $category->id;
            $this->editCategoryName = $category->name;
            $this->editCategoryRate = $category->default_rate;
            $this->dispatch('open-edit-category-modal');
        }
    }

    public function updateCategory()
    {
        $this->validate([
            'editCategoryName' => 'required|string|max:255',
            'editCategoryRate' => 'required|numeric|min:0|max:100',
        ]);

        $category = CommerceTaxCategory::find($this->editCategoryId);
        if ($category) {
            $category->update([
                'name' => $this->editCategoryName,
                'default_rate' => $this->editCategoryRate,
            ]);
            $this->loadMatrix();
            $this->dispatch('notify', type: 'success', message: 'Steuerkategorie wurde aktualisiert.');
        }

        $this->reset(['editCategoryId', 'editCategoryName', 'editCategoryRate']);
    }

    public function deleteCategory($id)
    {
        $category = CommerceTaxCategory::find($id);
        if ($category) {
            // Delete related tax rules first
            CommerceTaxRule::where('commerce_tax_category_id', $id)->delete();
            $category->delete();
            $this->loadMatrix();
            $this->dispatch('notify', type: 'success', message: 'Steuerkategorie wurde gelöscht.');
        }
    }

    // ── Sales Context CRUD ──

    public function saveSalesContext()
    {
        $this->validate([
            'context_name' => 'required|string|max:255',
            'context_description' => 'nullable|string|max:1000',
        ]);

        CommerceSalesContext::create([
            'name' => $this->context_name,
            'description' => $this->context_description,
            'valid_from' => now(),
            'user_id' => Auth::user()->id,
            'team_id' => Auth::user()->currentTeam->id,
        ]);

        (new TaxRuleManager())->updateTaxRules();
        $this->loadMatrix();
        $this->reset(['context_name', 'context_description']);
        $this->dispatch('notify', type: 'success', message: 'Verkaufskontext wurde angelegt.');
    }

    public function editContext($id)
    {
        $context = CommerceSalesContext::find($id);
        if ($context) {
            $this->editContextId = $context->id;
            $this->editContextName = $context->name;
            $this->editContextDescription = $context->description;
            $this->dispatch('open-edit-context-modal');
        }
    }

    public function updateContext()
    {
        $this->validate([
            'editContextName' => 'required|string|max:255',
            'editContextDescription' => 'nullable|string|max:1000',
        ]);

        $context = CommerceSalesContext::find($this->editContextId);
        if ($context) {
            $context->update([
                'name' => $this->editContextName,
                'description' => $this->editContextDescription,
            ]);
            $this->loadMatrix();
            $this->dispatch('notify', type: 'success', message: 'Verkaufskontext wurde aktualisiert.');
        }

        $this->reset(['editContextId', 'editContextName', 'editContextDescription']);
    }

    public function deleteContext($id)
    {
        $context = CommerceSalesContext::find($id);
        if ($context) {
            CommerceTaxRule::where('commerce_sales_context_id', $id)->delete();
            $context->delete();
            $this->loadMatrix();
            $this->dispatch('notify', type: 'success', message: 'Verkaufskontext wurde gelöscht.');
        }
    }

    // ── Article Type CRUD ──

    public function saveArticleType()
    {
        $this->validate([
            'type_name' => 'required|string|max:255',
            'type_description' => 'nullable|string|max:1000',
            'type_color' => 'nullable|string|max:7',
        ]);

        CommerceArticleType::create([
            'name' => $this->type_name,
            'description' => $this->type_description,
            'color' => $this->type_color,
            'user_id' => Auth::user()->id,
            'team_id' => Auth::user()->currentTeam->id,
        ]);

        $this->reset(['type_name', 'type_description', 'type_color']);
        $this->dispatch('notify', type: 'success', message: 'Artikel-Typ wurde angelegt.');
    }

    public function editArticleType($id)
    {
        $type = CommerceArticleType::find($id);
        if ($type) {
            $this->editTypeId = $type->id;
            $this->editTypeName = $type->name;
            $this->editTypeDescription = $type->description;
            $this->editTypeColor = $type->color;
            $this->dispatch('open-edit-type-modal');
        }
    }

    public function updateArticleType()
    {
        $this->validate([
            'editTypeName' => 'required|string|max:255',
            'editTypeDescription' => 'nullable|string|max:1000',
            'editTypeColor' => 'nullable|string|max:7',
        ]);

        $type = CommerceArticleType::find($this->editTypeId);
        if ($type) {
            $type->update([
                'name' => $this->editTypeName,
                'description' => $this->editTypeDescription,
                'color' => $this->editTypeColor,
            ]);
            $this->dispatch('notify', type: 'success', message: 'Artikel-Typ wurde aktualisiert.');
        }

        $this->reset(['editTypeId', 'editTypeName', 'editTypeDescription', 'editTypeColor']);
    }

    public function deleteArticleType($id)
    {
        $type = CommerceArticleType::find($id);
        if ($type) {
            $type->delete();
            $this->dispatch('notify', type: 'success', message: 'Artikel-Typ wurde gelöscht.');
        }
    }

    // ── Article Category CRUD ──

    public function saveArticleCategory()
    {
        $this->validate([
            'cat_name' => 'required|string|max:255',
            'cat_description' => 'nullable|string|max:1000',
            'cat_color' => 'nullable|string|max:7',
            'cat_parent_id' => 'nullable|integer|exists:commerce_article_categories,id',
        ]);

        CommerceArticleCategory::create([
            'name' => $this->cat_name,
            'description' => $this->cat_description,
            'color' => $this->cat_color,
            'parent_id' => $this->cat_parent_id ?: null,
            'team_id' => Auth::user()->currentTeam->id,
        ]);

        $this->reset(['cat_name', 'cat_description', 'cat_color', 'cat_parent_id']);
        $this->dispatch('notify', type: 'success', message: 'Artikel-Kategorie wurde angelegt.');
    }

    public function editArticleCategory($id)
    {
        $cat = CommerceArticleCategory::find($id);
        if ($cat) {
            $this->editCatId = $cat->id;
            $this->editCatName = $cat->name;
            $this->editCatDescription = $cat->description;
            $this->editCatColor = $cat->color;
            $this->editCatParentId = $cat->parent_id;
            $this->dispatch('open-edit-article-category-modal');
        }
    }

    public function updateArticleCategory()
    {
        $this->validate([
            'editCatName' => 'required|string|max:255',
            'editCatDescription' => 'nullable|string|max:1000',
            'editCatColor' => 'nullable|string|max:7',
            'editCatParentId' => 'nullable|integer|exists:commerce_article_categories,id',
        ]);

        $cat = CommerceArticleCategory::find($this->editCatId);
        if ($cat) {
            // Prevent setting self as parent
            $parentId = $this->editCatParentId ?: null;
            if ($parentId == $cat->id) {
                $parentId = null;
            }

            $cat->update([
                'name' => $this->editCatName,
                'description' => $this->editCatDescription,
                'color' => $this->editCatColor,
                'parent_id' => $parentId,
            ]);
            $this->dispatch('notify', type: 'success', message: 'Artikel-Kategorie wurde aktualisiert.');
        }

        $this->reset(['editCatId', 'editCatName', 'editCatDescription', 'editCatColor', 'editCatParentId']);
    }

    public function deleteArticleCategory($id)
    {
        $cat = CommerceArticleCategory::find($id);
        if ($cat) {
            // Move children to parent (or root)
            CommerceArticleCategory::where('parent_id', $id)->update(['parent_id' => $cat->parent_id]);
            $cat->delete();
            $this->dispatch('notify', type: 'success', message: 'Artikel-Kategorie wurde gelöscht. Unterkategorien wurden nach oben verschoben.');
        }
    }

    // ── Unit CRUD ──

    protected function getTeamUnits()
    {
        return CommerceUnit::where('team_id', Auth::user()->currentTeam->id)
            ->orderBy('type')
            ->orderBy('name')
            ->get();
    }

    protected function getTeamUnitConversions()
    {
        return CommerceUnitConversion::where('team_id', Auth::user()->currentTeam->id)
            ->with(['fromUnit', 'toUnit'])
            ->get();
    }

    public function saveUnit()
    {
        $this->validate([
            'unit_name' => 'required|string|max:255',
            'unit_symbol' => 'required|string|max:20',
            'unit_type' => 'required|string|in:time,piece,weight,volume,length,area,custom',
        ]);

        CommerceUnit::create([
            'name' => $this->unit_name,
            'symbol' => $this->unit_symbol,
            'type' => $this->unit_type,
            'user_id' => Auth::user()->id,
            'team_id' => Auth::user()->currentTeam->id,
        ]);

        $this->reset(['unit_name', 'unit_symbol']);
        $this->unit_type = 'piece';
        $this->dispatch('notify', type: 'success', message: 'Einheit wurde angelegt.');
    }

    public function editUnit($id)
    {
        $unit = CommerceUnit::find($id);
        if ($unit) {
            $this->editUnitId = $unit->id;
            $this->editUnitName = $unit->name;
            $this->editUnitSymbol = $unit->symbol;
            $this->editUnitType = $unit->type;
            $this->dispatch('open-edit-unit-modal');
        }
    }

    public function updateUnit()
    {
        $this->validate([
            'editUnitName' => 'required|string|max:255',
            'editUnitSymbol' => 'required|string|max:20',
            'editUnitType' => 'required|string|in:time,piece,weight,volume,length,area,custom',
        ]);

        $unit = CommerceUnit::find($this->editUnitId);
        if ($unit) {
            $unit->update([
                'name' => $this->editUnitName,
                'symbol' => $this->editUnitSymbol,
                'type' => $this->editUnitType,
            ]);
            $this->dispatch('notify', type: 'success', message: 'Einheit wurde aktualisiert.');
        }

        $this->reset(['editUnitId', 'editUnitName', 'editUnitSymbol', 'editUnitType']);
    }

    public function deleteUnit($id)
    {
        $unit = CommerceUnit::find($id);
        if ($unit) {
            CommerceUnitConversion::where('from_unit_id', $id)
                ->orWhere('to_unit_id', $id)
                ->delete();
            $unit->delete();
            $this->dispatch('notify', type: 'success', message: 'Einheit wurde gelöscht.');
        }
    }

    // ── Unit Conversion CRUD ──

    public function saveUnitConversion()
    {
        $this->validate([
            'conv_from_unit_id' => 'required|integer|exists:commerce_units,id',
            'conv_to_unit_id'   => 'required|integer|exists:commerce_units,id|different:conv_from_unit_id',
            'conv_factor'       => 'required|numeric|gt:0',
        ]);

        CommerceUnitConversion::create([
            'from_unit_id' => (int) $this->conv_from_unit_id,
            'to_unit_id'   => (int) $this->conv_to_unit_id,
            'factor'       => $this->conv_factor,
            'team_id'      => Auth::user()->currentTeam->id,
        ]);

        $this->reset(['conv_from_unit_id', 'conv_to_unit_id', 'conv_factor']);
        $this->dispatch('notify', type: 'success', message: 'Einheiten-Umrechnung wurde angelegt.');
    }

    public function deleteUnitConversion($id)
    {
        $conversion = CommerceUnitConversion::find($id);
        if ($conversion) {
            $conversion->delete();
            $this->dispatch('notify', type: 'success', message: 'Einheiten-Umrechnung wurde gelöscht.');
        }
    }

    // ── Cost Standard CRUD ──

    protected function getTeamCostStandards()
    {
        return CommerceCostStandard::where('team_id', Auth::user()->currentTeam->id)
            ->orderBy('sort_order')->orderBy('name')->get();
    }

    public function saveCostStandard()
    {
        $this->validate([
            'cs_name' => 'required|string|max:255',
            'cs_cost_per_hour' => 'nullable|numeric|min:0',
            'cs_cost_per_day' => 'nullable|numeric|min:0',
            'cs_color' => 'nullable|string|max:20',
        ]);

        CommerceCostStandard::create([
            'name' => $this->cs_name,
            'cost_per_hour' => $this->cs_cost_per_hour,
            'cost_per_day' => $this->cs_cost_per_day ?: (($this->cs_cost_per_hour) ? (float) $this->cs_cost_per_hour * 8 : null),
            'color' => $this->cs_color,
            'is_active' => true,
            'valid_from' => now(),
            'user_id' => Auth::user()->id,
            'team_id' => Auth::user()->currentTeam->id,
        ]);

        $this->reset(['cs_name', 'cs_cost_per_hour', 'cs_cost_per_day', 'cs_color']);
        $this->dispatch('notify', type: 'success', message: 'Kostensatz wurde angelegt.');
    }

    public function editCostStandard($id)
    {
        $cs = CommerceCostStandard::find($id);
        if ($cs) {
            $this->editCsId = $cs->id;
            $this->editCsName = $cs->name;
            $this->editCsCostPerHour = $cs->cost_per_hour;
            $this->editCsCostPerDay = $cs->cost_per_day;
            $this->editCsColor = $cs->color;
            $this->editCsActive = (bool) $cs->is_active;
            $this->dispatch('open-edit-cost-standard-modal');
        }
    }

    public function updateCostStandard()
    {
        $this->validate([
            'editCsName' => 'required|string|max:255',
            'editCsCostPerHour' => 'nullable|numeric|min:0',
            'editCsCostPerDay' => 'nullable|numeric|min:0',
        ]);

        $cs = CommerceCostStandard::find($this->editCsId);
        if ($cs) {
            $cs->update([
                'name' => $this->editCsName,
                'cost_per_hour' => $this->editCsCostPerHour,
                'cost_per_day' => $this->editCsCostPerDay,
                'color' => $this->editCsColor,
                'is_active' => (bool) $this->editCsActive,
            ]);
            $this->dispatch('notify', type: 'success', message: 'Kostensatz wurde aktualisiert.');
        }

        $this->reset(['editCsId', 'editCsName', 'editCsCostPerHour', 'editCsCostPerDay', 'editCsColor']);
        $this->editCsActive = true;
    }

    public function deleteCostStandard($id)
    {
        $cs = CommerceCostStandard::find($id);
        if ($cs) {
            \Platform\Commerce\Models\CommerceArticle::where('cost_standard_id', $id)
                ->update(['cost_standard_id' => null]);
            $cs->delete();
            $this->dispatch('notify', type: 'success', message: 'Kostensatz wurde gelöscht.');
        }
    }

    public function render()
    {
        return view('commerce::livewire.settings.index', [
            'categories' => $this->getTeamCategories(),
            'contexts' => $this->getTeamContexts(),
            'articleTypes' => $this->getTeamArticleTypes(),
            'articleCategories' => $this->getTeamArticleCategories(),
            'allArticleCategories' => $this->getAllArticleCategories(),
            'units' => $this->getTeamUnits(),
            'unitConversions' => $this->getTeamUnitConversions(),
            'costStandards' => $this->getTeamCostStandards(),
        ])->layout('platform::layouts.app');
    }
}
