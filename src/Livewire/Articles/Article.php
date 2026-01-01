<?php

namespace Platform\Commerce\Livewire\Articles;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Commerce\Models\CommerceArticle;
use Platform\Commerce\Models\CommerceArticleCategory;
use Platform\Commerce\Models\CommerceTaxCategory;
use Platform\Commerce\Models\CommerceAttributeSet;
use Platform\Commerce\Models\CommerceArticleNetPrice;

class Article extends Component
{
    public $article;
    public $categories;
    public $teamAttributeSets = [];
    public $selectedAttributeSets = [];
    public $selectedAttributeSetItems = [];
    public $netPrice;
    public $valid_from = null;
    public $taxCategories;

    protected function rules()
    {
        return [
            'article.name' => 'required|string|max:255',
            'article.description' => 'nullable|string',
            'article.color' => 'nullable|string',
            'article.sku' => 'nullable|string|unique:commerce_articles,sku,' . $this->article->id,
            'article.gtin' => 'nullable|string|unique:commerce_articles,gtin,' . $this->article->id,
            'article.ean' => 'nullable|string|unique:commerce_articles,ean,' . $this->article->id,
            'article.upc' => 'nullable|string|unique:commerce_articles,upc,' . $this->article->id,
            'article.isbn' => 'nullable|string|unique:commerce_articles,isbn,' . $this->article->id,
        'article.manufacturer_part_number' => 'nullable|string',
        'article.country_of_origin' => 'nullable|string|size:2',
        'article.hs_code' => 'nullable|string',
        'article.weight' => 'nullable|numeric|min:0',
        'article.width' => 'nullable|numeric|min:0',
        'article.height' => 'nullable|numeric|min:0',
        'article.depth' => 'nullable|numeric|min:0',
        'article.volume' => 'nullable|numeric|min:0',
        'article.is_fragile' => 'boolean',
        'article.shipping_class' => 'nullable|string',
        'article.lead_time_days' => 'nullable|integer|min:0',
        'article.is_available' => 'boolean',
        'article.stock_level' => 'nullable|integer|min:0',
        'article.stock_alert_threshold' => 'nullable|integer|min:0',
        'article.backorder_allowed' => 'boolean',
        'article.is_hazardous' => 'boolean',
        'article.expiry_date' => 'nullable|date',
        'article.storage_temperature' => 'nullable|numeric',
        'article.recyclable' => 'boolean',
        'article.category_id' => 'nullable|exists:commerce_article_categories,id',
        'article.commerce_tax_category_id' => 'nullable|exists:commerce_tax_categories,id',
        'article.tags' => 'nullable|json',
        'article.is_digital' => 'boolean',
        'article.is_physical' => 'boolean',
            'article.short_description' => 'nullable|string',
            'article.long_description' => 'nullable|string',
            'article.product_highlights' => 'nullable|json',
        ];
    }

    public function updatedSelectedAttributeSets()
    {
        $this->article->attributeSets()->sync($this->selectedAttributeSets);

        $this->selectedAttributeSetItems = $this->article
            ->attributeSets()
            ->with('attributeSetItems')
            ->get()
            ->flatMap(fn($set) => $set->attributeSetItems->pluck('id'))
            ->intersect($this->selectedAttributeSetItems)
            ->toArray();
    }

    public function updatedSelectedAttributeSetItems()
    {
        $this->article->attributeSetItems()->sync($this->selectedAttributeSetItems);
    }

    public function render()
    {
        return view('commerce::livewire.articles.article')->layout('platform::layouts.app');
    }

    public function mount(CommerceArticle $commerceArticle)
    {
        $this->article = $commerceArticle->load([
            'articleNetPrices',
            'articlePrices',
            'attributeSets',
            'attributeSetItems',
            'category',
            'taxCategory',
            'activities',
            'creator'
        ]);
        $this->categories = CommerceArticleCategory::all();
        $this->taxCategories = CommerceTaxCategory::all();

        $this->teamAttributeSets = CommerceAttributeSet::with('attributeSetItems')
            ->where('team_id', Auth::user()->currentTeam->id)
            ->get();

        $this->selectedAttributeSets = $this->article->attributeSets->pluck('id')->toArray();
        $this->selectedAttributeSetItems = $this->article->attributeSetItems->pluck('id')->toArray();
    }

    public function createPrice()
    {
        CommerceArticleNetPrice::create([
            'commerce_article_id' => $this->article->id,
            'net_price' => $this->netPrice,
            'valid_from' => $this->valid_from ?? now(),
            'valid_until' => null,
            'user_id' => Auth::user()->id,
            'team_id' => Auth::user()->currentTeam->id,
        ]);

        $this->article->refresh();
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
        $this->article->save();
    }
}

