<div class="space-y-6 min-w-0">
    {{-- =========================================================================
         1. SCREEN VIEW (Visible on Display, Hidden during Printing)
         ========================================================================= --}}
    <div class="print:hidden space-y-6">
        <!-- Header Page / Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200/80">
            <div class="flex items-center gap-3">
                <x-ui.button
                    href="{{ route('transactions.index') }}"
                    variant="outline"
                    size="sm"
                >
                    <x-slot:icon>
                        <x-heroicon-o-arrow-left class="w-3.5 h-3.5 text-slate-500 mr-1.5" />
                    </x-slot:icon>
                    Kembali ke Riwayat
                </x-ui.button>
                <div class="h-4 w-px bg-slate-200 hidden sm:block"></div>
                <div>
                    <h1 class="text-lg font-bold text-slate-900 tracking-tight">Detail Dokumen Mutasi</h1>
                    <p class="text-xs text-slate-500">Bukti pencatatan transaksi fisik gudang.</p>
                </div>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto">
                <x-ui.button
                    type="button"
                    onclick="window.print()"
                    variant="primary"
                    size="sm"
                >
                    <x-slot:icon>
                        <x-heroicon-o-printer class="w-4 h-4 mr-1.5 stroke-[2]" />
                    </x-slot:icon>
                    Cetak Dokumen
                </x-ui.button>
            </div>
        </div>

        <!-- Document Card (Screen Presentation) -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 sm:p-8 shadow-2xs text-slate-900 space-y-6">
            <!-- Document Header -->
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6 pb-6 border-b border-slate-200/80">
                <div>
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white font-bold text-sm shadow-2xs">
                            BMI
                        </div>
                        <div>
                            <span class="text-base font-bold text-slate-900 block leading-tight">Berkah Mandiri Inventory</span>
                            <p class="text-xs text-slate-500 mt-0.5">Dokumen Resmi Bukti Mutasi Barang Fisik Gudang</p>
                        </div>
                    </div>
                </div>

                <div class="text-left sm:text-right">
                    <div>
                        @if($transaction->type === 'IN')
                            <x-ui.badge variant="success" size="md">
                                <x-heroicon-s-arrow-down-tray class="w-3.5 h-3.5 mr-1" />
                                Barang Masuk (Stock In)
                            </x-ui.badge>
                        @else
                            <x-ui.badge variant="danger" size="md">
                                <x-heroicon-s-arrow-up-tray class="w-3.5 h-3.5 mr-1" />
                                Barang Keluar (Stock Out)
                            </x-ui.badge>
                        @endif
                    </div>
                    <div class="text-xl sm:text-2xl font-black text-slate-900 font-mono tracking-tight mt-2">
                        {{ $transaction->transaction_no }}
                    </div>
                    <div class="text-xs text-slate-500 mt-0.5">
                        Tanggal Transaksi: <span class="font-semibold text-slate-700">{{ $transaction->transaction_date->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Information Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 py-2 text-xs">
                <div class="space-y-1.5">
                    @if($transaction->type === 'IN')
                        <span class="text-slate-400 uppercase tracking-wider font-bold text-[10px] block">
                            Mitra Pemasok / Supplier
                        </span>
                        <div class="text-sm font-bold text-slate-900">{{ $transaction->supplier->name ?? '-' }}</div>
                        @if($transaction->supplier?->code)
                            <div class="text-slate-500 font-mono text-[11px]">Kode: {{ $transaction->supplier->code }}</div>
                        @endif
                        @if($transaction->supplier?->address)
                            <div class="text-slate-500 mt-1">{{ $transaction->supplier->address }}</div>
                        @endif
                        @if($transaction->supplier?->phone)
                            <div class="text-slate-500 text-[11px]">Telp: {{ $transaction->supplier->phone }}</div>
                        @endif
                    @else
                        <span class="text-slate-400 uppercase tracking-wider font-bold text-[10px] block">
                            Nama Penerima Barang
                        </span>
                        <div class="text-sm font-bold text-slate-900">{{ $transaction->recipient ?? '-' }}</div>
                        <div class="text-slate-500 mt-0.5">Pengeluaran persediaan barang gudang</div>
                    @endif
                </div>

                <div class="sm:text-right space-y-1.5">
                    <span class="text-slate-400 uppercase tracking-wider font-bold text-[10px] block">
                        Petugas Pencatat
                    </span>
                    <div class="text-sm font-bold text-slate-900">{{ $transaction->creator->name }}</div>
                    <div class="text-slate-500 capitalize text-[11px]">Peran: {{ $transaction->creator->role }}</div>
                    <div class="text-slate-500 text-[11px]">Dicatat pada: {{ $transaction->created_at->format('d/m/Y H:i:s') }}</div>

                    @if($transaction->note)
                    <div class="mt-3 p-3 rounded-xl bg-slate-50 border border-slate-200/80 text-slate-700 text-left text-xs">
                        <span class="font-bold text-slate-800">Catatan:</span> {{ $transaction->note }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Items Table (Screen) -->
            <div class="rounded-xl border border-slate-200/80 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-slate-600 bg-slate-50/80 border-b border-slate-200/80 uppercase tracking-wider text-[11px]">
                                <th class="py-3 px-4 font-semibold text-center w-12">No</th>
                                <th class="py-3 px-4 font-semibold w-36">Kode SKU</th>
                                <th class="py-3 px-4 font-semibold">Nama Barang</th>
                                <th class="py-3 px-4 font-semibold w-40">Kategori</th>
                                <th class="py-3 px-4 font-semibold text-center w-32">Kuantitas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($transaction->items as $idx => $item)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-3 px-4 text-center text-slate-500 font-mono text-xs">
                                    {{ $idx + 1 }}
                                </td>
                                <td class="py-3 px-4 font-mono font-bold text-blue-600">
                                    {{ $item->product->sku }}
                                </td>
                                <td class="py-3 px-4 font-medium text-slate-900">
                                    {{ $item->product->name }}
                                </td>
                                <td class="py-3 px-4 text-slate-500">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[11px] font-medium">
                                        {{ $item->product->category->name }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="font-bold text-slate-900 text-sm">{{ $item->quantity }}</span>
                                    <span class="text-slate-500 text-[11px] ml-0.5">{{ $item->product->unit->symbol }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-50/70 border-t border-slate-200/80 font-semibold text-slate-800">
                                <td colspan="4" class="py-3 px-4 text-right">
                                    Total Kuantitas ({{ $transaction->items->count() }} Jenis Barang):
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-sm text-slate-900">
                                    {{ $transaction->items->sum('quantity') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================================================================
         2. DEDICATED A4 PRINT DOCUMENT (Visible ONLY during Printing)
         ========================================================================= --}}
    <div class="hidden print:block w-full text-slate-950 bg-white">
        {{-- Print Header / Kop Dokumen --}}
        <div class="border-b-2 border-slate-900 pb-3 mb-4 flex items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-10 h-10 object-contain shrink-0 mt-0.5">
                <div>
                    <h1 class="text-[13pt] font-black uppercase tracking-tight text-slate-950 leading-tight">
                        BERKAH MANDIRI INVENTORY
                    </h1>
                    <p class="text-[8.5pt] font-medium text-slate-700 leading-snug">
                        Sistem Informasi Manajemen Persediaan & Pergudangan
                    </p>
                    <p class="text-[8pt] text-slate-600 leading-snug">
                        Dokumen Resmi Bukti Mutasi Barang Fisik Gudang
                    </p>
                </div>
            </div>

            <div class="text-right">
                <div class="inline-block px-3 py-1 border-2 border-slate-900 rounded font-black text-[9.5pt] uppercase tracking-wider bg-slate-100">
                    {{ $transaction->type === 'IN' ? 'BUKTI BARANG MASUK (IN)' : 'BUKTI BARANG KELUAR (OUT)' }}
                </div>
                <div class="font-mono text-[13pt] font-black text-slate-950 mt-1 leading-tight">
                    {{ $transaction->transaction_no }}
                </div>
                <div class="text-[8.5pt] text-slate-700 mt-0.5">
                    Tanggal Transaksi: <span class="font-bold text-slate-950">{{ $transaction->transaction_date->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>

        {{-- Metadata Grid (Pihak Terkait & Petugas) --}}
        <div class="grid grid-cols-12 gap-3 mb-4 text-[8.5pt]">
            {{-- Pihak Terkait --}}
            <div class="col-span-7 border border-slate-400 rounded p-2.5">
                <div class="text-[8pt] font-bold uppercase tracking-wider text-slate-700 border-b border-slate-300 pb-1 mb-1.5">
                    {{ $transaction->type === 'IN' ? 'Informasi Pemasok / Supplier' : 'Informasi Penerima Barang' }}
                </div>
                <table class="w-full leading-snug">
                    <tbody>
                        @if($transaction->type === 'IN')
                        <tr>
                            <td class="py-0.5 font-semibold text-slate-600 w-28">Nama Supplier</td>
                            <td class="py-0.5 px-1 text-slate-400 w-2">:</td>
                            <td class="py-0.5 font-bold text-slate-950">{{ $transaction->supplier->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="py-0.5 font-semibold text-slate-600">Kode Supplier</td>
                            <td class="py-0.5 px-1 text-slate-400">:</td>
                            <td class="py-0.5 font-mono text-slate-900">{{ $transaction->supplier->code ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="py-0.5 font-semibold text-slate-600">Alamat</td>
                            <td class="py-0.5 px-1 text-slate-400">:</td>
                            <td class="py-0.5 text-slate-900">{{ $transaction->supplier->address ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="py-0.5 font-semibold text-slate-600">No. Telepon</td>
                            <td class="py-0.5 px-1 text-slate-400">:</td>
                            <td class="py-0.5 text-slate-900">{{ $transaction->supplier->phone ?? '-' }}</td>
                        </tr>
                        @else
                        <tr>
                            <td class="py-0.5 font-semibold text-slate-600 w-28">Nama Penerima</td>
                            <td class="py-0.5 px-1 text-slate-400 w-2">:</td>
                            <td class="py-0.5 font-bold text-slate-950">{{ $transaction->recipient ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="py-0.5 font-semibold text-slate-600">Jenis Mutasi</td>
                            <td class="py-0.5 px-1 text-slate-400">:</td>
                            <td class="py-0.5 text-slate-900">Pengeluaran Stok Barang Gudang</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- Petugas & Catatan --}}
            <div class="col-span-5 border border-slate-400 rounded p-2.5 flex flex-col justify-between">
                <div>
                    <div class="text-[8pt] font-bold uppercase tracking-wider text-slate-700 border-b border-slate-300 pb-1 mb-1.5">
                        Data Pencatatan Sistem
                    </div>
                    <table class="w-full leading-snug">
                        <tbody>
                            <tr>
                                <td class="py-0.5 font-semibold text-slate-600 w-24">Petugas</td>
                                <td class="py-0.5 px-1 text-slate-400 w-2">:</td>
                                <td class="py-0.5 font-bold text-slate-950">{{ $transaction->creator->name }}</td>
                            </tr>
                            <tr>
                                <td class="py-0.5 font-semibold text-slate-600">Peran / Role</td>
                                <td class="py-0.5 px-1 text-slate-400">:</td>
                                <td class="py-0.5 capitalize text-slate-900">{{ $transaction->creator->role }}</td>
                            </tr>
                            <tr>
                                <td class="py-0.5 font-semibold text-slate-600">Waktu Catat</td>
                                <td class="py-0.5 px-1 text-slate-400">:</td>
                                <td class="py-0.5 text-slate-900">{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                @if($transaction->note)
                <div class="mt-2 pt-1.5 border-t border-slate-300 text-[8pt] text-slate-800 leading-tight">
                    <span class="font-bold text-slate-900">Catatan:</span> {{ $transaction->note }}
                </div>
                @endif
            </div>
        </div>

        {{-- Table Rincian Barang (.bmi-print-table) --}}
        <div class="mb-5">
            <table class="bmi-print-table w-full">
                <thead>
                    <tr>
                        <th style="width: 6%; text-align: center;">No</th>
                        <th style="width: 20%; text-align: left;">Kode SKU</th>
                        <th style="width: 42%; text-align: left;">Nama Barang</th>
                        <th style="width: 18%; text-align: left;">Kategori</th>
                        <th style="width: 14%; text-align: center;">Kuantitas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaction->items as $idx => $item)
                    <tr>
                        <td style="text-align: center;" class="tabular-nums">{{ $idx + 1 }}</td>
                        <td class="font-mono font-bold">{{ $item->product->sku }}</td>
                        <td class="font-semibold">{{ $item->product->name }}</td>
                        <td>{{ $item->product->category->name }}</td>
                        <td style="text-align: center;">
                            <span class="font-bold">{{ $item->quantity }}</span>
                            <span class="text-[7.5pt] ml-0.5">{{ $item->product->unit->symbol }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="font-bold bg-slate-100">
                        <td colspan="4" style="text-align: right; padding-right: 10px;">
                            Total Kuantitas Mutasi ({{ $transaction->items->count() }} Jenis Barang):
                        </td>
                        <td style="text-align: center; font-size: 9.5pt;">
                            {{ $transaction->items->sum('quantity') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Tanda Tangan / Signature Block (Break Inside Avoid Table) --}}
        <div class="mt-6 pt-3 border-t border-slate-400" style="break-inside: avoid; page-break-inside: avoid;">
            <table class="w-full text-center text-[8.5pt]" style="border-collapse: collapse; border: none !important;">
                <tbody>
                    <tr>
                        <td style="width: 33.33%; border: none !important; padding: 0 12px; vertical-align: top;">
                            <p class="font-semibold text-slate-700 mb-14">
                                {{ $transaction->type === 'IN' ? 'Diserahkan Oleh (Pemasok),' : 'Diterima Oleh (Penerima),' }}
                            </p>
                            <div class="border-b border-slate-900 mx-4 mb-1"></div>
                            <p class="text-[8pt] text-slate-600">
                                {{ $transaction->type === 'IN' ? ($transaction->supplier->name ?? 'Mitra Supplier') : ($transaction->recipient ?? 'Penerima Barang') }}
                            </p>
                        </td>

                        <td style="width: 33.33%; border: none !important; padding: 0 12px; vertical-align: top;">
                            <p class="font-semibold text-slate-700 mb-14">Petugas Gudang,</p>
                            <div class="border-b border-slate-900 mx-4 mb-1"></div>
                            <p class="text-[8pt] text-slate-900 font-bold">{{ $transaction->creator->name }}</p>
                        </td>

                        <td style="width: 33.33%; border: none !important; padding: 0 12px; vertical-align: top;">
                            <p class="font-semibold text-slate-700 mb-14">Mengetahui / Kepala Gudang,</p>
                            <div class="border-b border-slate-900 mx-4 mb-1"></div>
                            <p class="text-[8pt] text-slate-600">( ........................................ )</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Print Timestamp Footer --}}
        <div class="mt-4 pt-2 border-t border-slate-200 flex items-center justify-between text-[7.5pt] text-slate-500">
            <span>Berkah Mandiri Inventory &bull; Dokumen Bukti Mutasi Gudang</span>
            <span>Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} WIB</span>
        </div>
    </div>
</div>
