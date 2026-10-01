<?php

namespace App\Livewire\Reports;

use App\Models\Category;
use App\Models\StockTransaction;
use App\Models\Supplier;
use App\Services\StockService;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Laporan Inventaris & Mutasi Stok - Berkah Mandiri Inventory')]
class ReportIndex extends Component
{
    public string $activeTab = 'stock'; // 'stock', 'in', 'out'

    // Filter & Analisis Ranking Stok
    public string $stockCategory = '';

    public string $stockStatus = '';

    public string $stockSortBy = 'turnover'; // 'turnover', 'most_sold', 'most_in', 'stock_desc', 'stock_asc', 'name'

    public string $stockVelocity = ''; // '', 'FAST_MOVING', 'MEDIUM_MOVING', 'SLOW_MOVING', 'NON_MOVING'

    public ?string $stockStartDate = null;

    public ?string $stockEndDate = null;

    // Filter Mutasi Transaksi (In / Out)
    public string $startDate = '';

    public string $endDate = '';

    public string $supplierId = '';

    public string $recipient = '';

    public function mount(): void
    {
        $this->startDate = Carbon::today()->startOfMonth()->toDateString();
        $this->endDate = Carbon::today()->toDateString();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function render(StockService $stockService)
    {
        $categories = Category::active()->get();
        $suppliers = Supplier::active()->get();

        // 1. Data Laporan Stok dengan Analisis Mutasi & Ranking
        $stockProducts = collect();
        $stockSummary = [
            'total_products' => 0,
            'fast_moving' => 0,
            'medium_moving' => 0,
            'slow_moving' => 0,
            'non_moving' => 0,
        ];

        if ($this->activeTab === 'stock') {
            $analysis = $stockService->getProductsWithMovementAnalysis(
                startDate: $this->stockStartDate ?: null,
                endDate: $this->stockEndDate ?: null,
                categoryId: $this->stockCategory ? (int) $this->stockCategory : null,
                stockStatus: $this->stockStatus ?: null,
                velocityStatus: $this->stockVelocity ?: null,
                sortBy: $this->stockSortBy ?: 'turnover'
            );

            $stockProducts = $analysis['products'];
            $stockSummary = $analysis['summary'];
        }

        // 2. Data Laporan Barang Masuk
        $stockInTransactions = [];
        if ($this->activeTab === 'in') {
            $stockInTransactions = StockTransaction::in()
                ->with(['supplier', 'creator', 'items.product.unit'])
                ->when($this->startDate, fn ($q) => $q->where('transaction_date', '>=', $this->startDate))
                ->when($this->endDate, fn ($q) => $q->where('transaction_date', '<=', $this->endDate))
                ->when($this->supplierId, fn ($q) => $q->where('supplier_id', $this->supplierId))
                ->orderBy('transaction_date', 'asc')
                ->get();
        }

        // 3. Data Laporan Barang Keluar
        $stockOutTransactions = [];
        if ($this->activeTab === 'out') {
            $stockOutTransactions = StockTransaction::out()
                ->with(['creator', 'items.product.unit'])
                ->when($this->startDate, fn ($q) => $q->where('transaction_date', '>=', $this->startDate))
                ->when($this->endDate, fn ($q) => $q->where('transaction_date', '<=', $this->endDate))
                ->when($this->recipient, fn ($q) => $q->where('recipient', 'like', '%'.$this->recipient.'%'))
                ->orderBy('transaction_date', 'asc')
                ->get();
        }

        return view('livewire.reports.report-index', [
            'categories' => $categories,
            'suppliers' => $suppliers,
            'stockProducts' => $stockProducts,
            'stockSummary' => $stockSummary,
            'stockInTransactions' => $stockInTransactions,
            'stockOutTransactions' => $stockOutTransactions,
        ]);
    }
}
