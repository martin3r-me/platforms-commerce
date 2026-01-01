<?php

namespace Platform\Commerce\Services;

use Platform\Commerce\Models\CommerceTaxCategory;
use Platform\Commerce\Models\CommerceSalesContext;
use Platform\Commerce\Models\CommerceTaxRule;
use Illuminate\Support\Facades\Auth;

class TaxRuleManager
{
    public function updateTaxRules()
    {
        $categories = CommerceTaxCategory::orderBy('name')->get();
        $contexts = CommerceSalesContext::orderBy('name')->get();

        if ($categories->isEmpty() || $contexts->isEmpty()) {
            return;
        }

        $existingRules = CommerceTaxRule::all()->keyBy(function ($rule) {
            return $rule->commerce_sales_context_id . '-' . $rule->commerce_tax_category_id;
        });

        foreach ($contexts as $context) {
            foreach ($categories as $category) {
                $key = $context->id . '-' . $category->id;

                $taxRate = $existingRules[$key]->tax_rate ?? $category->default_rate;

                CommerceTaxRule::updateOrCreate(
                    [
                        'commerce_sales_context_id' => $context->id,
                        'commerce_tax_category_id' => $category->id,
                    ],
                    [
                        'user_id' => Auth::user()->id,
                        'team_id' => Auth::user()->currentTeam->id,
                        'tax_rate' => $taxRate,
                        'valid_from' => now(),
                    ]
                );
            }
        }

        unset($existingRules);
    }
}

