<?php

namespace Platform\Commerce\Livewire\Suppliers;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Commerce\Models\CommerceSupplier;
use Platform\Commerce\Enums\SupplierStatus;

class Detail extends Component
{
    public CommerceSupplier $commerceSupplier;

    public string $activeTab = 'overview';

    // Delete state
    public bool $showDeleteModal = false;

    public function mount(CommerceSupplier $commerceSupplier): void
    {
        $user = Auth::user();
        abort_unless($commerceSupplier->team_id === $user->currentTeam->id, 403);

        // Redirect to onboarding if still in onboarding status
        if ($commerceSupplier->isOnboarding() && !$commerceSupplier->isManual()) {
            $this->redirect(route('commerce.suppliers.onboarding', $commerceSupplier));
            return;
        }

        $this->commerceSupplier = $commerceSupplier;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function openDeleteModal(): void
    {
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
    }

    public function deleteSupplier(): void
    {
        $this->commerceSupplier->fieldMappings()->delete();
        $this->commerceSupplier->imports()->delete();
        $this->commerceSupplier->articles()->detach();
        $this->commerceSupplier->delete();

        $this->redirect(route('commerce.suppliers.index'));
    }

    public function toggleStatus(): void
    {
        if ($this->commerceSupplier->status === SupplierStatus::Active) {
            $this->commerceSupplier->update(['status' => SupplierStatus::Paused]);
        } elseif ($this->commerceSupplier->status === SupplierStatus::Paused) {
            $this->commerceSupplier->update(['status' => SupplierStatus::Active]);
        }

        $this->commerceSupplier->refresh();
    }

    public function getWebhookUrlProperty(): string
    {
        return url('/api/commerce/suppliers/ingest/' . $this->commerceSupplier->endpoint_token);
    }

    public function render()
    {
        $this->commerceSupplier->loadCount('articles');

        $fieldMappings = $this->commerceSupplier->fieldMappings()->get();
        $imports = $this->commerceSupplier->imports()->limit(20)->get();
        $articles = $this->commerceSupplier->articles()
            ->orderByDesc('commerce_article_supplier.last_synced_at')
            ->limit(50)
            ->get();

        return view('commerce::livewire.suppliers.detail', [
            'fieldMappings' => $fieldMappings,
            'imports' => $imports,
            'articles' => $articles,
        ])->layout('platform::layouts.app');
    }
}
