<div class="bmi-opname-page space-y-6 min-w-0">
    <div class="flex flex-col gap-4 pb-4 border-b border-slate-200/80 print:hidden">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <x-ui.button
            href="{{ route('opnames.index') }}"
            variant="outline"
            size="sm"
            class="w-full sm:w-auto"
        >
            <x-heroicon-o-arrow-left class="w-4 h-4 text-slate-500" />
            Kembali ke Daftar Opname
        </x-ui.button>
        <div class="h-4 w-px bg-slate-200 hidden sm:block"></div>
        <div class="min-w-0">
            <h1 class="text-lg font-bold text-slate-900 tracking-tight">Detail Stock Opname</h1>
            <p class="text-xs text-slate-500 mt-0.5">Bukti pemeriksaan dan penyesuaian saldo fisik barang gudang.</p>
        </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:flex lg:flex-wrap lg:justify-end gap-2">
            <x-ui.button
                href="{{ route('opnames.edit', $opname->id) }}"
                variant="secondary"
                size="sm"
            >
                <x-heroicon-o-pencil-square class="w-4 h-4" />
                Edit Opname
            </x-ui.button>

            <x-ui.button
                @click="window.SwalConfirm({
                    title: 'Hapus Berita Acara Opname?',
                    text: 'Dokumen Stock Opname {{ addslashes($opname->opname_no) }} akan dihapus dan seluruh penyesuaian stok fisik akan dikembalikan ke posisi semula.',
                    icon: 'warning',
                    confirmText: 'Ya, Hapus Dokumen',
                    variant: 'rose',
                    onConfirm: () => $wire.delete()
                })"
                type="button"
                variant="danger"
                size="sm"
            >
                <x-heroicon-o-trash class="w-4 h-4" />
                Hapus
            </x-ui.button>

            <x-ui.button
                href="{{ route('export.opname', $opname->id) }}"
                variant="success"
                size="sm"
            >
                <x-heroicon-o-arrow-down-tray class="w-4 h-4 text-white" />
                Export Excel (.xls)
            </x-ui.button>

            <x-ui.button
                type="button"
                onclick="window.print()"
                variant="primary"
                size="sm"
            >
                <x-heroicon-o-printer class="w-4 h-4 text-white" />
                Cetak Berita Acara
            </x-ui.button>
        </div>
    </div>

    <div class="bmi-opname-document bg-white border border-slate-200/80 rounded-2xl p-4 sm:p-6 lg:p-8 shadow-2xs text-slate-900 min-w-0 space-y-6 print:bg-white print:text-black print:border-none print:shadow-none print:p-0">
        <div class="bmi-opname-heading flex flex-col lg:flex-row lg:items-start justify-between gap-6 pb-6 border-b border-slate-200/80 print:border-gray-200">
            <div class="min-w-0">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white font-bold text-sm shadow-2xs shrink-0 print:hidden">
                        BMI
                    </div>
                    <div>
                        <span class="text-base font-bold text-slate-900 print:text-black block leading-tight">Berkah Mandiri Inventory</span>
                        <div class="bmi-opname-title text-xs font-semibold text-blue-700 mt-0.5">Berita Acara Stock Opname</div>
                    </div>
                </div>
                <p class="text-xs text-slate-500 print:text-gray-600 mt-3 leading-relaxed">Dokumen Resmi Bukti Pemeriksaan & Penyesuaian Saldo Fisik Barang Gudang</p>
            </div>

            <div class="bmi-opname-reference min-w-0 text-left lg:text-right">
                <x-ui.badge variant="info" class="print:bg-gray-100 print:text-black">
                    <x-heroicon-s-clipboard-document-check class="w-3.5 h-3.5 mr-1 shrink-0" />
                    Status: Selesai / Terverifikasi
                </x-ui.badge>
                <div class="text-xl sm:text-2xl font-bold text-slate-900 print:text-black font-mono tracking-tight mt-2 break-words">
                    {{ $opname->opname_no }}
                </div>
                <div class="text-xs text-slate-500 print:text-gray-600">
                    Tanggal Perhitungan: {{ $opname->opname_date->format('d F Y') }}
                </div>
            </div>
        </div>

        <div class="bmi-opname-metadata grid grid-cols-1 md:grid-cols-3 gap-5 sm:gap-6 pb-6 border-b border-slate-100 print:border-gray-200 text-xs">
            <div class="min-w-0">
                <span class="text-slate-500 print:text-gray-500 font-semibold block mb-1.5">
                    Petugas Pelaksana Pemeriksa
                </span>
                <div class="text-sm font-bold text-slate-900 print:text-black break-words">{{ $opname->creator->name }}</div>
                <div class="text-slate-500 print:text-gray-600 capitalize">Hak Akses: {{ $opname->creator->role }}</div>
                <div class="text-slate-500 print:text-gray-600 text-[11px] mt-1">Waktu Eksekusi Sistem: {{ $opname->created_at->format('d/m/Y H:i:s') }}</div>
            </div>

            <div class="md:col-span-2 min-w-0">
                <span class="text-slate-500 print:text-gray-500 font-semibold block mb-1.5">
                    Catatan / Berita Acara
                </span>
                <div class="p-3.5 rounded-xl bg-slate-50 print:bg-gray-50 border border-slate-200 print:border-gray-200 text-slate-700 print:text-gray-800 whitespace-pre-line break-words leading-relaxed">
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
        <div class="bmi-opname-summary grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 print:grid-cols-4">
            <x-kpi-card label="Total Diperiksa" :value="$totalCount . ' Barang'" tone="blue">
                <x-slot:icon><x-heroicon-o-clipboard-document-check class="w-4 h-4" /></x-slot:icon>
            </x-kpi-card>
            <x-kpi-card label="Stok Sesuai" :value="$matchedCount . ' Barang'" tone="emerald">
                <x-slot:icon><x-heroicon-o-check-circle class="w-4 h-4" /></x-slot:icon>
            </x-kpi-card>
            <x-kpi-card label="Kurang / Rusak" :value="$lossCount . ' Barang'" tone="rose">
                <x-slot:icon><x-heroicon-o-arrow-trending-down class="w-4 h-4" /></x-slot:icon>
            </x-kpi-card>
            <x-kpi-card label="Lebih / Surplus" :value="$surplusCount . ' Barang'" tone="blue">
                <x-slot:icon><x-heroicon-o-arrow-trending-up class="w-4 h-4" /></x-slot:icon>
            </x-kpi-card>
        </div>

        <div class="space-y-3 min-w-0">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-sm font-bold text-slate-900 print:text-black">Rincian Hasil Pemeriksaan Fisik</h2>
                <x-ui.badge variant="neutral">
                    {{ $totalCount }} jenis barang
                </x-ui.badge>
            </div>

            <x-ui.table class="bmi-opname-items print:overflow-visible print:border-none print:shadow-none [&>div]:print:overflow-visible">
                <x-slot:head>
                    <x-ui.th class="w-12 text-center">No</x-ui.th>
                    <x-ui.th class="w-36 whitespace-nowrap">Kode SKU</x-ui.th>
                    <x-ui.th class="min-w-[200px] print:min-w-0">Nama Barang</x-ui.th>
                    <x-ui.th class="min-w-[130px] print:min-w-0">Kategori</x-ui.th>
                    <x-ui.th align="right" class="w-32 whitespace-nowrap print:whitespace-normal">Stok Sistem</x-ui.th>
                    <x-ui.th align="right" class="w-32 whitespace-nowrap print:whitespace-normal">Stok Fisik</x-ui.th>
                    <x-ui.th align="right" class="w-24 whitespace-nowrap">Selisih</x-ui.th>
                    <x-ui.th class="w-40 whitespace-nowrap print:whitespace-normal">Alasan Penyesuaian</x-ui.th>
                    <x-ui.th class="min-w-[180px] print:min-w-0">Catatan</x-ui.th>
                </x-slot:head>

                @foreach($opname->items as $idx => $item)
                @php $diff = $item->difference; @endphp
                <tr wire:key="opname-item-{{ $item->id }}" class="hover:bg-slate-50/60 transition">
                    <x-ui.td align="center" class="text-slate-500 print:text-gray-600 tabular-nums">
                        {{ $idx + 1 }}
                    </x-ui.td>

                    <x-ui.td class="font-mono font-bold text-blue-700 print:text-black whitespace-nowrap">
                        {{ $item->product->sku }}
                    </x-ui.td>

                    <x-ui.td class="font-semibold text-slate-900 print:text-black leading-relaxed">
                        <div class="max-w-sm whitespace-normal break-words">{{ $item->product->name }}</div>
                    </x-ui.td>

                    <x-ui.td class="text-slate-600 print:text-gray-600">
                        {{ $item->product->category->name }}
                    </x-ui.td>

                    <x-ui.td align="right" :numeric="true" class="text-slate-600 font-semibold">
                        {{ $item->system_stock }}
                        <span class="font-normal text-slate-500 text-[11px] ml-0.5">{{ $item->product->unit->symbol }}</span>
                    </x-ui.td>

                    <x-ui.td align="right" :numeric="true" class="font-bold text-slate-900 print:text-black">
                        {{ $item->physical_stock }}
                        <span class="font-normal text-slate-500 text-[11px] ml-0.5">{{ $item->product->unit->symbol }}</span>
                    </x-ui.td>

                    <x-ui.td align="right" :numeric="true">
                        @if($diff === 0)
                            <span class="text-emerald-700 font-bold">0</span>
                        @elseif($diff < 0)
                            <span class="text-rose-700 font-bold">{{ $diff }}</span>
                        @else
                            <span class="text-blue-700 font-bold">+{{ $diff }}</span>
                        @endif
                    </x-ui.td>

                    <x-ui.td class="whitespace-nowrap print:whitespace-normal">
                        @php
                            $badgeVariant = match($item->reason) {
                                'SESUAI' => 'success',
                                'RUSAK', 'HILANG' => 'danger',
                                default => 'warning',
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
                        <x-ui.badge :variant="$badgeVariant" size="md">
                            {{ $reasonLabel }}
                        </x-ui.badge>
                    </x-ui.td>

                    <x-ui.td class="text-slate-600 print:text-gray-600 text-xs leading-relaxed">
                        <p class="max-w-sm whitespace-pre-line break-words">{{ $item->item_notes ?: '-' }}</p>
                    </x-ui.td>
                </tr>
                @endforeach
            </x-ui.table>
        </div>
    </div>
</div>

