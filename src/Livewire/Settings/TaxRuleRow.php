<?php

namespace Platform\Commerce\Livewire\Settings;

use Livewire\Component;

class TaxRuleRow extends Component
{
    public $rule;
    public $loop;

    protected function rules()
    {
        return [
            'rule.tax_rate' => 'required|numeric|min:0|max:100',
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
        $this->rule->save();
    }

    public function render()
    {
        return view('commerce::livewire.settings.tax-rule-row');
    }
}

