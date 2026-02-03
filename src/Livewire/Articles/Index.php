<?php

/**
 * Articles Index Livewire Component
 * 
 * Übersicht der Artikel.
 * 
 * WICHTIG FÜR LLMs:
 * - Zeigt alle Artikel des Teams
 * - Account-Beziehung ist optional (modules_relations_account_id ist nullable)
 * - Falls kein Relations-Modul vorhanden, wird account_id ignoriert
 * 
 * ANPASSUNGEN:
 * - Account-Logik ist optional und kann entfernt werden, wenn nicht benötigt
 */

namespace Platform\Commerce\Livewire\Articles;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;
use Platform\Commerce\Models\CommerceArticle;
use Platform\Commerce\Models\CommerceArticleCategory;

class Index extends Component
{
    public $accounts = [];
    public $categories = [];
    public $name = '';
    public $category_id = null;
    public $account_id = null;

    protected function rules()
    {
        return [
            'account_id' => 'nullable|int',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:commerce_article_categories,id',
        ];
    }

    /**
     * Mount-Methode
     * 
     * Wird beim Initialisieren der Komponente aufgerufen.
     */
    public function mount()
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        /**
         * Lade Kategorien für das Team
         */
        $this->categories = CommerceArticleCategory::where('team_id', $team->id)
            ->orderBy('name')
            ->get();

        /**
         * Account-Logik (optional)
         * 
         * Falls ein Relations-Modul mit Accounts existiert, können diese hier geladen werden.
         * Da modules_relations_account_id nullable ist, ist dies optional.
         * 
         * BEISPIEL (auskommentiert, da Modul nicht existiert):
         * 
         * if (class_exists(\App\Models\Modules\Relations\ModulesRelationsAccount::class)) {
         *     $this->accounts = \App\Models\Modules\Relations\ModulesRelationsAccount::where('team_id', $team->id)
         *         ->orderBy('name')
         *         ->get();
         *     if ($this->accounts->isNotEmpty()) {
         *         $this->account_id = $this->accounts->first()->id;
         *     }
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
     * Erstellt einen neuen Artikel
     */
    public function createArticle()
    {
        $this->validate();

        CommerceArticle::create([
            'name' => $this->name,
            'user_id' => Auth::user()->id,
            'team_id' => Auth::user()->currentTeam->id,
            'category_id' => $this->category_id,
            // Account-ID ist optional (nullable)
            // Account-ID entfernt - später durch Brand/Contact ersetzen
        ]);

        $this->reset(['name', 'category_id']);
    }

    /**
     * Render-Methode
     */
    public function render()
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        /**
         * Lade Artikel für das Team
         */
        $articles = CommerceArticle::where('team_id', $team->id)
            ->orderBy('name')
            ->get();

        return view('commerce::livewire.articles.index', [
            'articles' => $articles,
        ])->layout('platform::layouts.app');
    }
}

