<?php

namespace Platform\Commerce\Livewire;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('commerce::livewire.index')->layout('platform::layouts.app');
    }
}

