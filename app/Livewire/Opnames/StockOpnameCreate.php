<?php

namespace App\Livewire\Opnames;

use App\Livewire\Concerns\HasRemoteScannerPairing;
use App\Models\Product;
use App\Services\ScannerPairingService;
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
    use HasRemoteScannerPairing;

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

    public function mount(ScannerPairingService $pairingService): void
    {
        $this->opname_date = Carbon::today()->toDateString();
        $this->addItem();
        $this->initRemoteScannerSession($pairingService);
    }

    public function openPairing(ScannerPairingService $service): void
    {
        $this->startRemotePairing($service, 'OPNAME');
    }

    public function handleBarcodeScan(string $barcode): void
    {
        $code = trim(preg_replace('/[\r\n\t]/', '', $barcode));
        if ($code === '') {
            return;
        }

        $product = Product::where('barcode', $code)->orWhere('sku', $code)->first();

        if (! $product) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Barang Tidak Ditemukan',
                'text' => "Barcode {$code} tidak terdaftar di master data barang.",
                'toast' => true,
            ]);

            return;
        }

        // Check if already in items
        $existingIndex = null;
        foreach ($this->items as $idx => $item) {
            if ((string) $item['product_id'] === (string) $product->id) {
                $existingIndex = $idx;
                break;
            }
        }

        if ($existingIndex !== null) {
            $newPhysical = (int) $this->items[$existingIndex]['physical_stock'] + 1;
            $this->items[$existingIndex]['physical_stock'] = $newPhysical;
            $sys = (int) $this->items[$existingIndex]['system_stock'];
            $diff = $newPhysical - $sys;
            $this->items[$existingIndex]['difference'] = $diff;

            if ($diff === 0) {
                $this->items[$existingIndex]['reason'] = 'SESUAI';
            } elseif ($this->items[$existingIndex]['reason'] === 'SESUAI') {
                $this->items[$existingIndex]['reason'] = $diff < 0 ? 'RUSAK' : 'SELISIH_HITUNG';
            }

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Fisik Bertambah (+1)',
                'text' => "{$product->name} (Fisik: {$newPhysical} unit)",
                'toast' => true,
            ]);
        } else {
            $diff = 1 - (int) $product->current_stock;
            $reason = ($diff === 0) ? 'SESUAI' : ($diff < 0 ? 'RUSAK' : 'SELISIH_HITUNG');

            $row = [
                'product_id' => $product->id,
                'system_stock' => (int) $product->current_stock,
                'physical_stock' => 1,
                'difference' => $diff,
                'reason' => $reason,
                'item_notes' => '',
            ];

            if (count($this->items) === 1 && empty($this->items[0]['product_id'])) {
                $this->items[0] = $row;
            } else {
                $this->items[] = $row;
            }

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Barang Masuk Opname',
                'text' => "{$product->name} berhasil ditambahkan.",
                'toast' => true,
            ]);
        }
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

    public function save(StockService $stockService, ?ScannerPairingService $pairingService = null)
    {
        $pairingService ??= app(ScannerPairingService::class);
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
            $errorMsg = 'Terdapat produk ganda dalam daftar pemeriksaan opname.';
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Barang Duplikat',
                'text' => $errorMsg,
                'toast' => false,
            ]);
            throw ValidationException::withMessages([
                'items' => $errorMsg,
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

        if (! empty($this->pairingToken)) {
            $pairingService->notifyTransactionCompleted($this->pairingToken, $opname->opname_no);
        }

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
