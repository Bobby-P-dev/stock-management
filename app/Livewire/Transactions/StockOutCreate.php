<?php

namespace App\Livewire\Transactions;

use App\Livewire\Concerns\HasRemoteScannerPairing;
use App\Models\Product;
use App\Services\ScannerPairingService;
use App\Services\StockService;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Pencatatan Barang Keluar (Stock Out) - Berkah Mandiri Inventory')]
class StockOutCreate extends Component
{
    use HasRemoteScannerPairing;

    public string $transaction_date = '';

    public string $recipient = '';

    public string $note = '';

    /** @var list<array{product_id: int|string, quantity: int}> */
    public array $items = [];

    public function mount(ScannerPairingService $pairingService): void
    {
        $this->transaction_date = Carbon::today()->toDateString();
        $this->addItem();
        $this->initRemoteScannerSession($pairingService);
    }

    public function openPairing(ScannerPairingService $service): void
    {
        $this->startRemotePairing($service, 'OUT');
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

    public function save(StockService $stockService, ?ScannerPairingService $pairingService = null, bool $stayOnPage = false)
    {
        $pairingService ??= app(ScannerPairingService::class);

        $this->validate([
            'transaction_date' => ['required', 'date'],
            'recipient' => ['required', 'string', 'max:150'],
            'note' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ], [
            'recipient.required' => 'Nama penerima barang keluar wajib diisi.',
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

        foreach ($this->items as $idx => $item) {
            $product = Product::find($item['product_id']);
            if ($product && $product->current_stock < (int) $item['quantity']) {
                $errorMsg = "Stok '{$product->name}' tidak cukup! (Tersedia: {$product->current_stock}, Diminta: {$item['quantity']})";
                $this->addError("items.{$idx}.quantity", $errorMsg);
                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => 'Stok Tidak Mencukupi',
                    'text' => $errorMsg,
                    'toast' => false,
                ]);

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

        if (! empty($this->pairingToken)) {
            $pairingService->notifyTransactionCompleted($this->pairingToken, $tx->transaction_no);
        }

        if ($stayOnPage) {
            $this->recipient = '';
            $this->note = '';
            $this->transaction_date = Carbon::today()->toDateString();
            $this->items = [
                ['product_id' => '', 'quantity' => 1],
            ];

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Transaksi Tersimpan!',
                'text' => "Barang Keluar {$tx->transaction_no} berhasil dicatat. Formulir baru siap diinput (kamera HP tetap terhubung).",
                'toast' => false,
            ]);

            return null;
        }

        session()->flash('success', "Transaksi Barang Keluar {$tx->transaction_no} berhasil dicatat.");

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

        if (! $product) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Barang Tidak Ditemukan',
                'text' => "Barcode {$code} tidak terdaftar di master data barang.",
                'toast' => true,
            ]);

            return;
        }

        if (! $product->is_active) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Barang Non-Aktif',
                'text' => "Barang '{$product->name}' berstatus non-aktif di sistem.",
                'toast' => true,
            ]);

            return;
        }

        if ($product->current_stock <= 0) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Stok Habis (0)',
                'text' => "Stok '{$product->name}' saat ini 0 {$product->unit?->symbol}. Barang tidak dapat dikeluarkan.",
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
            $currentQty = (int) $this->items[$existingIndex]['quantity'];
            if ($currentQty + 1 > $product->current_stock) {
                $this->dispatch('swal', [
                    'icon' => 'warning',
                    'title' => 'Batas Stok Tercapai',
                    'text' => "Kuantitas tidak dapat ditambah lagi. Maksimal stok tersedia: {$product->current_stock} {$product->unit?->symbol}.",
                    'toast' => true,
                ]);

                return;
            }

            $this->items[$existingIndex]['quantity'] = $currentQty + 1;
            $newQty = $this->items[$existingIndex]['quantity'];
            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Kuantitas Bertambah (+1)',
                'text' => "{$product->name} (Total: {$newQty} {$product->unit?->symbol})",
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
                ];
            }

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Barang Ditambahkan',
                'text' => "{$product->name} masuk ke rincian barang keluar.",
                'toast' => true,
            ]);
        }
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
