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
    public $name;
    public $default_rate;
    public $context_name;
    public $context_description;
    public $matrix = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'default_rate' => 'required|numeric|min:0|max:100',
        'context_name' => 'required|string|max:255',
        'context_description' => 'nullable|string|max:1000',
    ];

    public function mount()
    {
        $this->matrix = CommerceTaxRule::with(['taxCategory', 'salesContext'])->orderBy('commerce_sales_context_id')->get();
    }

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

        $this->matrix = CommerceTaxRule::with(['taxCategory', 'salesContext'])->orderBy('commerce_sales_context_id')->get();
        $this->reset(['name', 'default_rate']);
    }

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

        $this->matrix = CommerceTaxRule::with(['taxCategory', 'salesContext'])->orderBy('commerce_sales_context_id')->get();
        $this->reset(['context_name', 'context_description']);
    }

    public function render()
    {
        return view('commerce::livewire.settings.index')->layout('platform::layouts.app');
    }
}

