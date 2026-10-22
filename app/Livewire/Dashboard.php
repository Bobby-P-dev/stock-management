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
        if (! auth()->check() || ! auth()->user()->is_active) {
            abort(403, 'Anda tidak memiliki akses untuk menjalankan rekonsiliasi stok.');
        }

        try {
            $products = Product::active()->get();
            $this->auditResults = [];
            $mismatchCount = 0;

            foreach ($products as $prod) {
                $rec = $stockService->reconcileStock($prod->id);
                $this->auditResults[] = [
                    'sku' => $prod->sku,
                    'name' => $prod->name,
                    'actual' => $rec['actual_stock'],
                    'calculated' => $rec['calculated_stock'],
                    'is_matched' => $rec['is_matched'],
                ];

                if (! $rec['is_matched']) {
                    $mismatchCount++;
                }
            }

            $this->hasAudited = true;

            if ($mismatchCount > 0) {
                $this->dispatch('swal', [
                    'icon' => 'warning',
                    'title' => 'Ditemukan Selisih Stok',
                    'text' => "Terdapat {$mismatchCount} barang yang memiliki selisih antara stok tercatat dan riwayat mutasi.",
                    'toast' => false,
                    'variant' => 'amber',
                ]);
            } else {
                $this->dispatch('swal', [
                    'icon' => 'success',
                    'title' => 'Rekonsiliasi Selesai',
                    'text' => 'Seluruh saldo stok barang aktif sinkron dengan riwayat mutasi.',
                    'toast' => true,
                ]);
            }
        } catch (\Throwable $e) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Gagal Menjalankan Rekonsiliasi',
                'text' => 'Terjadi kendala saat memeriksa riwayat mutasi stok.',
                'toast' => false,
                'variant' => 'rose',
            ]);
        }
    }

    public function render(StockService $stockService)
    {
        $stats = $stockService->getDashboardStats();

        return view('livewire.dashboard', [
            'stats' => $stats,
        ]);
    }
}
