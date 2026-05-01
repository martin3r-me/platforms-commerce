<?php

namespace Platform\Commerce\Livewire\Suppliers;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Platform\Commerce\Models\CommerceSupplier;
use Platform\Commerce\Enums\SupplierSourceType;
use Platform\Commerce\Enums\SupplierStatus;

class Index extends Component
{
    public string $name = '';
    public string $description = '';
    public string $sourceType = 'webhook_post';

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'sourceType' => 'required|in:manual,webhook_post,pull_get',
        ];
    }

    public function createSupplier()
    {
        $this->validate();

        $supplier = CommerceSupplier::create([
            'name' => $this->name,
            'description' => $this->description ?: null,
            'source_type' => $this->sourceType,
            'status' => SupplierStatus::Onboarding,
            'user_id' => Auth::user()->id,
            'team_id' => Auth::user()->currentTeam->id,
        ]);

        $this->reset(['name', 'description', 'sourceType']);

        if ($supplier->isManual()) {
            $supplier->update(['status' => SupplierStatus::Active]);
            return redirect()->route('commerce.suppliers.show', $supplier);
        }

        return redirect()->route('commerce.suppliers.onboarding', $supplier);
    }

    public function render()
    {
        $team = Auth::user()->currentTeam;

        $suppliers = CommerceSupplier::where('team_id', $team->id)
            ->withCount('articles')
            ->orderBy('name')
            ->get();

        return view('commerce::livewire.suppliers.index', [
            'suppliers' => $suppliers,
        ])->layout('platform::layouts.app');
    }
}
