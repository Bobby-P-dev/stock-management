<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\StockService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Kartu Stok Barang (Stock Card) - Berkah Mandiri Inventory')]
class StockCard extends Component
{
    public ?int $productId = null;

    public string $startDate = '';

    public string $endDate = '';

    public function mount(?int $productId = null): void
    {
        if ($productId) {
            $this->productId = $productId;
        } else {
            // Default ke produk pertama yang memiliki transaksi jika ada
            $firstProduct = Product::active()->first();
            $this->productId = $firstProduct?->id;
        }
    }

    public function render(StockService $stockService)
    {
        $products = Product::active()->with(['category', 'unit'])->get();

        $stockCardData = null;
        if ($this->productId) {
            $stockCardData = $stockService->getStockCard(
                $this->productId,
                $this->startDate ?: null,
                $this->endDate ?: null
            );
        }

        return view('livewire.stock-card', [
            'products' => $products,
            'stockCardData' => $stockCardData,
        ]);
    }
}
