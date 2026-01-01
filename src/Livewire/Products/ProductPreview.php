<?php

namespace Platform\Commerce\Livewire\Products;

use Livewire\Component;

class ProductPreview extends Component
{
    public $product;
    
    public function render()
    {
        return view('commerce::livewire.products.product-preview');
    }
}

