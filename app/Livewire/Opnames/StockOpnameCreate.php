<?php

namespace App\Livewire\Opnames;

use App\Models\Product;
use App\Services\StockService;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Catat Stock Opname - Berkah Mandiri Inventory')]
class StockOpnameCreate extends Component
{
    public string $opname_date = '';

    public string $note = '';

    /**
     * @var list<array{
     *     product_id: int|string,
     *     system_stock: int,
     *     physical_stock: int|string,
     *     difference: int,
     *     reason: string,
     *     item_notes: string
     * }>
     */
    public array $items = [];

    public function mount(): void
    {
        $this->opname_date = Carbon::today()->toDateString();
        $this->addItem();
    }

    public function addItem(): void
    {
        $this->items[] = [
            'product_id' => '',
            'system_stock' => 0,
            'physical_stock' => 0,
            'difference' => 0,
            'reason' => 'SESUAI',
            'item_notes' => '',
        ];
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) > 1) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
        }
    }

    public function updatedItems($value, $key): void
    {
        $parts = explode('.', $key);
        if (count($parts) === 2) {
            $index = (int) $parts[0];
            $field = $parts[1];

            if ($field === 'product_id' && ! empty($value)) {
                $product = Product::find($value);
                if ($product) {
                    $this->items[$index]['system_stock'] = $product->current_stock;
                    $this->items[$index]['physical_stock'] = $product->current_stock;
                    $this->items[$index]['difference'] = 0;
                    $this->items[$index]['reason'] = 'SESUAI';
                }
            }

            if ($field === 'physical_stock') {
                $physical = is_numeric($value) ? (int) $value : 0;
                $system = (int) ($this->items[$index]['system_stock'] ?? 0);
                $diff = $physical - $system;
                $this->items[$index]['difference'] = $diff;

                if ($diff === 0) {
                    $this->items[$index]['reason'] = 'SESUAI';
                } elseif ($this->items[$index]['reason'] === 'SESUAI') {
                    $this->items[$index]['reason'] = $diff < 0 ? 'RUSAK' : 'SELISIH_HITUNG';
                }
            }
        }
    }

    public function save(StockService $stockService)
    {
        $this->validate([
            'opname_date' => 'required|date',
            'note' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.physical_stock' => 'required|integer|min:0',
            'items.*.reason' => 'nullable|string|max:50',
            'items.*.item_notes' => 'nullable|string|max:255',
        ], [
            'items.min' => 'Minimal harus memeriksa 1 barang.',
            'items.*.product_id.required' => 'Pilih barang untuk diperiksa.',
            'items.*.physical_stock.required' => 'Kuantitas fisik wajib diisi.',
            'items.*.physical_stock.min' => 'Kuantitas fisik tidak boleh kurang dari 0.',
        ]);

        $productIds = array_column($this->items, 'product_id');
        if (count($productIds) !== count(array_unique($productIds))) {
            throw ValidationException::withMessages([
                'items' => 'Terdapat produk ganda dalam daftar pemeriksaan opname.',
            ]);
        }

        $formattedItems = array_map(function ($it) {
            return [
                'product_id' => (int) $it['product_id'],
                'physical_stock' => (int) $it['physical_stock'],
                'reason' => $it['reason'],
                'item_notes' => $it['item_notes'],
            ];
        }, $this->items);

        $opname = $stockService->recordStockOpname(
            [
                'opname_date' => $this->opname_date,
                'note' => $this->note,
            ],
            $formattedItems,
            auth()->id()
        );

        session()->flash('success', "Stock Opname {$opname->opname_no} berhasil dicatat dan saldo stok telah disesuaikan!");

        return redirect()->route('opnames.show', $opname->id);
    }

    public function render()
    {
        $products = Product::with(['unit', 'category'])->active()->orderBy('name')->get();

        return view('livewire.opnames.stock-opname-create', [
            'products' => $products,
        ]);
    }
}
