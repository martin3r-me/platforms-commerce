<?php

namespace Platform\Commerce\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Commerce\Models\CommerceProductBoard;
use Livewire\Attributes\On;

class Sidebar extends Component
{
    #[On('updateSidebar')] 
    public function updateSidebar()
    {
        // Sidebar wird aktualisiert
    }

    public function render()
    {
        $uid = auth()->id();
        $tid = auth()->user()?->currentTeam->id ?? null;
        
        // Lade Product Boards für die Sidebar
        $productBoards = CommerceProductBoard::query()
            ->where('team_id', $tid)
            ->orderBy('name')
            ->get();

        return view('commerce::livewire.sidebar', [
            'productBoards' => $productBoards,
        ]);
    }
}

