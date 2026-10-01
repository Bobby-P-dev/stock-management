<?php

namespace App\Livewire\Transactions;

use App\Models\Product;
use App\Services\StockService;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Pencatatan Barang Keluar (Stock Out) - Berkah Mandiri Inventory')]
class StockOutCreate extends Component
{
    public string $transaction_date = '';

    public string $recipient = '';

    public string $note = '';

    /** @var list<array{product_id: int|string, quantity: int}> */
    public array $items = [];

    public function mount(): void
    {
        $this->transaction_date = Carbon::today()->toDateString();
        $this->addItem();
    }

    public function addItem(): void
    {
        $this->items[] = [
            'product_id' => '',
            'quantity' => 1,
        ];
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) > 1) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
        }
    }

    public function save(StockService $stockService)
    {
        $this->validate([
            'transaction_date' => ['required', 'date'],
            'recipient' => ['required', 'string', 'max:150'],
            'note' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ], [
            'recipient.required' => 'Penerima / divisi tujuan barang keluar wajib diisi.',
            'items.*.product_id.required' => 'Barang wajib dipilih.',
            'items.*.quantity.min' => 'Kuantitas minimal 1 unit.',
        ]);

        // Cek duplikasi produk di form
        $productIds = array_column($this->items, 'product_id');
        if (count($productIds) !== count(array_unique($productIds))) {
            $this->addError('items', 'Terdapat barang duplikat di formulir. Harap gabungkan kuantitas untuk barang yang sama.');

            return null;
        }

        // Verifikasi ketersediaan stok di sisi client/Livewire sebelum dieksekusi oleh service
        foreach ($this->items as $idx => $item) {
            $product = Product::find($item['product_id']);
            if ($product && $product->current_stock < (int) $item['quantity']) {
                $this->addError("items.{$idx}.quantity", "Stok '{$product->name}' tidak cukup! (Tersedia: {$product->current_stock}, Diminta: {$item['quantity']})");

                return null;
            }
        }

        $formattedItems = array_map(function ($item) {
            return [
                'product_id' => (int) $item['product_id'],
                'quantity' => (int) $item['quantity'],
            ];
        }, $this->items);

        $tx = $stockService->recordStockOut(
            [
                'transaction_date' => $this->transaction_date,
                'recipient' => $this->recipient,
                'note' => $this->note,
            ],
            $formattedItems,
            auth()->id()
        );

        session()->flash('success', "Transaksi Barang Keluar {$tx->transaction_no} berhasil dicatat.");

        return $this->redirect(route('transactions.show', $tx->id), navigate: true);
    }

    public function render()
    {
        $products = Product::active()
            ->with(['category', 'unit'])
            ->get();

        return view('livewire.transactions.stock-out-create', [
            'products' => $products,
        ]);
    }
}
