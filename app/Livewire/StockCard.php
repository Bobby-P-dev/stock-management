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

    public string $productSearch = '';

    public string $startDate = '';

    public string $endDate = '';

    public function mount(?int $productId = null): void
    {
        if ($productId) {
            $this->productId = $productId;
        } else {
            $firstProduct = Product::active()->first();
            $this->productId = $firstProduct?->id;
        }
    }

    public function selectProduct(int $id): void
    {
        $this->productId = $id;
        $this->productSearch = '';
    }

    public function render(StockService $stockService)
    {
        $products = Product::active()
            ->with(['category', 'unit'])
            ->when($this->productSearch, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->productSearch.'%')
                        ->orWhere('sku', 'like', '%'.$this->productSearch.'%');
                });
            })
            ->orderBy('name', 'asc')
            ->limit(5)
            ->get();

        $selectedProduct = $this->productId
            ? Product::with(['category', 'unit'])->find($this->productId)
            : null;

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
            'selectedProduct' => $selectedProduct,
            'stockCardData' => $stockCardData,
        ]);
    }
}
