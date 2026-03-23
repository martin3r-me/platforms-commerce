<?php

namespace Platform\Commerce\ValueObjects;

class RuleResult
{
    public function __construct(
        public readonly bool $passed,
        public readonly ?string $ruleName,
        public readonly ?string $message,
        public readonly ?array $action = null,
    ) {}

    public function toArray(): array
    {
        return [
            'passed' => $this->passed,
            'rule_name' => $this->ruleName,
            'message' => $this->message,
            'action' => $this->action,
        ];
    }
}
