<?php

namespace App\Livewire\Opnames;

use App\Models\StockOpname;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Stock Opname & Penyesuaian Fisik - Berkah Mandiri Inventory')]
class StockOpnameIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $startDate = '';

    public string $endDate = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStartDate(): void
    {
        $this->resetPage();
    }

    public function updatingEndDate(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $opnames = StockOpname::with(['creator', 'items'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('opname_no', 'like', '%'.$this->search.'%')
                        ->orWhere('note', 'like', '%'.$this->search.'%')
                        ->orWhereHas('creator', fn ($u) => $u->where('name', 'like', '%'.$this->search.'%'));
                });
            })
            ->when($this->startDate, fn ($q) => $q->where('opname_date', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->where('opname_date', '<=', $this->endDate))
            ->orderBy('opname_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.opnames.stock-opname-index', [
            'opnames' => $opnames,
        ]);
    }
}
