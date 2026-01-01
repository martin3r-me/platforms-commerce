<?php

namespace Platform\Commerce\Livewire\Products\Boards;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Commerce\Models\CommerceProductBoardSlot;
use Platform\Commerce\Models\CommerceProduct;

class SlotHeader extends Component
{
    public $productBoardSlot;

    protected function rules()
    {
        return [
            'productBoardSlot.name' => 'required|string|max:255',
            'productBoardSlot.color' => 'nullable|string|max:7',
        ];
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

        $this->redirect(route('commerce.products.show', $newProduct));
    }

    public function deleteSlot()
    {
        $this->productBoardSlot->delete();
        $this->dispatch('deleted');
    }

    public function render()
    {
        return view('commerce::livewire.products.boards.slot-header');
    }

    public function save()
    {
        $this->productBoardSlot->save();
    }
}

