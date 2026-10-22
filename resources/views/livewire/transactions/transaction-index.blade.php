<div class="space-y-6 min-w-0">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200/80">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200/60 flex items-center justify-center text-blue-600 shrink-0 shadow-2xs mt-0.5">
                <x-heroicon-o-arrows-right-left class="w-5 h-5 stroke-[2]" />
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Riwayat Mutasi Stok</h1>
                <p class="text-xs text-slate-500 mt-1">Daftar rekaman seluruh transaksi Barang Masuk (Stock In) dan Barang Keluar (Stock Out).</p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 self-start sm:self-auto">
            <x-ui.button
                href="{{ route('transactions.in') }}"
                variant="success"
                size="sm"
            >
                <x-slot:icon>
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4 mr-1.5 stroke-[2]" />
                </x-slot:icon>
                Barang Masuk
            </x-ui.button>

            <x-ui.button
                href="{{ route('transactions.out') }}"
                variant="danger"
                size="sm"
            >
                <x-slot:icon>
                    <x-heroicon-o-arrow-up-tray class="w-4 h-4 mr-1.5 stroke-[2]" />
                </x-slot:icon>
                Barang Keluar
            </x-ui.button>
        </div>
    </div>

    @if(session('success'))
    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2.5 shadow-2xs">
        <x-heroicon-s-check-circle class="w-4 h-4 text-emerald-600 shrink-0" />
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Filter Toolbar -->
    <div class="bg-white border border-slate-200/80 p-4 sm:p-5 rounded-2xl shadow-2xs space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-end">
            <div class="lg:col-span-5">
                <x-ui.input
                    label="Pencarian Transaksi"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari no. transaksi, pihak, catatan..."
                    :showClear="$search !== ''"
                    clearAction="$set('search', '')"
                >
                    <x-slot:icon>
                        <x-heroicon-o-magnifying-glass class="w-4 h-4 text-slate-400" />
                    </x-slot:icon>
                </x-ui.input>
            </div>

            <div class="lg:col-span-3">
                <x-searchable-select
                    label="Jenis Transaksi"
                    wire:model.live="typeFilter"
                    :options="[
                        ['value' => '', 'label' => 'Semua (IN & OUT)'],
                        ['value' => 'IN', 'label' => 'Hanya Barang Masuk (IN)'],
                        ['value' => 'OUT', 'label' => 'Hanya Barang Keluar (OUT)']
                    ]"
                    placeholder="Semua (IN & OUT)"
                    searchPlaceholder="Cari jenis transaksi..."
                />
            </div>

            <div class="lg:col-span-2">
                <x-ui.input
                    label="Dari Tanggal"
                    wire:model.live="startDate"
                    type="date"
                    :showClear="$startDate !== ''"
                    clearAction="$set('startDate', '')"
                />
            </div>

            <div class="lg:col-span-2">
                <x-ui.input
                    label="Sampai Tanggal"
                    wire:model.live="endDate"
                    type="date"
                    :showClear="$endDate !== ''"
                    clearAction="$set('endDate', '')"
                />
            </div>
        </div>

        @if($search !== '' || $typeFilter !== '' || $startDate !== '' || $endDate !== '')
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2 text-xs">
                <span class="text-slate-500 text-[11px]">Menampilkan hasil filter transaksi mutasi</span>
                <x-ui.button
                    variant="ghost"
                    size="xs"
                    wire:click="resetFilters"
                    class="text-blue-600 hover:text-blue-700 hover:bg-blue-50/70"
                >
                    <x-slot:icon>
                        <x-heroicon-o-arrow-path class="w-3.5 h-3.5 mr-1" />
                    </x-slot:icon>
                    Reset Filter
                </x-ui.button>
            </div>
        @endif
    </div>

    <!-- Data Table -->
    <x-ui.table loadingTarget="search,typeFilter,startDate,endDate,gotoPage,previousPage,nextPage">
        <x-slot:head>
            <x-ui.th class="w-44 whitespace-nowrap">No. Transaksi</x-ui.th>
            <x-ui.th class="w-32 whitespace-nowrap">Tanggal Fisik</x-ui.th>
            <x-ui.th align="center" class="w-28 whitespace-nowrap">Tipe</x-ui.th>
            <x-ui.th class="min-w-[200px]">Pihak Terkait</x-ui.th>
            <x-ui.th align="center" class="w-32 whitespace-nowrap">Rincian Barang</x-ui.th>
            <x-ui.th class="w-40 whitespace-nowrap">Dicatat Oleh</x-ui.th>
            <x-ui.th align="right" class="w-24 whitespace-nowrap">Aksi</x-ui.th>
        </x-slot:head>

        @forelse($transactions as $tx)
        <tr wire:key="tx-row-{{ $tx->id }}" class="hover:bg-slate-50/60 transition">
            <x-ui.td>
                <span class="font-mono font-bold text-slate-900 tracking-tight">
                    {{ $tx->transaction_no }}
                </span>
                @if($tx->note)
                    <p class="text-[11px] text-slate-400 truncate max-w-xs mt-0.5" title="{{ $tx->note }}">
                        {{ $tx->note }}
                    </p>
                @endif
            </x-ui.td>

            <x-ui.td class="text-slate-600 text-xs">
                {{ $tx->transaction_date->format('d/m/Y') }}
            </x-ui.td>

            <x-ui.td align="center">
                @if($tx->type === 'IN')
                    <x-ui.badge variant="success" size="md">
                        <x-heroicon-s-arrow-down-tray class="w-3 h-3 mr-1" />
                        Masuk (IN)
                    </x-ui.badge>
                @else
                    <x-ui.badge variant="danger" size="md">
                        <x-heroicon-s-arrow-up-tray class="w-3 h-3 mr-1" />
                        Keluar (OUT)
                    </x-ui.badge>
                @endif
            </x-ui.td>

            <x-ui.td>
                @if($tx->type === 'IN')
                    <div class="text-slate-900 font-semibold">{{ $tx->supplier->name ?? '-' }}</div>
                    <div class="text-[11px] text-slate-400 font-mono">{{ $tx->supplier->code ?? 'Supplier' }}</div>
                @else
                    <div class="text-slate-900 font-semibold">{{ $tx->recipient ?? '-' }}</div>
                    <div class="text-[11px] text-slate-400">Penerima Barang</div>
                @endif
            </x-ui.td>

            <x-ui.td align="center">
                <span class="inline-flex items-center px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 font-medium text-[11px] border border-slate-200/60">
                    {{ $tx->items->count() }} jenis barang
                </span>
            </x-ui.td>

            <x-ui.td>
                <div class="text-slate-800 font-medium">{{ $tx->creator->name }}</div>
                <div class="text-[10px] capitalize text-slate-400">{{ $tx->creator->role }}</div>
            </x-ui.td>

            <x-ui.td align="right">
                <x-ui.button
                    href="{{ route('transactions.show', $tx->id) }}"
                    variant="outline"
                    size="xs"
                >
                    <x-slot:icon>
                        <x-heroicon-o-eye class="w-3.5 h-3.5 mr-1 text-slate-500" />
                    </x-slot:icon>
                    Detail
                </x-ui.button>
            </x-ui.td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="py-14 text-center text-slate-400">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                    <x-heroicon-o-inbox class="w-6 h-6 stroke-[1.5]" />
                </div>
                <p class="font-medium text-slate-700 text-xs">Tidak ada data transaksi mutasi</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Tidak ditemukan transaksi yang cocok dengan kriteria pencarian dan filter Anda.</p>
            </td>
        </tr>
        @endforelse

        @if($transactions->hasPages())
        <x-slot:footer>
            {{ $transactions->links() }}
        </x-slot:footer>
        @endif
    </x-ui.table>
</div>
