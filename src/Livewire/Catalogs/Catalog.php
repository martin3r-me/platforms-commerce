<?php

namespace Platform\Commerce\Livewire\Catalogs;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Commerce\Models\CommerceCatalog;
use Platform\Commerce\Models\CommerceCatalogSection;
use Platform\Commerce\Models\CommerceProductBoard;
use Platform\Commerce\Enums\CatalogStatus;

class Catalog extends Component
{
    public $catalog;
    public $sectionName = '';
    public $sectionSortOrder = 0;
    public $availableBoards = [];

    protected function rules()
    {
        return [
            'catalog.name' => 'required|string|max:255',
            'catalog.slug' => 'required|string|max:255',
            'catalog.description' => 'nullable|string',
            'catalog.status' => 'required',
            'catalog.valid_from' => 'nullable|date',
            'catalog.valid_until' => 'nullable|date',
        ];
    }

    public function mount(CommerceCatalog $commerceCatalog)
    {
        $this->catalog = $commerceCatalog->load([
            'sections.productBoards.productBoardSlots.products.article.costStandard',
            'sections.productBoards.productBoardSlots.products.article.suppliers',
            'creator',
        ]);

        $this->availableBoards = CommerceProductBoard::where('team_id', Auth::user()->currentTeam->id)
            ->orderBy('name')
            ->get();
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
        $this->catalog->save();
    }

    public function createSection()
    {
        $this->validate([
            'sectionName' => 'required|string|max:255',
        ]);

        CommerceCatalogSection::create([
            'commerce_catalog_id' => $this->catalog->id,
            'name' => $this->sectionName,
            'sort_order' => $this->sectionSortOrder,
            'user_id' => Auth::user()->id,
            'team_id' => Auth::user()->currentTeam->id,
        ]);

        $this->reset(['sectionName', 'sectionSortOrder']);
        $this->catalog->refresh();
        $this->catalog->load(['sections.productBoards.productBoardSlots.products.article.costStandard', 'sections.productBoards.productBoardSlots.products.article.suppliers']);
    }

    public function deleteSection($sectionId)
    {
        $section = CommerceCatalogSection::where('id', $sectionId)
            ->where('commerce_catalog_id', $this->catalog->id)
            ->firstOrFail();

        $section->delete();

        $this->catalog->refresh();
        $this->catalog->load(['sections.productBoards.productBoardSlots.products.article.costStandard', 'sections.productBoards.productBoardSlots.products.article.suppliers']);
    }

    public function attachBoard($sectionId, $boardId)
    {
        $section = $this->catalog->sections->find($sectionId);
        if ($section && !$section->productBoards->contains($boardId)) {
            $maxSort = $section->productBoards->max('pivot.sort_order') ?? 0;
            $section->productBoards()->attach($boardId, ['sort_order' => $maxSort + 1]);
        }

        $this->catalog->refresh();
        $this->catalog->load(['sections.productBoards.productBoardSlots.products.article.costStandard', 'sections.productBoards.productBoardSlots.products.article.suppliers']);
    }

    public function detachBoard($sectionId, $boardId)
    {
        $section = $this->catalog->sections->find($sectionId);
        if ($section) {
            $section->productBoards()->detach($boardId);
        }

        $this->catalog->refresh();
        $this->catalog->load(['sections.productBoards.productBoardSlots.products.article.costStandard', 'sections.productBoards.productBoardSlots.products.article.suppliers']);
    }

    public function render()
    {
        return view('commerce::livewire.catalogs.catalog', [
            'statuses' => CatalogStatus::cases(),
        ])->layout('platform::layouts.app');
    }
}
