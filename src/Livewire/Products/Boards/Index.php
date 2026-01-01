<?php

namespace Platform\Commerce\Livewire\Products\Boards;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;
use Platform\Commerce\Models\CommerceProductBoard;

class Index extends Component
{
    public $accounts;
    public $account_id;
    public $name;

    protected function rules()
    {
        return [
            'account_id' => 'int',
        ];
    }

    public function mount()
    {
        $accounts = \App\Models\Modules\Relations\ModulesRelationsAccount::where('team_id', Auth::user()->currentTeam->id)->orderBy('name')->get();
        if ($accounts->isNotEmpty()) {
            $this->account_id = $accounts->first()->id;
        }
        $this->accounts = $accounts;
    }

    #[Computed]
    public function account()
    {
        if (!$this->account_id) {
            return null;
        }
        return \App\Models\Modules\Relations\ModulesRelationsAccount::find($this->account_id);
    }

    public function createProductBoard()
    {
        $newProductBoard = new CommerceProductBoard();
        $newProductBoard->user_id = Auth::user()->id;
        $newProductBoard->team_id = Auth::user()->currentTeam->id;
        $newProductBoard->modules_relations_account_id = $this->account_id;
        $newProductBoard->name = $this->name;
        $newProductBoard->save();
    }

    public function render()
    {
        return view('commerce::livewire.products.boards.index')->layout('platform::layouts.app');
    }
}

