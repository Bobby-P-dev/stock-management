<?php

namespace App\Livewire\Transactions;

use App\Models\StockTransaction;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Riwayat Transaksi Mutasi Stok - Berkah Mandiri Inventory')]
class TransactionIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $typeFilter = '';

    public string $startDate = '';

    public string $endDate = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStartDate(): void
    {
        $this->resetPage();
    }

    public function updatingEndDate(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'typeFilter', 'startDate', 'endDate']);
        $this->resetPage();
    }

    public function render()
    {
        $transactions = StockTransaction::with(['creator', 'supplier', 'items.product.unit'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('transaction_no', 'like', '%'.$this->search.'%')
                        ->orWhere('recipient', 'like', '%'.$this->search.'%')
                        ->orWhere('note', 'like', '%'.$this->search.'%')
                        ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', '%'.$this->search.'%'));
                });
            })
            ->when($this->typeFilter, fn ($q) => $q->where('type', $this->typeFilter))
            ->when($this->startDate, fn ($q) => $q->where('transaction_date', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->where('transaction_date', '<=', $this->endDate))
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(12);

        return view('livewire.transactions.transaction-index', [
            'transactions' => $transactions,
        ]);
    }
}
