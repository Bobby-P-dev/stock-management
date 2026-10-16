<div class="space-y-6 max-w-6xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                    Audit Ledger
                </span>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Kartu Stok (Stock Card)</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1">Buku mutasi pergerakan kuantitas masuk, keluar, dan kalkulasi saldo berjalan secara kronologis.</p>
        </div>

        <div class="flex items-center gap-2 self-start sm:self-auto print:hidden">
            @if($productId)
            <a
                href="{{ route('export.stock-card', ['productId' => $productId, 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition"
            >
                <x-heroicon-o-arrow-down-tray class="w-4 h-4 text-white" />
                Export Excel (.xls)
            </a>
            @endif

            <button
                type="button"
                onclick="window.print()"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold border border-slate-200 shadow-sm transition cursor-pointer"
            >
                <x-heroicon-o-printer class="w-4 h-4 text-slate-500" />
                Cetak Kartu Stok
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-white border border-slate-200/80 p-4 rounded-2xl shadow-sm print:hidden">
        <div 
            x-data="{ open: false }" 
            @click.outside="open = false" 
            @keydown.escape.window="open = false" 
            class="relative z-20"
        >
            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Pilih Produk Barang *</label>

            <!-- Searchable Input with dropdown toggle -->
            <div class="relative">
                <input
                    id="stock-card-product-search"
                    wire:key="stock-card-product-search"
                    type="text"
                    wire:model.live.debounce.250ms="productSearch"
                    @focus="open = true"
                    @click="open = true"
                    placeholder="{{ $selectedProduct ? $selectedProduct->sku . ' - ' . $selectedProduct->name : 'Ketik untuk cari produk...' }}"
                    class="w-full px-3 py-2 pr-14 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs placeholder:text-slate-800 placeholder:font-medium focus:outline-none focus:bg-white focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition"
                    autocomplete="off"
                >

                <div class="absolute inset-y-0 right-0 flex items-center pr-2 gap-1">
                    @if($productSearch)
                    <button
                        type="button"
                        wire:click="$set('productSearch', '')"
                        class="p-1 text-slate-400 hover:text-slate-600 cursor-pointer"
                        title="Hapus pencarian"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    @endif

                    <button
                        type="button"
                        @click="open = !open; if(open) $nextTick(() => document.getElementById('stock-card-product-search')?.focus())"
                        class="p-1 text-slate-400 hover:text-slate-600 cursor-pointer"
                    >
                        <svg class="w-4 h-4 transition-transform duration-150" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Dropdown Menu (Limit 5) -->
            <div
                x-show="open"
                class="absolute z-30 left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden py-1"
                style="display: none;"
            >
                <div class="max-h-48 overflow-y-auto divide-y divide-slate-50">
                    @forelse($products as $prod)
                    <button
                        type="button"
                        wire:click="selectProduct({{ $prod->id }})"
                        @click="open = false"
                        class="w-full px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50 transition flex items-center justify-between gap-2 cursor-pointer {{ $productId === $prod->id ? 'bg-indigo-50/60 font-semibold text-indigo-700' : '' }}"
                    >
                        <span class="truncate">{{ $prod->sku }} - {{ $prod->name }}</span>
                        @if($productId === $prod->id)
                        <span class="text-indigo-600 text-xs font-bold shrink-0">✓</span>
                        @endif
                    </button>
                    @empty
                    <div class="px-3 py-2.5 text-center text-xs text-slate-400">
                        Tidak ada barang yang cocok
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div>
            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Dari Tanggal (Opsional)</label>
            <input
                wire:model.live="startDate"
                type="date"
                class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs focus:outline-none focus:bg-white focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition"
            >
        </div>

        <div>
            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Sampai Tanggal (Opsional)</label>
            <input
                wire:model.live="endDate"
                type="date"
                class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs focus:outline-none focus:bg-white focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition"
            >
        </div>
    </div>

    @if($stockCardData)
    @php
        $product = $stockCardData['product'];
    @endphp

    <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm print:bg-white print:text-black print:border-gray-200 print:shadow-none">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-slate-100 print:border-gray-200">
            <div>
                <span class="text-xs font-mono font-bold text-indigo-700 bg-indigo-50 px-2.5 py-0.5 rounded-full border border-indigo-200">
                    {{ $product->sku }}
                </span>
                <h2 class="text-xl font-bold text-slate-900 print:text-black mt-2">{{ $product->name }}</h2>
                <div class="flex items-center gap-4 text-xs text-slate-500 print:text-gray-600 mt-1">
                    <span>Kategori: <strong class="text-slate-800 print:text-black">{{ $product->category->name }}</strong></span>
                    <span>&bull;</span>
                    <span>Satuan: <strong class="text-slate-800 print:text-black">{{ $product->unit->name }} ({{ $product->unit->symbol }})</strong></span>
                    <span>&bull;</span>
                    <span>Batas Min: <strong class="text-slate-800 print:text-black">{{ $product->minimum_stock }} {{ $product->unit->symbol }}</strong></span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="px-4 py-3 rounded-xl bg-emerald-50/70 border border-emerald-100 text-center">
                    <span class="text-[10px] text-emerald-700 uppercase tracking-wider font-semibold block">Total Masuk</span>
                    <span class="text-base font-extrabold text-emerald-700">+{{ $stockCardData['total_in'] }}</span>
                </div>

                <div class="px-4 py-3 rounded-xl bg-rose-50/70 border border-rose-100 text-center">
                    <span class="text-[10px] text-rose-700 uppercase tracking-wider font-semibold block">Total Keluar</span>
                    <span class="text-base font-extrabold text-rose-700">-{{ $stockCardData['total_out'] }}</span>
                </div>

                <div class="px-5 py-3 rounded-xl bg-indigo-50 border border-indigo-200 text-center">
                    <span class="text-[10px] text-indigo-700 uppercase tracking-wider font-semibold block">Saldo Akhir</span>
                    <span class="text-2xl font-extrabold text-indigo-700">{{ $stockCardData['final_balance'] }}</span>
                    <span class="text-[10px] text-indigo-500 font-medium">{{ $product->unit->symbol }}</span>
                </div>
            </div>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-500 print:text-gray-600 border-b border-slate-200 print:border-gray-300 bg-slate-50/80 print:bg-gray-50 uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-3 font-semibold">Tanggal</th>
                        <th class="py-3 px-3 font-semibold">No. Transaksi</th>
                        <th class="py-3 px-3 font-semibold text-center">Tipe</th>
                        <th class="py-3 px-3 font-semibold">Pihak (Supplier / Penerima)</th>
                        <th class="py-3 px-3 font-semibold text-center text-emerald-700 print:text-emerald-700">Masuk (+)</th>
                        <th class="py-3 px-3 font-semibold text-center text-rose-700 print:text-rose-700">Keluar (-)</th>
                        <th class="py-3 px-3 font-semibold text-center text-indigo-700 print:text-indigo-700">Saldo</th>
                        <th class="py-3 px-3 font-semibold">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 print:divide-gray-200 font-mono">
                    @if($startDate)
                    <tr class="bg-slate-50/60 text-slate-600 font-sans italic">
                        <td class="py-2.5 px-3" colspan="4">Saldo Awal Sebelum {{ Carbon\Carbon::parse($startDate)->format('d-m-Y') }}</td>
                        <td class="py-2.5 px-3 text-center">-</td>
                        <td class="py-2.5 px-3 text-center">-</td>
                        <td class="py-2.5 px-3 text-center font-bold text-slate-900">{{ $stockCardData['initial_balance'] }}</td>
                        <td class="py-2.5 px-3">-</td>
                    </tr>
                    @endif

                    @forelse($stockCardData['ledger'] as $row)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="py-2.5 px-3 font-sans text-slate-700 print:text-gray-800">{{ $row['date'] }}</td>
                        <td class="py-2.5 px-3 font-bold text-slate-900 print:text-black">
                            <a href="{{ route('transactions.show', $row['transaction_id']) }}" class="hover:underline text-indigo-600 print:text-indigo-700">
                                {{ $row['transaction_no'] }}
                            </a>
                        </td>
                        <td class="py-2.5 px-3 text-center font-sans">
                            @if($row['type'] === 'IN')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                IN
                            </span>
                            @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                OUT
                            </span>
                            @endif
                        </td>
                        <td class="py-2.5 px-3 font-sans text-slate-700 print:text-gray-800">{{ $row['partner'] }}</td>
                        <td class="py-2.5 px-3 text-center font-bold text-emerald-600 print:text-emerald-700">
                            {{ $row['in_qty'] > 0 ? '+' . $row['in_qty'] : '-' }}
                        </td>
                        <td class="py-2.5 px-3 text-center font-bold text-rose-600 print:text-rose-700">
                            {{ $row['out_qty'] > 0 ? '-' . $row['out_qty'] : '-' }}
                        </td>
                        <td class="py-2.5 px-3 text-center text-sm font-extrabold text-indigo-700 print:text-indigo-800">
                            {{ $row['balance'] }}
                        </td>
                        <td class="py-2.5 px-3 font-sans text-slate-500 print:text-gray-600 text-[11px] truncate max-w-xs">
                            {{ $row['note'] ?: '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-10 text-center text-slate-400 font-sans">
                            <x-heroicon-o-inbox class="w-8 h-8 text-slate-300 mx-auto mb-2" />
                            Belum ada catatan mutasi transaksi untuk barang ini pada rentang waktu yang dipilih.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
