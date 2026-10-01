<div class="space-y-6 max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Riwayat Mutasi Stok</h1>
            <p class="text-xs text-slate-500 mt-1">Daftar rekaman seluruh transaksi Barang Masuk (Stock In) dan Barang Keluar (Stock Out).</p>
        </div>

        <div class="flex items-center gap-2">
            <a
                href="{{ route('transactions.in') }}"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-sm transition"
            >
                <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                Barang Masuk
            </a>

            <a
                href="{{ route('transactions.out') }}"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs shadow-sm transition"
            >
                <x-heroicon-o-arrow-up-tray class="w-4 h-4" />
                Barang Keluar
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2 shadow-sm">
        <x-heroicon-s-check-circle class="w-4 h-4 text-emerald-600 shrink-0" />
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 bg-white border border-slate-200/80 p-4 rounded-2xl shadow-sm">
        <div class="lg:col-span-2">
            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Cari No Transaksi / Pihak</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                </div>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="SIN-..., penerima, supplier..."
                    class="w-full pl-9 pr-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs placeholder-slate-400 focus:outline-none focus:bg-white focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition"
                >
            </div>
        </div>

        <div>
            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Jenis Transaksi</label>
            <select
                wire:model.live="typeFilter"
                class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs focus:outline-none focus:bg-white focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition"
            >
                <option value="">Semua (IN & OUT)</option>
                <option value="IN">Hanya Barang Masuk (IN)</option>
                <option value="OUT">Hanya Barang Keluar (OUT)</option>
            </select>
        </div>

        <div>
            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Dari Tanggal</label>
            <input
                wire:model.live="startDate"
                type="date"
                class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs focus:outline-none focus:bg-white focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition"
            >
        </div>

        <div>
            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Sampai Tanggal</label>
            <input
                wire:model.live="endDate"
                type="date"
                class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs focus:outline-none focus:bg-white focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition"
            >
        </div>
    </div>

    <div class="bg-white border border-slate-200/80 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-500 bg-slate-50/80 border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4 font-semibold">No. Transaksi</th>
                        <th class="py-3 px-4 font-semibold">Tanggal Fisik</th>
                        <th class="py-3 px-4 font-semibold text-center">Tipe</th>
                        <th class="py-3 px-4 font-semibold">Pihak Terkait</th>
                        <th class="py-3 px-4 font-semibold text-center">Rincian Barang</th>
                        <th class="py-3 px-4 font-semibold">Dicatat Oleh</th>
                        <th class="py-3 px-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $tx)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                            {{ $tx->transaction_no }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">
                            {{ $tx->transaction_date->format('d/m/Y') }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($tx->type === 'IN')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <x-heroicon-s-arrow-down-tray class="w-3 h-3 text-emerald-600" />
                                Masuk (IN)
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                <x-heroicon-s-arrow-up-tray class="w-3 h-3 text-rose-600" />
                                Keluar (OUT)
                            </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 font-medium text-slate-800">
                            @if($tx->type === 'IN')
                                <div class="text-slate-900">{{ $tx->supplier->name ?? '-' }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $tx->supplier->code ?? '' }}</div>
                            @else
                                <div class="text-slate-900">{{ $tx->recipient }}</div>
                                <div class="text-[10px] text-slate-400">Penerima Barang</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-semibold text-[11px] border border-slate-200/60">
                                {{ $tx->items->count() }} jenis barang
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-slate-500">
                            <div class="text-slate-800 font-medium">{{ $tx->creator->name }}</div>
                            <div class="text-[10px] capitalize text-slate-400">{{ $tx->creator->role }}</div>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <a
                                href="{{ route('transactions.show', $tx->id) }}"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold border border-indigo-200/60 transition"
                            >
                                <x-heroicon-o-eye class="w-3.5 h-3.5" />
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            <x-heroicon-o-inbox class="w-8 h-8 text-slate-300 mx-auto mb-2" />
                            Tidak ada data transaksi mutasi yang cocok dengan filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
