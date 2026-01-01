<div class="p-3 bg-gray-50 rounded border">
    <div class="d-flex items-center gap-4">
        <div class="flex-1">{{ $rule->taxCategory->name ?? '-' }}</div>
        <div class="flex-1">{{ $rule->salesContext->name ?? '-' }}</div>
        <div class="w-32">
            <input type="number" wire:model="rule.tax_rate" class="w-full border rounded px-2 py-1">
        </div>
    </div>
</div>

