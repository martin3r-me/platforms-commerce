<?php

namespace Platform\Commerce\Livewire\Products\Boards;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Commerce\Models\CommerceProductBoard;
use Platform\Commerce\Models\CommerceProductBoardSlot;
use Platform\Commerce\Models\CommerceProduct;

class Board extends Component
{
    public $board;

    public function mount(CommerceProductBoard $commerceProductBoard)
    {
        $this->board = $commerceProductBoard->load([
            'productBoardSlots.products',
            'activities'
        ]);
    }

    public function createProductBoardSlot()
    {
        $newProductBoardSlot = new CommerceProductBoardSlot();
        $newProductBoardSlot->commerce_product_board_id = $this->board->id;
        $newProductBoardSlot->user_id = Auth::user()->id;
        $newProductBoardSlot->team_id = Auth::user()->currentTeam->id;
        $newProductBoardSlot->name = "Neuer Slot";
        $newProductBoardSlot->order = $this->board->productBoardSlots->count() + 1;
        $newProductBoardSlot->save();

        $this->board->refresh();
    }

    public function updateProductBoardSlotOrder($slots)
    {
        foreach($slots as $slot) {
            $slotDb = CommerceProductBoardSlot::find($slot['value']);
            $slotDb->order = $slot['order'];
            $slotDb->save();
        }

        $this->board->refresh();
    }

    public function updateProductOrder($slots)
    {
        foreach ($slots as $slot) {
            $slotId = $slot['value'];
            $productIds = collect($slot['items'])->pluck('value')->toArray();
            $products = CommerceProduct::whereIn('id', $productIds)->get();

            foreach ($products as $product) {
                $newOrder = collect($slot['items'])->firstWhere('value', $product->id)['order'];
                $newSlotId = $slot['value'];

                if ($product->order != $newOrder || $product->commerce_product_board_slot_id != $newSlotId) {
                    $product->order = $newOrder;
                    $product->commerce_product_board_slot_id = $newSlotId;
                    $product->save();
                }
            }
        }
    }

    public function render()
    {
        return view('commerce::livewire.products.boards.board')->layout('platform::layouts.app');
    }
}

