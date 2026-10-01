<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\StockService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard - Berkah Mandiri Inventory')]
class Dashboard extends Component
{
    /** @var array<int, array{sku: string, name: string, actual: int, calculated: int, is_matched: bool}> */
    public array $auditResults = [];

    public bool $hasAudited = false;

    public function runAudit(StockService $stockService): void
    {
        $products = Product::active()->get();
        $this->auditResults = [];

        foreach ($products as $prod) {
            $rec = $stockService->reconcileStock($prod->id);
            $this->auditResults[] = [
                'sku' => $prod->sku,
                'name' => $prod->name,
                'actual' => $rec['actual_stock'],
                'calculated' => $rec['calculated_stock'],
                'is_matched' => $rec['is_matched'],
            ];
        }

        $this->hasAudited = true;
    }

    public function render(StockService $stockService)
    {
        $stats = $stockService->getDashboardStats();

        return view('livewire.dashboard', [
            'stats' => $stats,
        ]);
    }
}
