<div class="space-y-6 max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200 print:hidden">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Pusat Laporan Inventaris & Mutasi</h1>
            <p class="text-xs text-slate-500 mt-1">Rekapitulasi data posisi stok, faktur barang masuk dari supplier, dan pengeluaran barang.</p>
        </div>

        <div class="flex items-center gap-2 self-start sm:self-auto">
            @if($activeTab === 'stock')
            <a
                href="{{ route('export.stock', ['category' => $stockCategory, 'status' => $stockStatus, 'sort_by' => $stockSortBy, 'velocity' => $stockVelocity, 'start_date' => $stockStartDate, 'end_date' => $stockEndDate]) }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition"
            >
                <x-heroicon-o-arrow-down-tray class="w-4 h-4 text-white" />
                Export Excel (.xls)
            </a>
            @elseif($activeTab === 'in')
            <a
                href="{{ route('export.stock-in', ['start_date' => $startDate, 'end_date' => $endDate, 'supplier_id' => $supplierId]) }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition"
            >
                <x-heroicon-o-arrow-down-tray class="w-4 h-4 text-white" />
                Export Excel (.xls)
            </a>
            @else
            <a
                href="{{ route('export.stock-out', ['start_date' => $startDate, 'end_date' => $endDate, 'recipient' => $recipient]) }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition"
            >
                <x-heroicon-o-arrow-down-tray class="w-4 h-4 text-white" />
                Export Excel (.xls)
            </a>
            @endif

            <button
                type="button"
                onclick="window.print()"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition cursor-pointer"
            >
                <x-heroicon-o-printer class="w-4 h-4 text-white" />
                Cetak Laporan
            </button>
        </div>
    </div>

    <div class="flex items-center gap-2 border-b border-slate-200 print:hidden">
        <button
            wire:click="setTab('stock')"
            type="button"
            class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs font-semibold border-b-2 transition cursor-pointer {{ $activeTab === 'stock' ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-slate-500 hover:text-slate-800' }}"
        >
            <x-heroicon-o-cube class="w-4 h-4" />
            1. Posisi Stok Barang
        </button>

        <button
            wire:click="setTab('in')"
            type="button"
            class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs font-semibold border-b-2 transition cursor-pointer {{ $activeTab === 'in' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-800' }}"
        >
            <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
            2. Barang Masuk (Stock In)
        </button>

        <button
            wire:click="setTab('out')"
            type="button"
            class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs font-semibold border-b-2 transition cursor-pointer {{ $activeTab === 'out' ? 'border-rose-600 text-rose-700' : 'border-transparent text-slate-500 hover:text-slate-800' }}"
        >
            <x-heroicon-o-arrow-up-tray class="w-4 h-4" />
            3. Barang Keluar (Stock Out)
        </button>
    </div>

    @if($activeTab === 'stock')
    <div class="space-y-4">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 print:hidden">
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Fast Moving (Paling Laku)</span>
                    <div class="text-xl font-bold text-slate-900 mt-0.5">{{ $stockSummary['fast_moving'] ?? 0 }} <span class="text-xs font-normal text-slate-500">item</span></div>
                    <p class="text-[10px] text-slate-500 mt-0.5">Perputaran mutasi tinggi</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center border border-emerald-100">
                    <x-heroicon-o-arrow-trending-up class="w-5 h-5" />
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-700">Medium Moving</span>
                    <div class="text-xl font-bold text-slate-900 mt-0.5">{{ $stockSummary['medium_moving'] ?? 0 }} <span class="text-xs font-normal text-slate-500">item</span></div>
                    <p class="text-[10px] text-slate-500 mt-0.5">Perputaran keluar-masuk stabil</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-slate-50 text-slate-700 flex items-center justify-center border border-slate-200">
                    <x-heroicon-o-arrows-right-left class="w-5 h-5" />
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700">Slow Moving</span>
                    <div class="text-xl font-bold text-slate-900 mt-0.5">{{ $stockSummary['slow_moving'] ?? 0 }} <span class="text-xs font-normal text-slate-500">item</span></div>
                    <p class="text-[10px] text-slate-500 mt-0.5">Jarang bergerak keluar</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center border border-amber-100">
                    <x-heroicon-o-clock class="w-5 h-5" />
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-rose-700">Non-Moving (Stok Diam)</span>
                    <div class="text-xl font-bold text-slate-900 mt-0.5">{{ $stockSummary['non_moving'] ?? 0 }} <span class="text-xs font-normal text-slate-500">item</span></div>
                    <p class="text-[10px] text-slate-500 mt-0.5">Tanpa riwayat transaksi</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-700 flex items-center justify-center border border-rose-100">
                    <x-heroicon-o-archive-box-x-mark class="w-5 h-5" />
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200/80 p-4 rounded-2xl shadow-xs print:hidden space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Urutan Ranking Data</label>
                    <select wire:model.live="stockSortBy" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs font-medium focus:outline-none focus:bg-white focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition">
                        <option value="turnover">Paling Cepat (Total In + Out)</option>
                        <option value="most_sold">Paling Laku (Total Keluar)</option>
                        <option value="most_in">Paling Sering Masuk</option>
                        <option value="stock_desc">Stok Fisik Terbanyak</option>
                        <option value="stock_asc">Stok Fisik Tersedikit</option>
                        <option value="name">Nama Barang (A-Z)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Status Kecepatan Perputaran</label>
                    <select wire:model.live="stockVelocity" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs font-medium focus:outline-none focus:bg-white focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition">
                        <option value="">Semua Perputaran</option>
                        <option value="FAST_MOVING">Fast Moving (Paling Cepat)</option>
                        <option value="MEDIUM_MOVING">Medium Moving (Sedang)</option>
                        <option value="SLOW_MOVING">Slow Moving (Lambat)</option>
                        <option value="NON_MOVING">Non-Moving (Stok Diam)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Filter Kategori</label>
                    <select wire:model.live="stockCategory" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs focus:outline-none focus:bg-white focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Status Ketersediaan</label>
                    <select wire:model.live="stockStatus" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs focus:outline-none focus:bg-white focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition">
                        <option value="">Semua Status Stok</option>
                        <option value="safe">Stok Aman</option>
                        <option value="low">Stok Menipis</option>
                        <option value="out">Stok Habis</option>
                    </select>
                </div>
            </div>

            <div class="pt-2 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-500">
                <div class="flex items-center gap-2">
                    <span class="font-medium text-slate-700">Periode Analisis Mutasi:</span>
                    <input
                        wire:model.live="stockStartDate"
                        type="date"
                        class="px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 text-xs focus:outline-none focus:bg-white focus:border-indigo-600"
                    />
                    <span>s/d</span>
                    <input
                        wire:model.live="stockEndDate"
                        type="date"
                        class="px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 text-xs focus:outline-none focus:bg-white focus:border-indigo-600"
                    />
                    @if($stockStartDate || $stockEndDate)
                    <button
                        type="button"
                        wire:click="$set('stockStartDate', null); $set('stockEndDate', null);"
                        class="text-[11px] text-rose-600 hover:underline cursor-pointer ml-1"
                    >
                        Reset ke Seluruh Waktu
                    </button>
                    @endif
                </div>
                <div class="text-[11px] text-slate-400">
                    *Kosongkan tanggal untuk menganalisis seluruh data mutasi sejak awal.
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-2xl overflow-hidden shadow-sm print:border-gray-200 print:bg-white print:text-black print:shadow-none">
            <div class="p-4 border-b border-slate-100 hidden print:block">
                <h2 class="text-base font-bold text-black">LAPORAN REKAPITULASI POSISI STOK & RANKING PERPUTARAN BARANG</h2>
                <p class="text-xs text-gray-600">Dicetak pada: {{ now()->format('d/m/Y H:i') }} &bull; Oleh: {{ auth()->user()->name }}</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-500 print:text-gray-600 bg-slate-50/80 print:bg-gray-100 border-b border-slate-200 print:border-gray-300 uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-3 font-semibold text-center w-12">Rank</th>
                            <th class="py-3 px-3 font-semibold">SKU</th>
                            <th class="py-3 px-4 font-semibold">Nama Barang</th>
                            <th class="py-3 px-3 font-semibold">Kategori</th>
                            <th class="py-3 px-3 font-semibold text-center text-emerald-700">Masuk (+)</th>
                            <th class="py-3 px-3 font-semibold text-center text-rose-700">Keluar (-)</th>
                            <th class="py-3 px-3 font-semibold text-center">Total Mutasi</th>
                            <th class="py-3 px-3 font-semibold text-center">Stok Terkini</th>
                            <th class="py-3 px-3 font-semibold text-center">Status Stok</th>
                            <th class="py-3 px-3 font-semibold text-center">Perputaran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 print:divide-gray-200">
                        @forelse($stockProducts as $p)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-3 text-center text-xs font-semibold text-slate-700 font-mono">
                                {{ $p->rank ?? '-' }}
                            </td>
                            <td class="py-3 px-3 font-mono font-bold text-indigo-600 print:text-indigo-800">{{ $p->sku }}</td>
                            <td class="py-3 px-4 font-medium text-slate-900 print:text-black">
                                <div>{{ $p->name }}</div>
                                <div class="text-[10px] text-slate-400">Batas Min: {{ $p->minimum_stock }} {{ $p->unit->symbol ?? '' }}</div>
                            </td>
                            <td class="py-3 px-3 text-slate-500 print:text-gray-600">{{ $p->category->name ?? '-' }}</td>
                            <td class="py-3 px-3 text-center font-semibold text-emerald-600">
                                {{ $p->total_in > 0 ? '+'.$p->total_in : '-' }}
                            </td>
                            <td class="py-3 px-3 text-center font-bold text-rose-600">
                                {{ $p->total_out > 0 ? '-'.$p->total_out : '-' }}
                            </td>
                            <td class="py-3 px-3 text-center font-extrabold text-slate-900">
                                {{ $p->total_movement }}
                            </td>
                            <td class="py-3 px-3 text-center font-bold text-sm text-slate-900 print:text-black">
                                {{ $p->current_stock }} <span class="text-[10px] text-slate-400 font-normal">{{ $p->unit->symbol ?? '' }}</span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                @if($p->isOutOfStock())
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Habis</span>
                                @elseif($p->isLowStock())
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Menipis</span>
                                @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Aman</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center">
                                @if($p->velocity_status === 'FAST_MOVING')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                    Fast Moving
                                </span>
                                @elseif($p->velocity_status === 'MEDIUM_MOVING')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                                    Medium
                                </span>
                                @elseif($p->velocity_status === 'SLOW_MOVING')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                    Slow
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-medium bg-rose-50 text-rose-800 border border-rose-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    Non-Moving
                                </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="py-10 text-center text-slate-400">
                                <x-heroicon-o-inbox class="w-8 h-8 text-slate-300 mx-auto mb-2" />
                                Tidak ada data barang sesuai kriteria ranking & filter.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    @if($activeTab === 'in')
    <div class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-white border border-slate-200/80 p-4 rounded-2xl shadow-sm print:hidden">
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Dari Tanggal</label>
                <input wire:model.live="startDate" type="date" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs focus:outline-none focus:bg-white focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition">
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Sampai Tanggal</label>
                <input wire:model.live="endDate" type="date" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs focus:outline-none focus:bg-white focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition">
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Filter Supplier</label>
                <select wire:model.live="supplierId" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs focus:outline-none focus:bg-white focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition">
                    <option value="">Semua Supplier</option>
                    @foreach($suppliers as $sup)
                    <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-2xl overflow-hidden shadow-sm print:border-gray-200 print:bg-white print:text-black print:shadow-none">
            <div class="p-4 border-b border-slate-100 hidden print:block">
                <h2 class="text-base font-bold text-black">LAPORAN REKAPITULASI BARANG MASUK (STOCK IN)</h2>
                <p class="text-xs text-gray-600">Periode: {{ $startDate }} s/d {{ $endDate }} &bull; Dicetak: {{ now()->format('d/m/Y H:i') }}</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-500 print:text-gray-600 bg-slate-50/80 print:bg-gray-100 border-b border-slate-200 print:border-gray-300 uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4 font-semibold">No. Faktur</th>
                            <th class="py-3 px-4 font-semibold">Tanggal</th>
                            <th class="py-3 px-4 font-semibold">Supplier</th>
                            <th class="py-3 px-4 font-semibold">Daftar Barang Diterima</th>
                            <th class="py-3 px-4 font-semibold text-center">Total Unit</th>
                            <th class="py-3 px-4 font-semibold">Dicatat Oleh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 print:divide-gray-200">
                        @php $totalUnitMasuk = 0; @endphp
                        @forelse($stockInTransactions as $tx)
                        @php $unitSub = $tx->items->sum('quantity'); $totalUnitMasuk += $unitSub; @endphp
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-4 font-mono font-bold text-emerald-600 print:text-emerald-700">
                                {{ $tx->transaction_no }}
                            </td>
                            <td class="py-3 px-4 text-slate-600 print:text-black">{{ $tx->transaction_date->format('d/m/Y') }}</td>
                            <td class="py-3 px-4 text-slate-900 print:text-black font-medium">{{ $tx->supplier->name ?? '-' }}</td>
                            <td class="py-3 px-4 text-slate-600 print:text-gray-700">
                                <ul class="list-disc list-inside space-y-0.5">
                                    @foreach($tx->items as $it)
                                    <li>{{ $it->product->name }} <strong class="text-slate-900 print:text-black">({{ $it->quantity }} {{ $it->product->unit->symbol }})</strong></li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-slate-900 print:text-black">
                                +{{ $unitSub }}
                            </td>
                            <td class="py-3 px-4 text-slate-500 print:text-gray-600">{{ $tx->creator->name }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400">
                                <x-heroicon-o-inbox class="w-8 h-8 text-slate-300 mx-auto mb-2" />
                                Tidak ada data transaksi masuk pada rentang tanggal terpilih.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if(count($stockInTransactions) > 0)
                    <tfoot>
                        <tr class="border-t border-slate-200 print:border-gray-300 font-bold bg-slate-50/60 print:bg-gray-50">
                            <td colspan="4" class="py-3 px-4 text-right text-slate-700 print:text-black">Total Kuantitas Masuk:</td>
                            <td class="py-3 px-4 text-center text-base text-emerald-600 print:text-emerald-700 font-mono">+{{ $totalUnitMasuk }} Unit</td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
    @endif

    @if($activeTab === 'out')
    <div class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-white border border-slate-200/80 p-4 rounded-2xl shadow-sm print:hidden">
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Dari Tanggal</label>
                <input wire:model.live="startDate" type="date" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs focus:outline-none focus:bg-white focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition">
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Sampai Tanggal</label>
                <input wire:model.live="endDate" type="date" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs focus:outline-none focus:bg-white focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition">
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Cari Penerima / Divisi</label>
                <input wire:model.live.debounce.300ms="recipient" type="text" placeholder="Nama divisi / orang..." class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs focus:outline-none focus:bg-white focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition">
            </div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-2xl overflow-hidden shadow-sm print:border-gray-200 print:bg-white print:text-black print:shadow-none">
            <div class="p-4 border-b border-slate-100 hidden print:block">
                <h2 class="text-base font-bold text-black">LAPORAN REKAPITULASI BARANG KELUAR (STOCK OUT)</h2>
                <p class="text-xs text-gray-600">Periode: {{ $startDate }} s/d {{ $endDate }} &bull; Dicetak: {{ now()->format('d/m/Y H:i') }}</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-500 print:text-gray-600 bg-slate-50/80 print:bg-gray-100 border-b border-slate-200 print:border-gray-300 uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4 font-semibold">No. Transaksi</th>
                            <th class="py-3 px-4 font-semibold">Tanggal</th>
                            <th class="py-3 px-4 font-semibold">Penerima / Pemohon</th>
                            <th class="py-3 px-4 font-semibold">Rincian Barang Keluar</th>
                            <th class="py-3 px-4 font-semibold text-center">Total Unit</th>
                            <th class="py-3 px-4 font-semibold">Dicatat Oleh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 print:divide-gray-200">
                        @php $totalUnitKeluar = 0; @endphp
                        @forelse($stockOutTransactions as $tx)
                        @php $unitSub = $tx->items->sum('quantity'); $totalUnitKeluar += $unitSub; @endphp
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-4 font-mono font-bold text-rose-600 print:text-rose-700">
                                {{ $tx->transaction_no }}
                            </td>
                            <td class="py-3 px-4 text-slate-600 print:text-black">{{ $tx->transaction_date->format('d/m/Y') }}</td>
                            <td class="py-3 px-4 text-slate-900 print:text-black font-medium">{{ $tx->recipient }}</td>
                            <td class="py-3 px-4 text-slate-600 print:text-gray-700">
                                <ul class="list-disc list-inside space-y-0.5">
                                    @foreach($tx->items as $it)
                                    <li>{{ $it->product->name }} <strong class="text-slate-900 print:text-black">({{ $it->quantity }} {{ $it->product->unit->symbol }})</strong></li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-rose-600 print:text-rose-700">
                                -{{ $unitSub }}
                            </td>
                            <td class="py-3 px-4 text-slate-500 print:text-gray-600">{{ $tx->creator->name }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400">
                                <x-heroicon-o-inbox class="w-8 h-8 text-slate-300 mx-auto mb-2" />
                                Tidak ada data transaksi keluar pada rentang tanggal terpilih.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if(count($stockOutTransactions) > 0)
                    <tfoot>
                        <tr class="border-t border-slate-200 print:border-gray-300 font-bold bg-slate-50/60 print:bg-gray-50">
                            <td colspan="4" class="py-3 px-4 text-right text-slate-700 print:text-black">Total Kuantitas Keluar:</td>
                            <td class="py-3 px-4 text-center text-base text-rose-600 print:text-rose-700 font-mono">-{{ $totalUnitKeluar }} Unit</td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
