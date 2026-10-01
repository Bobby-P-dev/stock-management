<?php

namespace App\Livewire\Opnames;

use App\Models\StockOpname;
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

    public function render()
    {
        return view('livewire.opnames.stock-opname-show', [
            'opname' => $this->opname,
        ]);
    }
}
