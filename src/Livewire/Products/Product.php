<?php

namespace Platform\Commerce\Livewire\Products;

use Livewire\Component;
use Platform\Commerce\Models\CommerceProduct;
use Platform\Commerce\Models\CommerceProductSlot;

class Product extends Component
{
    public $product;

    protected function rules()
    {
        return [
            'product.name' => 'required|string|max:255',
            'product.description' => 'string',
            'product.color' => 'string',
            'product.price_deviation_type' => 'string',
            'product.price_deviation_value' => 'nullable|numeric',
        ];
    }

    public function mount(CommerceProduct $commerceProduct)
    {
        $this->product = $commerceProduct->load([
            'productSlots',
            'activities',
            'creator'
        ]);
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
        $this->product->save();
    }

    public function render()
    {
        return view('commerce::livewire.products.product')->layout('platform::layouts.app');
    }

    public function createProductSlot()
    {
        $slot = CommerceProductSlot::create([
            'name' => "Neuer Artikel-Slot",
            'required' => false,
            'multi_select' => false,
            'order' => 0,
            'min_selection' => null,
            'max_selection' => null,
            'active' => true,
            'user_id' => \Illuminate\Support\Facades\Auth::user()->id,
            'team_id' => \Illuminate\Support\Facades\Auth::user()->currentTeam->id,
        ]);

        $this->product->productSlots()->attach($slot->id);
        $this->product->refresh();
    }
}

