<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-200 print:hidden">
        <a
            href="{{ route('transactions.index') }}"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold border border-slate-200 shadow-sm transition"
        >
            <x-heroicon-o-arrow-left class="w-4 h-4 text-slate-500" />
            Kembali ke Riwayat
        </a>

        <div class="flex items-center gap-2">
            <button
                type="button"
                onclick="window.print()"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition cursor-pointer"
            >
                <x-heroicon-o-printer class="w-4 h-4 text-white" />
                Cetak Dokumen
            </button>
        </div>
    </div>

    <div class="bg-white border border-slate-200/80 rounded-3xl p-8 shadow-sm text-slate-900 print:bg-white print:text-black print:border-none print:shadow-none print:p-0">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6 pb-6 border-b border-slate-100 print:border-gray-200">
            <div>
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-bold text-xs shadow-sm">
                        BMI
                    </div>
                    <span class="text-lg font-bold text-slate-900 print:text-black">Berkah Mandiri Inventory</span>
                </div>
                <p class="text-xs text-slate-500 print:text-gray-600 mt-1">Dokumen Resmi Bukti Mutasi Barang Fisik Gudang</p>
            </div>

            <div class="text-left sm:text-right">
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ $transaction->type === 'IN' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 print:bg-emerald-50 print:text-emerald-700' : 'bg-rose-50 text-rose-700 border border-rose-200 print:bg-rose-50 print:text-rose-700' }}">
                    @if($transaction->type === 'IN')
                        <x-heroicon-s-arrow-down-tray class="w-3.5 h-3.5 text-emerald-600" />
                        Barang Masuk (Stock In)
                    @else
                        <x-heroicon-s-arrow-up-tray class="w-3.5 h-3.5 text-rose-600" />
                        Barang Keluar (Stock Out)
                    @endif
                </span>
                <div class="text-xl font-extrabold text-slate-900 print:text-black font-mono mt-2">
                    {{ $transaction->transaction_no }}
                </div>
                <div class="text-xs text-slate-500 print:text-gray-600">
                    Tanggal: {{ $transaction->transaction_date->format('d F Y') }}
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 py-6 border-b border-slate-100 print:border-gray-200 text-xs">
            <div>
                @if($transaction->type === 'IN')
                <span class="text-slate-400 print:text-gray-500 uppercase tracking-wider font-semibold text-[10px] block mb-1">
                    Mitra Pemasok / Supplier
                </span>
                <div class="text-sm font-bold text-slate-900 print:text-black">{{ $transaction->supplier->name ?? '-' }}</div>
                <div class="text-slate-500 print:text-gray-600 font-mono mt-0.5">Kode: {{ $transaction->supplier->code ?? '-' }}</div>
                <div class="text-slate-500 print:text-gray-600 mt-1">{{ $transaction->supplier->address ?? '-' }}</div>
                <div class="text-slate-500 print:text-gray-600 mt-0.5">Telp: {{ $transaction->supplier->phone ?? '-' }}</div>
                @else
                <span class="text-slate-400 print:text-gray-500 uppercase tracking-wider font-semibold text-[10px] block mb-1">
                    Penerima / Divisi Pemohon
                </span>
                <div class="text-sm font-bold text-slate-900 print:text-black">{{ $transaction->recipient }}</div>
                <div class="text-slate-500 print:text-gray-600 mt-1">Pengeluaran stok barang operasional</div>
                @endif
            </div>

            <div class="sm:text-right">
                <span class="text-slate-400 print:text-gray-500 uppercase tracking-wider font-semibold text-[10px] block mb-1">
                    Petugas Pencatat Transaksi
                </span>
                <div class="text-sm font-bold text-slate-900 print:text-black">{{ $transaction->creator->name }}</div>
                <div class="text-slate-500 print:text-gray-600 capitalize">Peran: {{ $transaction->creator->role }}</div>
                <div class="text-slate-500 print:text-gray-600 mt-1">Dicatat pada sistem: {{ $transaction->created_at->format('d/m/Y H:i:s') }}</div>

                @if($transaction->note)
                <div class="mt-3 p-3 rounded-xl bg-slate-50 print:bg-gray-50 border border-slate-200 print:border-gray-200 text-slate-700 print:text-gray-700 text-left text-[11px]">
                    <span class="font-bold text-slate-800 print:text-gray-800">Catatan:</span> {{ $transaction->note }}
                </div>
                @endif
            </div>
        </div>

        <div class="py-6 overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-500 print:text-gray-600 border-b border-slate-200 print:border-gray-300 uppercase tracking-wider text-[11px]">
                        <th class="pb-3 font-semibold">No</th>
                        <th class="pb-3 font-semibold">Kode SKU</th>
                        <th class="pb-3 font-semibold">Nama Barang</th>
                        <th class="pb-3 font-semibold">Kategori</th>
                        <th class="pb-3 font-semibold text-center">Kuantitas</th>
                        @if($transaction->type === 'IN')
                        <th class="pb-3 font-semibold text-right">Harga Satuan</th>
                        <th class="pb-3 font-semibold text-right">Subtotal</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 print:divide-gray-200">
                    @php $grandTotal = 0; @endphp
                    @foreach($transaction->items as $idx => $item)
                    @php $grandTotal += $item->subtotal; @endphp
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3 text-slate-400 print:text-gray-600">{{ $idx + 1 }}</td>
                        <td class="py-3 font-mono font-bold text-indigo-600 print:text-indigo-800">{{ $item->product->sku }}</td>
                        <td class="py-3 font-medium text-slate-900 print:text-black">{{ $item->product->name }}</td>
                        <td class="py-3 text-slate-500 print:text-gray-600">{{ $item->product->category->name }}</td>
                        <td class="py-3 text-center">
                            <span class="font-bold text-slate-900 print:text-black">{{ $item->quantity }}</span>
                            <span class="text-slate-400 print:text-gray-600 text-[10px]">{{ $item->product->unit->symbol }}</span>
                        </td>
                        @if($transaction->type === 'IN')
                        <td class="py-3 text-right text-slate-600 print:text-gray-800">
                            {{ $item->unit_price ? 'Rp ' . number_format($item->unit_price, 0, ',', '.') : '-' }}
                        </td>
                        <td class="py-3 text-right font-semibold text-slate-900 print:text-black">
                            {{ $item->subtotal ? 'Rp ' . number_format($item->subtotal, 0, ',', '.') : '-' }}
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
                @if($transaction->type === 'IN' && $grandTotal > 0)
                <tfoot>
                    <tr class="border-t border-slate-200 print:border-gray-300 font-bold">
                        <td colspan="6" class="pt-4 text-right text-slate-700 print:text-gray-800">Total Nilai Pembelian:</td>
                        <td class="pt-4 text-right text-base text-emerald-600 print:text-emerald-700 font-mono">
                            Rp {{ number_format($grandTotal, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        <div class="pt-10 grid grid-cols-2 gap-8 text-center text-xs text-slate-500 print:text-black">
            <div>
                <p>Petugas Penanggung Jawab,</p>
                <div class="h-16"></div>
                <p class="font-bold text-slate-900 print:text-black">({{ $transaction->creator->name }})</p>
                <p class="text-[10px] text-slate-400">Berkah Mandiri Inventory</p>
            </div>
            <div>
                <p>{{ $transaction->type === 'IN' ? 'Pihak Pengirim / Supplier,' : 'Pihak Penerima Barang,' }}</p>
                <div class="h-16"></div>
                <p class="font-bold text-slate-900 print:text-black">
                    ({{ $transaction->type === 'IN' ? ($transaction->supplier->name ?? 'Supplier') : $transaction->recipient }})
                </p>
                <p class="text-[10px] text-slate-400">Tanda Tangan & Nama Terang</p>
            </div>
        </div>
    </div>
</div>
