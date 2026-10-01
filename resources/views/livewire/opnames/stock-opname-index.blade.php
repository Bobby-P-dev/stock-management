<div class="space-y-6 max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    Audit Fisik Gudang
                </span>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Stock Opname & Penyesuaian Stok</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1">Rekapitulasi pemeriksaan fisik riil inventaris barang terhadap pencatatan sistem secara berkala.</p>
        </div>

        <a
            href="{{ route('opnames.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-sm transition self-start sm:self-auto"
        >
            <x-heroicon-o-plus class="w-4 h-4" />
            Catat Stock Opname Baru
        </a>
    </div>

    @if(session('success'))
    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2 shadow-sm">
        <x-heroicon-s-check-circle class="w-4 h-4 text-emerald-600 shrink-0" />
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-white border border-slate-200/80 p-4 rounded-2xl shadow-sm">
        <div>
            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Cari Dokumen / Catatan</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                </div>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="SOP-..., nama petugas, catatan..."
                    class="w-full pl-9 pr-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs placeholder-slate-400 focus:outline-none focus:bg-white focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition"
                >
            </div>
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
                        <th class="py-3 px-4 font-semibold">No. Dokumen Opname</th>
                        <th class="py-3 px-4 font-semibold">Tanggal Fisik</th>
                        <th class="py-3 px-4 font-semibold text-center">Jumlah Barang</th>
                        <th class="py-3 px-4 font-semibold text-center">Akumulasi Selisih</th>
                        <th class="py-3 px-4 font-semibold">Catatan / Berita Acara</th>
                        <th class="py-3 px-4 font-semibold">Petugas Pemeriksa</th>
                        <th class="py-3 px-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($opnames as $op)
                    @php
                        $totalDiff = $op->items->sum('difference');
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                            {{ $op->opname_no }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">
                            {{ $op->opname_date->format('d/m/Y') }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-700 font-semibold text-[11px] border border-slate-200/60">
                                {{ $op->items->count() }} jenis barang
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($totalDiff === 0)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Cocok (0)
                            </span>
                            @elseif($totalDiff < 0)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                Kurang ({{ $totalDiff }})
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                Lebih (+{{ $totalDiff }})
                            </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-slate-600 max-w-xs truncate">
                            {{ $op->note ?: '-' }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-500">
                            <div class="text-slate-800 font-medium">{{ $op->creator->name }}</div>
                            <div class="text-[10px] capitalize text-slate-400">{{ $op->creator->role }}</div>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <a
                                href="{{ route('opnames.show', $op->id) }}"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold border border-indigo-200/60 transition"
                            >
                                <x-heroicon-o-eye class="w-3.5 h-3.5" />
                                Rincian
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            <x-heroicon-o-inbox class="w-8 h-8 text-slate-300 mx-auto mb-2" />
                            Belum ada riwayat dokumen Stock Opname yang tercatat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $opnames->links() }}
        </div>
    </div>
</div>

