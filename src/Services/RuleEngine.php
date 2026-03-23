<?php

namespace Platform\Commerce\Services;

use Platform\Commerce\Models\CommerceProductRule;
use Platform\Commerce\ValueObjects\RuleResult;
use Platform\Commerce\Enums\RuleType;

class RuleEngine
{
    /**
     * Evaluate all active rules for a given target.
     *
     * @param int $targetId The product or article ID
     * @param string $targetType The morph type (e.g. CommerceProduct::class)
     * @param array $context Context data for evaluation (quantity, order_value, date, etc.)
     * @param int $teamId
     * @return RuleResult[]
     */
    public function evaluate(int $targetId, string $targetType, array $context, int $teamId): array
    {
        $now = now();

        $rules = CommerceProductRule::where('team_id', $teamId)
            ->where('is_active', true)
            ->where(function ($q) use ($targetId, $targetType) {
                $q->where(function ($sq) use ($targetId, $targetType) {
                    $sq->where('applies_to_type', $targetType)
                       ->where('applies_to_id', $targetId);
                })->orWhereNull('applies_to_type');

                // Also include rules linked via commerce_product_id
                $q->orWhere('commerce_product_id', $targetId);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', $now);
            })
            ->orderBy('priority', 'desc')
            ->get();

        $results = [];

        foreach ($rules as $rule) {
            $results[] = $this->evaluateRule($rule, $context);
        }

        return $results;
    }

    protected function evaluateRule(CommerceProductRule $rule, array $context): RuleResult
    {
        $ruleType = $rule->rule_type;

        return match ($ruleType) {
            RuleType::QuantityLimit => $this->evaluateQuantityLimit($rule, $context),
            RuleType::OrderValue => $this->evaluateOrderValue($rule, $context),
            RuleType::SalePeriod => $this->evaluateSalePeriod($rule, $context),
            RuleType::Dependency => $this->evaluateDependency($rule, $context),
            RuleType::Exclusion => $this->evaluateExclusion($rule, $context),
            RuleType::MandatoryField => $this->evaluateMandatoryField($rule, $context),
            default => new RuleResult(
                passed: true,
                ruleName: $rule->name,
                message: 'Unknown rule type, skipped.',
                action: null,
            ),
        };
    }

    protected function evaluateQuantityLimit(CommerceProductRule $rule, array $context): RuleResult
    {
        $conditions = $rule->conditions ?? [];
        $maxQuantity = $conditions['max_quantity'] ?? $rule->max_quantity_per_order;
        $quantity = $context['quantity'] ?? 0;

        if ($maxQuantity !== null && $quantity > $maxQuantity) {
            return new RuleResult(
                passed: false,
                ruleName: $rule->name,
                message: "Quantity {$quantity} exceeds maximum of {$maxQuantity}.",
                action: $rule->actions,
            );
        }

        return new RuleResult(passed: true, ruleName: $rule->name, message: 'Quantity within limit.');
    }

    protected function evaluateOrderValue(CommerceProductRule $rule, array $context): RuleResult
    {
        $conditions = $rule->conditions ?? [];
        $minValue = $conditions['min_order_value'] ?? $rule->min_order_value;
        $orderValue = $context['order_value'] ?? 0;

        if ($minValue !== null && $orderValue < $minValue) {
            return new RuleResult(
                passed: false,
                ruleName: $rule->name,
                message: "Order value {$orderValue} is below minimum of {$minValue}.",
                action: $rule->actions,
            );
        }

        return new RuleResult(passed: true, ruleName: $rule->name, message: 'Order value meets requirement.');
    }

    protected function evaluateSalePeriod(CommerceProductRule $rule, array $context): RuleResult
    {
        $now = $context['date'] ?? now();
        $conditions = $rule->conditions ?? [];
        $start = $conditions['sale_period_start'] ?? $rule->sale_period_start;
        $end = $conditions['sale_period_end'] ?? $rule->sale_period_end;

        if ($start && $now < $start) {
            return new RuleResult(
                passed: false,
                ruleName: $rule->name,
                message: "Sale period has not started yet.",
                action: $rule->actions,
            );
        }

        if ($end && $now > $end) {
            return new RuleResult(
                passed: false,
                ruleName: $rule->name,
                message: "Sale period has ended.",
                action: $rule->actions,
            );
        }

        return new RuleResult(passed: true, ruleName: $rule->name, message: 'Within sale period.');
    }

    protected function evaluateDependency(CommerceProductRule $rule, array $context): RuleResult
    {
        $conditions = $rule->conditions ?? [];
        $requiredProductIds = $conditions['required_product_ids'] ?? [];
        $cartProductIds = $context['cart_product_ids'] ?? [];

        foreach ($requiredProductIds as $requiredId) {
            if (!in_array($requiredId, $cartProductIds)) {
                return new RuleResult(
                    passed: false,
                    ruleName: $rule->name,
                    message: "Required product {$requiredId} not in cart.",
                    action: $rule->actions,
                );
            }
        }

        return new RuleResult(passed: true, ruleName: $rule->name, message: 'All dependencies met.');
    }

    protected function evaluateExclusion(CommerceProductRule $rule, array $context): RuleResult
    {
        $conditions = $rule->conditions ?? [];
        $excludedProductIds = $conditions['excluded_product_ids'] ?? [];
        $cartProductIds = $context['cart_product_ids'] ?? [];

        foreach ($excludedProductIds as $excludedId) {
            if (in_array($excludedId, $cartProductIds)) {
                return new RuleResult(
                    passed: false,
                    ruleName: $rule->name,
                    message: "Excluded product {$excludedId} found in cart.",
                    action: $rule->actions,
                );
            }
        }

        return new RuleResult(passed: true, ruleName: $rule->name, message: 'No exclusion conflicts.');
    }

    protected function evaluateMandatoryField(CommerceProductRule $rule, array $context): RuleResult
    {
        $conditions = $rule->conditions ?? [];
        $requiredFields = $conditions['required_fields'] ?? [];
        $data = $context['data'] ?? [];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                return new RuleResult(
                    passed: false,
                    ruleName: $rule->name,
                    message: "Mandatory field '{$field}' is missing or empty.",
                    action: $rule->actions,
                );
            }
        }

        return new RuleResult(passed: true, ruleName: $rule->name, message: 'All mandatory fields present.');
    }
}
