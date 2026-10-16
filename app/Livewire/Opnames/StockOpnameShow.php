<?php

namespace App\Livewire\Opnames;

use App\Models\StockOpname;
use App\Services\StockService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Berita Acara Stock Opname - Berkah Mandiri Inventory')]
class StockOpnameShow extends Component
{
    public StockOpname $opname;

    public function mount(StockOpname $opname): void
    {
        $this->opname = $opname->load(['items.product.unit', 'items.product.category', 'creator']);
    }

    public function delete(StockService $stockService)
    {
        $opnameNo = $this->opname->opname_no;
        $stockService->deleteStockOpname($this->opname);

        session()->flash('success', "Dokumen Stock Opname {$opnameNo} berhasil dihapus dan penyesuaian stok telah dibatalkan.");

        return redirect()->route('opnames.index');
    }

    public function render()
    {
        return view('livewire.opnames.stock-opname-show', [
            'opname' => $this->opname,
        ]);
    }
}
