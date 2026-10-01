<?php

namespace App\Livewire\Transactions;

use App\Models\StockTransaction;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Detail Faktur Transaksi Mutasi - Berkah Mandiri Inventory')]
class TransactionShow extends Component
{
    public StockTransaction $transaction;

    public function mount(StockTransaction $transaction): void
    {
        $this->transaction = $transaction->load(['creator', 'supplier', 'items.product.unit', 'items.product.category']);
    }

    public function render()
    {
        return view('livewire.transactions.transaction-show');
    }
}
