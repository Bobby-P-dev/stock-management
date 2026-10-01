<div class="space-y-6 max-w-7xl mx-auto">
    <div class="rounded-2xl bg-white border border-slate-200/80 p-6 md:p-8 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 mb-3">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Gudang Berkah Mandiri &bull; Status Operasional Normal
                </span>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Selamat Datang, {{ auth()->user()->name }}!
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-xl">
                    Panel kendali persediaan barang gudang. Pantau mutasi masuk dan keluar, cek kartu stok, serta pastikan pencatatan barang selalu rapi.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a
                    href="{{ route('transactions.in') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-sm transition cursor-pointer"
                >
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                    Catat Barang Masuk
                </a>

                <a
                    href="{{ route('transactions.out') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs shadow-sm transition cursor-pointer"
                >
                    <x-heroicon-o-arrow-up-tray class="w-4 h-4" />
                    Catat Barang Keluar
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Total Jenis Barang</span>
                <div class="p-2 rounded-xl bg-indigo-50 text-indigo-600">
                    <x-heroicon-o-archive-box class="w-5 h-5" />
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900">{{ $stats['total_products'] }}</span>
                <span class="text-xs text-slate-500">item aktif</span>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-amber-700">Peringatan Menipis</span>
                <div class="p-2 rounded-xl bg-amber-50 text-amber-600">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-amber-600">{{ $stats['low_stock_count'] }}</span>
                <span class="text-xs text-slate-500">&le; batas minimum</span>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-emerald-700">Barang Masuk Hari Ini</span>
                <div class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                    <x-heroicon-o-arrow-down-tray class="w-5 h-5" />
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-emerald-600">{{ $stats['stock_in_today'] }}</span>
                <span class="text-xs text-slate-500">faktur transaksi</span>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-rose-700">Barang Keluar Hari Ini</span>
                <div class="p-2 rounded-xl bg-rose-50 text-rose-600">
                    <x-heroicon-o-arrow-up-tray class="w-5 h-5" />
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-rose-600">{{ $stats['stock_out_today'] }}</span>
                <span class="text-xs text-slate-500">faktur transaksi</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        Barang Perlu Restock
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar stok di bawah kuantitas minimum</p>
                </div>
                <a href="{{ route('products.index') }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-semibold flex items-center gap-1">
                    Lihat Semua
                    <x-heroicon-o-arrow-right class="w-3.5 h-3.5" />
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-500 border-b border-slate-100">
                            <th class="pb-2 font-semibold">SKU</th>
                            <th class="pb-2 font-semibold">Nama Barang</th>
                            <th class="pb-2 font-semibold text-center">Tersedia</th>
                            <th class="pb-2 font-semibold text-center">Batas Min.</th>
                            <th class="pb-2 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($stats['low_stock_items'] as $item)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-2.5 font-mono font-semibold text-slate-700">{{ $item->sku }}</td>
                            <td class="py-2.5 font-medium text-slate-900">{{ $item->name }}</td>
                            <td class="py-2.5 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $item->current_stock <= 0 ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                    {{ $item->current_stock }} {{ $item->unit->symbol }}
                                </span>
                            </td>
                            <td class="py-2.5 text-center text-slate-500">{{ $item->minimum_stock }} {{ $item->unit->symbol }}</td>
                            <td class="py-2.5 text-right">
                                <a href="{{ route('stock-card', ['productId' => $item->id]) }}" class="text-indigo-600 hover:text-indigo-700 font-semibold text-xs">
                                    Kartu Stok
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400">
                                Seluruh stok barang dalam kondisi aman.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                        Aktivitas Mutasi Terbaru
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">5 transaksi mutasi stok terakhir</p>
                </div>
                <a href="{{ route('transactions.index') }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-semibold flex items-center gap-1">
                    Lihat Semua
                    <x-heroicon-o-arrow-right class="w-3.5 h-3.5" />
                </a>
            </div>

            <div class="space-y-2.5">
                @forelse($stats['recent_transactions'] as $tx)
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100 hover:border-slate-200 transition">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold {{ $tx->type === 'IN' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                            {{ $tx->type }}
                        </span>
                        <div>
                            <div class="text-xs font-bold text-slate-900 font-mono">{{ $tx->transaction_no }}</div>
                            <div class="text-[11px] text-slate-500">
                                {{ $tx->type === 'IN' ? 'Dari: ' . ($tx->supplier->name ?? '-') : 'Ke: ' . ($tx->recipient ?? '-') }} &bull; {{ $tx->transaction_date->format('d/m/Y') }}
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('transactions.show', $tx->id) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-white transition" title="Lihat detail">
                        <x-heroicon-o-chevron-right class="w-4 h-4" />
                    </a>
                </div>
                @empty
                <div class="py-6 text-center text-slate-400 text-xs">
                    Belum ada transaksi mutasi tercatat.
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <x-heroicon-s-shield-check class="w-4 h-4 text-emerald-600" />
                    Pemeriksaan & Rekonsiliasi Saldo Stok
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Memverifikasi kecocokan antara saldo stok tercatat saat ini dengan akumulasi riwayat mutasi barang masuk dan keluar.
                </p>
            </div>

            <button
                wire:click="runAudit"
                type="button"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-xs font-semibold transition cursor-pointer"
            >
                <span wire:loading.remove wire:target="runAudit" class="flex items-center gap-1.5">
                    <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
                    Jalankan Rekonsiliasi
                </span>
                <span wire:loading wire:target="runAudit" class="inline-flex items-center gap-2">
                    <svg class="animate-spin h-3.5 w-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    Memeriksa Riwayat...
                </span>
            </button>
        </div>

        @if($hasAudited)
        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-500 border-b border-slate-100">
                        <th class="pb-2 font-semibold">SKU</th>
                        <th class="pb-2 font-semibold">Nama Barang</th>
                        <th class="pb-2 font-semibold text-center">Stok Tercatat</th>
                        <th class="pb-2 font-semibold text-center">Hasil Riwayat Mutasi (Masuk - Keluar)</th>
                        <th class="pb-2 font-semibold text-center">Status Rekonsiliasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($auditResults as $row)
                    <tr>
                        <td class="py-2.5 font-mono font-semibold text-slate-700">{{ $row['sku'] }}</td>
                        <td class="py-2.5 text-slate-900 font-medium">{{ $row['name'] }}</td>
                        <td class="py-2.5 text-center font-bold text-slate-800">{{ $row['actual'] }}</td>
                        <td class="py-2.5 text-center font-bold text-slate-800">{{ $row['calculated'] }}</td>
                        <td class="py-2.5 text-center">
                            @if($row['is_matched'])
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <x-heroicon-s-check-circle class="w-3.5 h-3.5 text-emerald-600" />
                                Sinkron (Sesuai)
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                <x-heroicon-s-x-circle class="w-3.5 h-3.5 text-rose-600" />
                                Selisih (Perlu Cek)
                            </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
