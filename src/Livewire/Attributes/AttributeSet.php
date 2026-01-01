<?php

namespace Platform\Commerce\Livewire\Attributes;

use Livewire\Component;
use Platform\Commerce\Models\CommerceAttributeSet;
use Platform\Commerce\Models\CommerceAttributeSetItem;

class AttributeSet extends Component
{
    public $attributeSet;
    public $items;
    public $itemName, $itemDescription, $itemColor;
    public $itemId;

    protected $rules = [
        'attributeSet.name' => 'required|string|max:255',
        'attributeSet.color' => 'nullable|string|max:7',
        'attributeSet.is_multiselect' => 'boolean',
        'attributeSet.is_required' => 'boolean',
        'itemName' => 'required|string|max:255',
        'itemDescription' => 'nullable|string',
        'itemColor' => 'nullable|string|max:7',
    ];

    public function mount(CommerceAttributeSet $commerceAttributeSet)
    {
        $this->attributeSet = $commerceAttributeSet;
        $this->items = $this->attributeSet->attributeSetItems;
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
        $this->attributeSet->save();
    }

    public function createItem()
    {
        $this->validate([
            'itemName' => 'required|string|max:255',
            'itemDescription' => 'nullable|string',
            'itemColor' => 'nullable|string|max:7',
        ]);

        $item = $this->attributeSet->attributeSetItems()->create([
            'name' => $this->itemName,
            'description' => $this->itemDescription,
            'color' => $this->itemColor,
        ]);

        $this->items = $this->attributeSet->attributeSetItems()->get();
        $this->reset(['itemName', 'itemDescription', 'itemColor']);
    }

    public function deleteItem($id)
    {
        $item = CommerceAttributeSetItem::find($id);
        if ($item) {
            $item->delete();
            $this->items = $this->items->filter(fn($i) => $i->id !== $id);
        }
    }

    public function updateItem()
    {
        $this->validate([
            'itemName' => 'required|string|max:255',
            'itemDescription' => 'nullable|string',
            'itemColor' => 'nullable|string|max:7',
        ]);

        $item = CommerceAttributeSetItem::find($this->itemId);
        
        if ($item) {
            $item->update([
                'name' => $this->itemName,
                'description' => $this->itemDescription,
                'color' => $this->itemColor,
            ]);

            $this->items = $this->attributeSet->attributeSetItems()->get();
        }

        $this->reset(['itemId', 'itemName', 'itemDescription', 'itemColor']);
    }

    public function resetItemFields()
    {
        $this->reset(['itemName', 'itemDescription', 'itemColor']);
    }

    public function render()
    {
        return view('commerce::livewire.attributes.attribute-set')->layout('platform::layouts.app');
    }
}

