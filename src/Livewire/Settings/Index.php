<?php

namespace Platform\Commerce\Livewire\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Commerce\Models\CommerceTaxCategory;
use Platform\Commerce\Models\CommerceSalesContext;
use Platform\Commerce\Models\CommerceTaxRule;
use Platform\Commerce\Services\TaxRuleManager;

class Index extends Component
{
    // Tax Category fields
    public $name;
    public $default_rate;

    // Sales Context fields
    public $context_name;
    public $context_description;

    // Edit fields
    public $editCategoryId;
    public $editCategoryName;
    public $editCategoryRate;

    public $editContextId;
    public $editContextName;
    public $editContextDescription;

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
            // Delete related tax rules first (cascade would handle it, but be explicit)
            CommerceTaxRule::where('commerce_sales_context_id', $id)->delete();
            $context->delete();
            $this->loadMatrix();
            $this->dispatch('notify', type: 'success', message: 'Verkaufskontext wurde gelöscht.');
        }
    }

    public function render()
    {
        return view('commerce::livewire.settings.index', [
            'categories' => $this->getTeamCategories(),
            'contexts' => $this->getTeamContexts(),
        ])->layout('platform::layouts.app');
    }
}
