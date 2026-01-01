<?php

namespace Platform\Commerce\Livewire\Articles;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;
use Platform\Commerce\Models\CommerceArticleCategory;

class Index extends Component
{
    public $accounts;
    public $categories;
    public $name;
    public $category_id;
    public $account_id;

    protected function rules()
    {
        return [
            'account_id' => 'int',
        ];
    }

    public function render()
    {
        return view('commerce::livewire.articles.index')->layout('platform::layouts.app');
    }

    public function mount()
    {
        $this->categories = CommerceArticleCategory::all();
        $this->accounts = \App\Models\Modules\Relations\ModulesRelationsAccount::where('team_id', Auth::user()->currentTeam->id)->orderBy('name')->get();
        if ($this->accounts->isNotEmpty()) {
            $this->account_id = $this->accounts->first()->id;
        }
    }

    #[Computed]
    public function account()
    {
        if (!$this->account_id) {
            return null;
        }
        return \App\Models\Modules\Relations\ModulesRelationsAccount::find($this->account_id);
    }

    public function createArticle()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:commerce_article_categories,id',
        ]);

        \Platform\Commerce\Models\CommerceArticle::create([
            'name' => $this->name,
            'modules_relations_account_id' => $this->account?->id,
            'user_id' => Auth::user()->id,
            'team_id' => Auth::user()->currentTeam->id,
            'category_id' => $this->category_id,
        ]);

        $this->reset(['name', 'category_id']);
    }
}

