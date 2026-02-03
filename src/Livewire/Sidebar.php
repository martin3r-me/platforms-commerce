<?php

/**
 * Commerce Sidebar Livewire Component
 *
 * Modul-spezifische Sidebar für Commerce.
 *
 * WICHTIG FÜR LLMs:
 * - Wird automatisch in der Haupt-Sidebar eingebunden
 * - Zeigt modul-spezifische Navigation
 */

namespace Platform\Commerce\Livewire;

use Livewire\Component;

class Sidebar extends Component
{
    /**
     * Render-Methode
     *
     * Gibt die Sidebar-View zurück.
     */
    public function render()
    {
        return view('commerce::livewire.sidebar');
    }
}

