<?php

/**
 * Products Index Livewire Component
 *
 * Übersicht der Produkte als einfache Liste.
 *
 * WICHTIG FÜR LLMs:
 * - Zeigt alle Produkte des Teams als Liste
 * - Keine Board-Struktur mehr
 * - Einfache, übersichtliche Darstellung
 */

namespace Platform\Commerce\Livewire\Products;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Commerce\Models\CommerceProduct;

class Index extends Component
{
    public $name = '';

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }

    /**
     * Erstellt ein neues Produkt
     */
    public function createProduct()
    {
        $this->validate();

        CommerceProduct::create([
            'name' => $this->name,
            'user_id' => Auth::user()->id,
            'team_id' => Auth::user()->currentTeam->id,
        ]);

        $this->reset(['name']);
    }

    /**
     * Render-Methode
     */
    public function render()
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        /**
         * Lade Produkte für das Team
         */
        $products = CommerceProduct::where('team_id', $team->id)
            ->with(['article', 'slot.board', 'productSlots'])
            ->orderBy('name')
            ->get();

        return view('commerce::livewire.products.index', [
            'products' => $products,
        ])->layout('platform::layouts.app');
    }
}
