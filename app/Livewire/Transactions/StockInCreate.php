<?php

namespace App\Livewire\Transactions;

use App\Livewire\Concerns\HasRemoteScannerPairing;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Services\ScannerPairingService;
use App\Services\StockService;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Pencatatan Barang Masuk (Stock In) - Berkah Mandiri Inventory')]
class StockInCreate extends Component
{
    use HasRemoteScannerPairing;

    public string $transaction_date = '';

    public ?int $supplier_id = null;

    public string $note = '';

    /** @var list<array{product_id: int|string, quantity: int, unit_price: float|int|string}> */
    public array $items = [];

    // Quick Product Registration (Just-In-Time)
    public bool $showQuickProductModal = false;

    public string $quickBarcode = '';

    public string $quickSku = '';

    public string $quickName = '';

    public ?int $quickCategoryId = null;

    public ?int $quickUnitId = null;

    public int $quickMinStock = 5;

    public function mount(ScannerPairingService $pairingService): void
    {
        $this->transaction_date = Carbon::today()->toDateString();
        $this->addItem();
        $this->initRemoteScannerSession($pairingService);
    }

    public function openPairing(ScannerPairingService $service): void
    {
        $this->startRemotePairing($service, 'IN');
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

    public function save(StockService $stockService, ?ScannerPairingService $pairingService = null, bool $stayOnPage = false)
    {
        $pairingService ??= app(ScannerPairingService::class);

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
            $errorMsg = 'Terdapat barang duplikat di formulir. Harap gabungkan kuantitas untuk barang yang sama.';
            $this->addError('items', $errorMsg);
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Barang Duplikat',
                'text' => $errorMsg,
                'toast' => false,
            ]);

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

        if (! empty($this->pairingToken)) {
            $pairingService->notifyTransactionCompleted($this->pairingToken, $tx->transaction_no);
        }

        if ($stayOnPage) {
            $this->supplier_id = null;
            $this->note = '';
            $this->transaction_date = Carbon::today()->toDateString();
            $this->items = [
                ['product_id' => '', 'quantity' => 1, 'unit_price' => ''],
            ];

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Transaksi Tersimpan!',
                'text' => "Barang Masuk {$tx->transaction_no} berhasil dicatat. Formulir baru siap diinput (kamera HP tetap terhubung).",
                'toast' => false,
            ]);

            return null;
        }

        session()->flash('success', "Transaksi Barang Masuk {$tx->transaction_no} berhasil dicatat.");

        return $this->redirect(route('transactions.show', $tx->id), navigate: true);
    }

    public function saveAndCreateNew(StockService $stockService, ScannerPairingService $pairingService): void
    {
        $this->save($stockService, $pairingService, stayOnPage: true);
    }

    public function handleBarcodeScan(string $barcode): void
    {
        $code = trim($barcode);
        if ($code === '') {
            return;
        }

        $product = Product::where('barcode', $code)->orWhere('sku', $code)->first();

        if ($product) {
            if (! $product->is_active) {
                $this->dispatch('swal', [
                    'icon' => 'warning',
                    'title' => 'Barang Non-Aktif',
                    'text' => "Barang '{$product->name}' berstatus non-aktif di sistem.",
                    'toast' => true,
                ]);

                return;
            }

            // Check if already in items list (Combined Mode 1 + Mode 2)
            $existingIndex = null;
            foreach ($this->items as $idx => $item) {
                if ((string) $item['product_id'] === (string) $product->id) {
                    $existingIndex = $idx;
                    break;
                }
            }

            if ($existingIndex !== null) {
                $this->items[$existingIndex]['quantity'] = (int) $this->items[$existingIndex]['quantity'] + 1;
                $newQty = $this->items[$existingIndex]['quantity'];
                $this->dispatch('swal', [
                    'icon' => 'success',
                    'title' => 'Kuantitas Bertambah (+1)',
                    'text' => "{$product->name} (Total: {$newQty} unit)",
                    'toast' => true,
                ]);
            } else {
                // Check if the only existing row is empty
                if (count($this->items) === 1 && empty($this->items[0]['product_id'])) {
                    $this->items[0]['product_id'] = $product->id;
                    $this->items[0]['quantity'] = 1;
                } else {
                    $this->items[] = [
                        'product_id' => $product->id,
                        'quantity' => 1,
                        'unit_price' => '',
                    ];
                }

                $this->dispatch('swal', [
                    'icon' => 'success',
                    'title' => 'Barang Ditambahkan',
                    'text' => "{$product->name} masuk ke rincian barang masuk.",
                    'toast' => true,
                ]);
            }
        } else {
            // Product not found -> Open Quick Master Data Registration
            $this->quickBarcode = $code;
            $this->quickSku = 'PRD-'.strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $code), -6));
            $this->quickName = '';
            $this->quickCategoryId = null;
            $this->quickUnitId = null;
            $this->quickMinStock = 5;
            $this->showQuickProductModal = true;

            $this->dispatch('swal', [
                'icon' => 'info',
                'title' => 'Barang Belum Terdaftar',
                'text' => "Barcode {$code} belum ada di master data. Silakan lengkapi info barang baru.",
                'toast' => true,
            ]);
        }
    }

    public function closeQuickProductModal(): void
    {
        $this->showQuickProductModal = false;
        $this->resetValidation([
            'quickSku', 'quickBarcode', 'quickName', 'quickCategoryId', 'quickUnitId', 'quickMinStock',
        ]);
    }

    public function saveQuickProduct(): void
    {
        $this->validate([
            'quickSku' => ['required', 'string', 'max:50', 'unique:products,sku'],
            'quickBarcode' => ['nullable', 'string', 'max:100', 'unique:products,barcode'],
            'quickName' => ['required', 'string', 'max:200'],
            'quickCategoryId' => ['required', 'exists:categories,id'],
            'quickUnitId' => ['required', 'exists:units,id'],
            'quickMinStock' => ['required', 'integer', 'min:0'],
        ], [
            'quickSku.required' => 'Kode SKU wajib diisi.',
            'quickSku.unique' => 'Kode SKU sudah terdaftar.',
            'quickBarcode.unique' => 'Barcode sudah terdaftar.',
            'quickName.required' => 'Nama barang wajib diisi.',
            'quickCategoryId.required' => 'Kategori wajib dipilih.',
            'quickUnitId.required' => 'Satuan wajib dipilih.',
        ]);

        $product = Product::create([
            'sku' => $this->quickSku,
            'barcode' => ! empty($this->quickBarcode) ? trim($this->quickBarcode) : null,
            'name' => $this->quickName,
            'category_id' => $this->quickCategoryId,
            'unit_id' => $this->quickUnitId,
            'minimum_stock' => $this->quickMinStock,
            'current_stock' => 0,
            'is_active' => true,
        ]);

        // Auto-add to items
        if (count($this->items) === 1 && empty($this->items[0]['product_id'])) {
            $this->items[0]['product_id'] = $product->id;
            $this->items[0]['quantity'] = 1;
        } else {
            $this->items[] = [
                'product_id' => $product->id,
                'quantity' => 1,
                'unit_price' => '',
            ];
        }

        $this->showQuickProductModal = false;
        $this->resetValidation();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Master Barang Didaftarkan',
            'text' => "Barang '{$product->name}' berhasil dibuat dan langsung masuk ke transaksi.",
            'toast' => true,
        ]);
    }

    public function render()
    {
        $suppliers = Supplier::active()->get();
        $products = Product::active()->with(['category', 'unit'])->get();
        $categories = Category::active()->get();
        $units = Unit::active()->get();

        return view('livewire.transactions.stock-in-create', [
            'suppliers' => $suppliers,
            'products' => $products,
            'categories' => $categories,
            'units' => $units,
        ]);
    }
}
