<?php

namespace Platform\Commerce\Livewire\Catalogs;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Platform\Commerce\Models\CommerceCatalog;
use Platform\Commerce\Enums\CatalogStatus;

class Index extends Component
{
    public $name = '';

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }

    public function createCatalog()
    {
        $this->validate();

        $catalog = CommerceCatalog::create([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'user_id' => Auth::user()->id,
            'team_id' => Auth::user()->currentTeam->id,
            'status' => CatalogStatus::Draft,
        ]);

        $this->reset(['name']);

        return redirect()->route('commerce.catalogs.show', $catalog);
    }

    public function render()
    {
        $team = Auth::user()->currentTeam;

        $catalogs = CommerceCatalog::where('team_id', $team->id)
            ->withCount('sections')
            ->orderBy('name')
            ->get();

        return view('commerce::livewire.catalogs.index', [
            'catalogs' => $catalogs,
        ])->layout('platform::layouts.app');
    }
}
