<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-200 print:hidden">
        <a
            href="{{ route('opnames.index') }}"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold border border-slate-200 shadow-sm transition"
        >
            <x-heroicon-o-arrow-left class="w-4 h-4 text-slate-500" />
            Kembali ke Daftar Opname
        </a>

        <div class="flex items-center gap-2">
            <a
                href="{{ route('export.opname', $opname->id) }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition cursor-pointer"
            >
                <x-heroicon-o-arrow-down-tray class="w-4 h-4 text-white" />
                Export Excel (.xls)
            </a>

            <button
                type="button"
                onclick="window.print()"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition cursor-pointer"
            >
                <x-heroicon-o-printer class="w-4 h-4 text-white" />
                Cetak Berita Acara
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
                    <div>
                        <span class="text-lg font-bold text-slate-900 print:text-black">Berkah Mandiri Inventory</span>
                        <div class="text-[11px] font-semibold text-indigo-600 uppercase tracking-wider">Berita Acara Stock Opname</div>
                    </div>
                </div>
                <p class="text-xs text-slate-500 print:text-gray-600 mt-2">Dokumen Resmi Bukti Pemeriksaan & Penyesuaian Saldo Fisik Barang Gudang</p>
            </div>

            <div class="text-left sm:text-right">
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 print:bg-gray-100 print:text-black">
                    <x-heroicon-s-clipboard-document-check class="w-3.5 h-3.5 text-indigo-600" />
                    Status: Selesai / Terverifikasi
                </span>
                <div class="text-xl font-extrabold text-slate-900 print:text-black font-mono mt-2">
                    {{ $opname->opname_no }}
                </div>
                <div class="text-xs text-slate-500 print:text-gray-600">
                    Tanggal Perhitungan: {{ $opname->opname_date->format('d F Y') }}
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 py-5 border-b border-slate-100 print:border-gray-200 text-xs">
            <div>
                <span class="text-slate-400 print:text-gray-500 uppercase tracking-wider font-semibold text-[10px] block mb-1">
                    Petugas Pelaksana Pemeriksa
                </span>
                <div class="text-sm font-bold text-slate-900 print:text-black">{{ $opname->creator->name }}</div>
                <div class="text-slate-500 print:text-gray-600 capitalize">Hak Akses: {{ $opname->creator->role }}</div>
                <div class="text-slate-400 print:text-gray-600 text-[11px] mt-0.5">Waktu Eksekusi Sistem: {{ $opname->created_at->format('d/m/Y H:i:s') }}</div>
            </div>

            <div>
                <span class="text-slate-400 print:text-gray-500 uppercase tracking-wider font-semibold text-[10px] block mb-1">
                    Catatan / Berita Acara
                </span>
                <div class="p-3 rounded-xl bg-slate-50 print:bg-gray-50 border border-slate-200 print:border-gray-200 text-slate-700 print:text-gray-800 text-[11px]">
                    {{ $opname->note ?: 'Tidak ada catatan khusus.' }}
                </div>
            </div>
        </div>

        @php
            $totalCount = $opname->items->count();
            $matchedCount = $opname->items->where('difference', 0)->count();
            $lossCount = $opname->items->where('difference', '<', 0)->count();
            $surplusCount = $opname->items->where('difference', '>', 0)->count();
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 py-5 border-b border-slate-100 print:border-gray-200">
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 text-center">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Total Diperiksa</span>
                <span class="text-lg font-bold text-slate-900">{{ $totalCount }} Barang</span>
            </div>

            <div class="p-3.5 rounded-xl bg-emerald-50/70 border border-emerald-100 text-center">
                <span class="text-[10px] font-semibold text-emerald-700 uppercase tracking-wider block">Stok Sesuai</span>
                <span class="text-lg font-bold text-emerald-700">{{ $matchedCount }} Barang</span>
            </div>

            <div class="p-3.5 rounded-xl bg-rose-50/70 border border-rose-100 text-center">
                <span class="text-[10px] font-semibold text-rose-700 uppercase tracking-wider block">Kurang / Rusak</span>
                <span class="text-lg font-bold text-rose-700">{{ $lossCount }} Barang</span>
            </div>

            <div class="p-3.5 rounded-xl bg-indigo-50/70 border border-indigo-100 text-center">
                <span class="text-[10px] font-semibold text-indigo-700 uppercase tracking-wider block">Lebih / Surplus</span>
                <span class="text-lg font-bold text-indigo-700">{{ $surplusCount }} Barang</span>
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
                        <th class="pb-3 font-semibold text-center">Stok Sistem</th>
                        <th class="pb-3 font-semibold text-center">Stok Fisik</th>
                        <th class="pb-3 font-semibold text-center">Selisih</th>
                        <th class="pb-3 font-semibold">Alasan Penyesuaian</th>
                        <th class="pb-3 font-semibold">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 print:divide-gray-200">
                    @foreach($opname->items as $idx => $item)
                    @php $diff = $item->difference; @endphp
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3 text-slate-400 print:text-gray-600">{{ $idx + 1 }}</td>
                        <td class="py-3 font-mono font-bold text-indigo-600 print:text-indigo-800">{{ $item->product->sku }}</td>
                        <td class="py-3 font-medium text-slate-900 print:text-black">{{ $item->product->name }}</td>
                        <td class="py-3 text-slate-500 print:text-gray-600">{{ $item->product->category->name }}</td>
                        <td class="py-3 text-center text-slate-600 font-semibold">{{ $item->system_stock }} {{ $item->product->unit->symbol }}</td>
                        <td class="py-3 text-center font-bold text-slate-900 print:text-black">{{ $item->physical_stock }} {{ $item->product->unit->symbol }}</td>
                        <td class="py-3 text-center font-bold">
                            @if($diff === 0)
                            <span class="text-emerald-600">0</span>
                            @elseif($diff < 0)
                            <span class="text-rose-600 font-bold">{{ $diff }}</span>
                            @else
                            <span class="text-indigo-600 font-bold">+{{ $diff }}</span>
                            @endif
                        </td>
                        <td class="py-3">
                            @php
                                $badgeClass = match($item->reason) {
                                    'SESUAI' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'RUSAK', 'HILANG' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    default => 'bg-amber-50 text-amber-700 border-amber-200',
                                };
                                $reasonLabel = match($item->reason) {
                                    'SESUAI' => 'Stok Sesuai',
                                    'RUSAK' => 'Barang Rusak',
                                    'HILANG' => 'Barang Hilang',
                                    'KADALUWARSA' => 'Kadaluwarsa',
                                    'SELISIH_HITUNG' => 'Koreksi Hitung',
                                    default => 'Penyesuaian Fisik',
                                };
                            @endphp
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $badgeClass }} print:border-none print:p-0">
                                {{ $reasonLabel }}
                            </span>
                        </td>
                        <td class="py-3 text-slate-500 print:text-gray-600 text-[11px]">
                            {{ $item->item_notes ?: '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pt-10 grid grid-cols-2 gap-8 text-center text-xs text-slate-500 print:text-black">
            <div>
                <p>Petugas Pelaksana Pemeriksa Fisik,</p>
                <div class="h-16"></div>
                <p class="font-bold text-slate-900 print:text-black">({{ $opname->creator->name }})</p>
                <p class="text-[10px] text-slate-400">Tim Audit Gudang</p>
            </div>
            <div>
                <p>Penanggung Jawab / Kepala Gudang,</p>
                <div class="h-16"></div>
                <p class="font-bold text-slate-900 print:text-black">( ............................................ )</p>
                <p class="text-[10px] text-slate-400">Tanda Tangan & Nama Terang</p>
            </div>
        </div>
    </div>
</div>

