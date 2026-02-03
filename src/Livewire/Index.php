<?php

/**
 * Commerce Index Livewire Component
 * 
 * Hauptübersicht des Commerce-Moduls.
 * 
 * WICHTIG FÜR LLMs:
 * - Jedes Modul sollte ein Index/Dashboard haben
 * - Verwendet platform::layouts.app Layout
 * - Kann comms-Event dispatch'en (für Kommunikation)
 * 
 * ANPASSUNGEN:
 * - Füge Datenqueries hinzu
 * - Passe View an deine Bedürfnisse an
 * - Füge Statistiken hinzu
 */

namespace Platform\Commerce\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Commerce\Models\CommerceArticle;
use Platform\Commerce\Models\CommerceProduct;
use Platform\Commerce\Models\CommerceAttributeSet;
use Platform\Commerce\Models\CommerceArticleCategory;

class Index extends Component
{
    /**
     * Dispatch comms-Event (optional)
     * 
     * Wird nach dem Rendern aufgerufen.
     * Kann für Kommunikation/Notifications verwendet werden.
     */
    public function rendered()
    {
        $this->dispatch('comms', [
            'model' => null,
            'modelId' => null,
            'subject' => 'Commerce Dashboard',
            'description' => 'Übersicht des Commerce-Moduls',
            'url' => route('commerce.index'),
            'source' => 'commerce.index',
            'recipients' => [],
            'meta' => [
                'view_type' => 'dashboard',
            ],
        ]);
    }

    /**
     * Render-Methode
     * 
     * Lädt Daten und gibt die View zurück.
     * 
     * PATTERN:
     * 1. User/Team holen
     * 2. Daten laden (Models, Statistiken, etc.)
     * 3. View mit Daten zurückgeben
     */
    public function render()
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        /**
         * Statistiken laden
         * 
         * WICHTIG: Daten werden hier geladen, nicht in der View!
         * Das ist Best Practice für Performance und Testbarkeit.
         */
        $stats = [
            'total_articles' => CommerceArticle::where('team_id', $team->id)->count(),
            'total_products' => CommerceProduct::where('team_id', $team->id)->count(),
            'total_attributes' => CommerceAttributeSet::where('team_id', $team->id)->count(),
            'total_categories' => CommerceArticleCategory::where('team_id', $team->id)->count(),
        ];

        return view('commerce::livewire.index', [
            'stats' => $stats,
        ])->layout('platform::layouts.app');
    }
}

