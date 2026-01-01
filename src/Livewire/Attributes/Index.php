<?php

namespace Platform\Commerce\Livewire\Attributes;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Commerce\Models\CommerceAttributeSet;

class Index extends Component
{
    public $search = '';
    public $name, $color, $is_multiselect = false, $is_required = false;

    public function getAttributeSetsProperty()
    {
        $query = CommerceAttributeSet::query()
            ->where('team_id', auth()->user()->currentTeam->id);
        
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }
        
        return $query->get();
    }

    public function createAttributeSet()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'is_multiselect' => 'boolean',
            'is_required' => 'boolean',
        ]);

        CommerceAttributeSet::create([
            'user_id' => Auth::user()->id,
            'team_id' => Auth::user()->currentTeam->id,
            'name' => $this->name,
            'color' => $this->color,
            'is_multiselect' => $this->is_multiselect,
            'is_required' => $this->is_required,
        ]);

        $this->reset(['name', 'color', 'is_multiselect', 'is_required']);
    }

    public function render()
    {
        return view('commerce::livewire.attributes.index')->layout('platform::layouts.app');
    }
}

