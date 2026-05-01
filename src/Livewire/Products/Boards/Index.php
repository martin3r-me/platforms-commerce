<?php

/**
 * Products Boards Index Livewire Component
 * 
 * Übersicht der Produkt-Boards.
 * 
 * WICHTIG FÜR LLMs:
 * - Zeigt alle Product Boards des Teams
 * - Account-Beziehung ist optional (modules_relations_account_id ist nullable)
 * - Falls kein Relations-Modul vorhanden, wird account_id ignoriert
 * 
 * ANPASSUNGEN:
 * - Account-Logik ist optional und kann entfernt werden, wenn nicht benötigt
 */

namespace Platform\Commerce\Livewire\Products\Boards;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;
use Platform\Commerce\Models\CommerceProductBoard;

class Index extends Component
{
    public $accounts = [];
    public $account_id = null;
    public $name = '';

    protected function rules()
    {
        return [
            'account_id' => 'nullable|int',
            'name' => 'required|string|max:255',
        ];
    }

    /**
     * Mount-Methode
     * 
     * Wird beim Initialisieren der Komponente aufgerufen.
     * 
     * HINWEIS: Account-Logik ist optional.
     * Falls kein Relations-Modul vorhanden ist, wird account_id ignoriert.
     */
    public function mount()
    {
        /**
         * Account-Logik (optional)
         * 
         * Falls ein Relations-Modul mit Accounts existiert, können diese hier geladen werden.
         * Da modules_relations_account_id nullable ist, ist dies optional.
         * 
         * BEISPIEL (auskommentiert, da Modul nicht existiert):
         * 
         * if (class_exists(\App\Models\Modules\Relations\ModulesRelationsAccount::class)) {
         *     $accounts = \App\Models\Modules\Relations\ModulesRelationsAccount::where('team_id', Auth::user()->currentTeam->id)
         *         ->orderBy('name')
         *         ->get();
         *     if ($accounts->isNotEmpty()) {
         *         $this->account_id = $accounts->first()->id;
         *     }
         *     $this->accounts = $accounts;
         * }
         */
        
        // Account-Logik ist aktuell deaktiviert, da Relations-Modul nicht existiert
        $this->accounts = collect([]);
    }

    /**
     * Account-Getter (optional)
     * 
     * Gibt das ausgewählte Account zurück, falls vorhanden.
     */
    #[Computed]
    public function account()
    {
        if (!$this->account_id) {
            return null;
        }
        
        // Account-Logik ist aktuell deaktiviert
        // if (class_exists(\App\Models\Modules\Relations\ModulesRelationsAccount::class)) {
        //     return \App\Models\Modules\Relations\ModulesRelationsAccount::find($this->account_id);
        // }
        
        return null;
    }

    /**
     * Erstellt ein neues Product Board
     */
    public function createProductBoard()
    {
        $this->validate();

        $newProductBoard = new CommerceProductBoard();
        $newProductBoard->user_id = Auth::user()->id;
        $newProductBoard->team_id = Auth::user()->currentTeam->id;
        $newProductBoard->name = $this->name;
        
        // Account-ID entfernt - später durch Brand/Contact ersetzen
        
        $newProductBoard->save();

        $this->reset(['name']);
        
        // Sidebar aktualisieren, damit neues Board erscheint
        $this->dispatch('updateSidebar');
    }

    /**
     * Render-Methode
     */
    public function render()
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        /**
         * Lade Product Boards für das Team
         */
        $productBoards = CommerceProductBoard::where('team_id', $team->id)
            ->withCount('productBoardSlots')
            ->orderBy('name')
            ->get();

        // Count products per board via slots
        $productBoards->each(function ($board) {
            $board->products_count = \Platform\Commerce\Models\CommerceProduct::whereIn(
                'commerce_product_board_slot_id',
                $board->productBoardSlots()->pluck('id')
            )->count();
        });

        return view('commerce::livewire.products.boards.index', [
            'productBoards' => $productBoards,
        ])->layout('platform::layouts.app');
    }
}

