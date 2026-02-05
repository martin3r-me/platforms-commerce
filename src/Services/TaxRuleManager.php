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
        $teamId = Auth::user()->currentTeam->id;
        $userId = Auth::user()->id;

        $categories = CommerceTaxCategory::where('team_id', $teamId)->orderBy('name')->get();
        $contexts = CommerceSalesContext::where('team_id', $teamId)->orderBy('name')->get();

        if ($categories->isEmpty() || $contexts->isEmpty()) {
            return;
        }

        $existingRules = CommerceTaxRule::where('team_id', $teamId)->get()->keyBy(function ($rule) {
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
                        'team_id' => $teamId,
                    ],
                    [
                        'user_id' => $userId,
                        'tax_rate' => $taxRate,
                        'valid_from' => now(),
                    ]
                );
            }
        }

        unset($existingRules);
    }
}
