<?php

namespace App\Livewire\Transactions;

use App\Models\Product;
use App\Models\Supplier;
use App\Services\StockService;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Pencatatan Barang Masuk (Stock In) - Berkah Mandiri Inventory')]
class StockInCreate extends Component
{
    public string $transaction_date = '';

    public ?int $supplier_id = null;

    public string $note = '';

    /** @var list<array{product_id: int|string, quantity: int, unit_price: float|int|string}> */
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
            'unit_price' => '',
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
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'note' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
        ], [
            'supplier_id.required' => 'Supplier wajib dipilih.',
            'items.*.product_id.required' => 'Barang wajib dipilih.',
            'items.*.quantity.min' => 'Kuantitas minimal 1 unit.',
        ]);

        $productIds = array_column($this->items, 'product_id');
        if (count($productIds) !== count(array_unique($productIds))) {
            $this->addError('items', 'Terdapat barang duplikat di formulir. Harap gabungkan kuantitas untuk barang yang sama.');

            return null;
        }

        $formattedItems = array_map(function ($item) {
            return [
                'product_id' => (int) $item['product_id'],
                'quantity' => (int) $item['quantity'],
                'unit_price' => ! empty($item['unit_price']) ? (float) $item['unit_price'] : null,
            ];
        }, $this->items);

        $tx = $stockService->recordStockIn(
            [
                'transaction_date' => $this->transaction_date,
                'supplier_id' => $this->supplier_id,
                'note' => $this->note,
            ],
            $formattedItems,
            auth()->id()
        );

        session()->flash('success', "Transaksi Barang Masuk {$tx->transaction_no} berhasil dicatat.");

        return $this->redirect(route('transactions.show', $tx->id), navigate: true);
    }

    public function render()
    {
        $suppliers = Supplier::active()->get();
        $products = Product::active()->with(['category', 'unit'])->get();

        return view('livewire.transactions.stock-in-create', [
            'suppliers' => $suppliers,
            'products' => $products,
        ]);
    }
}
