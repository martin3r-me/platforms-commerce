<?php

namespace Platform\Commerce\Livewire\Products\Boards;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Commerce\Models\CommerceProductBoardSlot;
use Platform\Commerce\Models\CommerceProduct;

class Slot extends Component
{
    public $productBoardSlot;

    public function render()
    {
        return view('commerce::livewire.products.boards.slot');
    }

    public function createProduct()
    {
        $newProduct = new CommerceProduct;
        $newProduct->name = "New Product";
        $newProduct->user_id = Auth::user()->id;
        $newProduct->team_id = Auth::user()->currentTeam->id;
        $minOrder = $this->productBoardSlot->products()->min('order');
        $newProduct->order = $minOrder ? $minOrder - 1 : 1;
        $newProduct->commerce_product_board_slot_id = $this->productBoardSlot->id;
        $newProduct->save();

        $this->productBoardSlot->refresh();
    }
}

