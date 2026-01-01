<?php

namespace Platform\Commerce\Livewire\Products;

use Livewire\Component;
use Platform\Commerce\Models\CommerceProductSlotVariant;

class MatrixRow extends Component
{
    public $variant;
    public $articles;

    protected function rules()
    {
        return [
            'variant.commerce_article_id' => 'nullable|integer',
        ];
    }

    public function mount($variant, $articles)
    {
        if (!($variant instanceof CommerceProductSlotVariant)) {
            throw new \Exception('Ungültige Variante übergeben.');
        }

        $this->variant = $variant;
        $this->articles = $articles;
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
        $this->variant->save();
    }

    public function saveVariant()
    {
        $this->validate();
        $this->variant->save();
        session()->flash('message', 'Variant updated successfully!');
    }

    public function render()
    {
        return view('commerce::livewire.products.matrix-row', [
            'variant' => $this->variant,
            'articles' => $this->articles,
        ]);
    }
}

