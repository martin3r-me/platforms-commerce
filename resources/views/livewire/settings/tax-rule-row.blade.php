{{--
    Tax Rule Row View
    Einzelne Zeile in der Steuermatrix
--}}

<div class="grid grid-cols-12 px-6 py-3 items-center hover:bg-blue-50/50 transition-colors">
    {{-- Verkaufskontext --}}
    <div class="col-span-4">
        <span class="text-[13px] text-gray-700">{{ $rule->salesContext->name ?? '-' }}</span>
    </div>

    {{-- Steuerkategorie --}}
    <div class="col-span-4">
        <span class="text-[13px] text-gray-700">{{ $rule->taxCategory->name ?? '-' }}</span>
    </div>

    {{-- Steuersatz --}}
    <div class="col-span-3">
        <div class="relative">
            <input type="number"
                   wire:model.live="rule.tax_rate"
                   step="0.01"
                   min="0"
                   max="100"
                   class="w-full px-3 py-2 text-[13px] rounded-md border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#166EE1]/20 focus:border-[#166EE1]">
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <span class="text-[13px] text-gray-500">%</span>
            </div>
        </div>
        @error('rule.tax_rate')
            <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Aktionen --}}
    <div class="col-span-1 text-right">
        <span class="text-[11px] text-gray-400">
            @if($rule->updated_at)
                {{ $rule->updated_at->diffForHumans() }}
            @endif
        </span>
    </div>
</div>
