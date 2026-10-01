<?php

use Livewire\Component;

new class extends Component
{
    public int $stock = 25;
    public string $itemName = 'Laptop Pro 16"';
    public string $statusMessage = 'Sistem siap digunakan.';

    public function increaseStock(): void
    {
        $this->stock++;
        $this->statusMessage = "Stok {$this->itemName} ditambahkan 1 unit pada " . now()->format('H:i:s');
    }

    public function decreaseStock(): void
    {
        if ($this->stock > 0) {
            $this->stock--;
            $this->statusMessage = "Stok {$this->itemName} dikurangi 1 unit pada " . now()->format('H:i:s');
        } else {
            $this->statusMessage = "Stok sudah habis!";
        }
    }

    public function resetStock(): void
    {
        $this->stock = 25;
        $this->statusMessage = "Stok di-reset ke nilai default (25).";
    }
};
?>

<div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-8 max-w-lg mx-auto">
    <div class="flex items-center justify-between pb-6 border-b border-slate-100">
        <div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Livewire & Tailwind Aktif
            </span>
            <h3 class="text-xl font-bold text-slate-900 mt-2">{{ $itemName }}</h3>
            <p class="text-sm text-slate-500">SKU: STK-8829-LP</p>
        </div>
        <div class="text-right">
            <span class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Tersedia</span>
            <div class="text-3xl font-extrabold text-indigo-600 transition-all duration-200">
                {{ $stock }}
            </div>
        </div>
    </div>

    <div class="mt-4 p-3 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-600 flex items-center gap-2">
        <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
        </svg>
        <span class="truncate">{{ $statusMessage }}</span>
    </div>

    <div class="mt-6 flex items-center gap-3">
        <button
            wire:click="decreaseStock"
            type="button"
            class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-medium text-sm rounded-xl transition duration-150 active:scale-95 cursor-pointer disabled:opacity-50"
            {{ $stock === 0 ? 'disabled' : '' }}
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
            </svg>
            Kurang
        </button>

        <button
            wire:click="resetStock"
            type="button"
            class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-sm rounded-xl transition duration-150 active:scale-95 cursor-pointer"
            title="Reset Stok"
        >
            Reset
        </button>

        <button
            wire:click="increaseStock"
            type="button"
            class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-xl shadow-md shadow-indigo-200 transition duration-150 active:scale-95 cursor-pointer"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah
        </button>
    </div>
</div>