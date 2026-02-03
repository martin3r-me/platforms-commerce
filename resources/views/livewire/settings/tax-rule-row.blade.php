{{--
    Tax Rule Row View
    Einzelne Zeile in der Steuermatrix
--}}

<div class="grid grid-cols-12 px-6 py-3 items-center hover:bg-[var(--ui-muted-5)] transition-colors">
    {{-- Verkaufskontext --}}
    <div class="col-span-4">
        <span class="text-sm text-[var(--ui-secondary)]">{{ $rule->salesContext->name ?? '-' }}</span>
    </div>

    {{-- Steuerkategorie --}}
    <div class="col-span-4">
        <span class="text-sm text-[var(--ui-secondary)]">{{ $rule->taxCategory->name ?? '-' }}</span>
    </div>

    {{-- Steuersatz --}}
    <div class="col-span-3">
        <div class="relative">
            <input type="number"
                   wire:model.live="rule.tax_rate"
                   step="0.01"
                   min="0"
                   max="100"
                   class="w-full bg-[var(--ui-muted-5)] border-0 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[var(--ui-primary)]">
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <span class="text-sm text-[var(--ui-muted)]">%</span>
            </div>
        </div>
        @error('rule.tax_rate')
            <span class="text-xs text-red-500">{{ $message }}</span>
        @enderror
    </div>

    {{-- Aktionen --}}
    <div class="col-span-1 text-right">
        <span class="text-xs text-[var(--ui-muted)]">
            @if($rule->updated_at)
                {{ $rule->updated_at->diffForHumans() }}
            @endif
        </span>
    </div>
</div>
