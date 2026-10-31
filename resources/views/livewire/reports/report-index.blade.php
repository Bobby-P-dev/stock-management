<div class="bmi-report-page space-y-6 min-w-0">
    <style>
        @media print {
            @page {
                size: A4 landscape;
                margin: 10mm 12mm;
            }
        }
    </style>

    {{-- Print Document Header (Visible ONLY on Print) --}}
    <div class="hidden print:block mb-4 pb-3 border-b-2 border-slate-800">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="text-xs font-bold tracking-tight text-slate-800 uppercase">
                    Berkah Mandiri Inventory
                </div>
                <h1 class="text-base font-extrabold text-slate-900 mt-0.5 uppercase tracking-tight">
                    @if($activeTab === 'stock')
                        Laporan Rekapitulasi Posisi Stok &amp; Ranking Perputaran Barang
                    @elseif($activeTab === 'in')
                        Laporan Rekapitulasi Barang Masuk (Stock In)
                    @else
                        Laporan Rekapitulasi Barang Keluar (Stock Out)
                    @endif
                </h1>
                <div class="text-[9pt] text-slate-600 mt-1 flex flex-wrap items-center gap-x-2">
                    @if($activeTab === 'stock')
                        <span>Tipe Laporan: <strong>Posisi Stok &amp; Analisis Mutasi</strong></span>
                        <span>&bull;</span>
                        <span>Periode: <strong>{{ ($stockStartDate ? \Carbon\Carbon::parse($stockStartDate)->format('d/m/Y') : 'Semua Waktu') . ($stockEndDate ? ' s/d ' . \Carbon\Carbon::parse($stockEndDate)->format('d/m/Y') : ($stockStartDate ? ' s/d Sekarang' : '')) }}</strong></span>
                        @if($stockVelocity)
                            <span>&bull;</span>
                            <span>Perputaran: <strong>{{ str_replace('_', ' ', $stockVelocity) }}</strong></span>
                        @endif
                        @if($stockCategory)
                            @php $catObj = $categories->firstWhere('id', $stockCategory); @endphp
                            <span>&bull;</span>
                            <span>Kategori: <strong>{{ $catObj ? $catObj->name : 'ID ' . $stockCategory }}</strong></span>
                        @endif
                    @elseif($activeTab === 'in')
                        <span>Tipe Laporan: <strong>Faktur Barang Masuk Dari Supplier</strong></span>
                        <span>&bull;</span>
                        <span>Periode: <strong>{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') . ' s/d ' . \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</strong></span>
                        @if($supplierId)
                            <span>&bull;</span>
                            <span>Supplier: <strong>{{ $suppliers->firstWhere('value', $supplierId)['label'] ?? 'ID: ' . $supplierId }}</strong></span>
                        @endif
                    @else
                        <span>Tipe Laporan: <strong>Faktur Barang Keluar / Distribusi</strong></span>
                        <span>&bull;</span>
                        <span>Periode: <strong>{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') . ' s/d ' . \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</strong></span>
                        @if($recipient)
                            <span>&bull;</span>
                            <span>Penerima: <strong>{{ $recipient }}</strong></span>
                        @endif
                    @endif
                </div>
            </div>

            <div class="text-right text-[8.5pt] text-slate-600 shrink-0">
                <div>Dicetak: <strong>{{ now()->format('d/m/Y H:i') }}</strong></div>
                <div>Oleh: <strong>{{ auth()->user()->name }}</strong></div>
            </div>
        </div>
    </div>

    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200/80 print:hidden">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200/60 flex items-center justify-center text-blue-600 shrink-0 shadow-2xs mt-0.5">
                <x-heroicon-o-chart-bar class="w-5 h-5 stroke-[2]" />
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Pusat Laporan Inventaris & Mutasi</h1>
                <p class="text-xs text-slate-500 mt-1">Rekapitulasi data posisi stok, faktur barang masuk dari supplier, dan pengeluaran barang.</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
            @if($activeTab === 'stock')
            <x-ui.button
                href="{{ route('export.stock', ['category' => $stockCategory, 'status' => $stockStatus, 'sort_by' => $stockSortBy, 'velocity' => $stockVelocity, 'start_date' => $stockStartDate, 'end_date' => $stockEndDate]) }}"
                variant="outline"
                size="sm"
                class="border-emerald-200 text-emerald-700 hover:bg-emerald-50 hover:border-emerald-300"
            >
                <x-slot:icon>
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4 text-emerald-600 mr-1.5" />
                </x-slot:icon>
                Export Excel (.xls)
            </x-ui.button>
            @elseif($activeTab === 'in')
            <x-ui.button
                href="{{ route('export.stock-in', ['start_date' => $startDate, 'end_date' => $endDate, 'supplier_id' => $supplierId]) }}"
                variant="outline"
                size="sm"
                class="border-emerald-200 text-emerald-700 hover:bg-emerald-50 hover:border-emerald-300"
            >
                <x-slot:icon>
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4 text-emerald-600 mr-1.5" />
                </x-slot:icon>
                Export Excel (.xls)
            </x-ui.button>
            @else
            <x-ui.button
                href="{{ route('export.stock-out', ['start_date' => $startDate, 'end_date' => $endDate, 'recipient' => $recipient]) }}"
                variant="outline"
                size="sm"
                class="border-emerald-200 text-emerald-700 hover:bg-emerald-50 hover:border-emerald-300"
            >
                <x-slot:icon>
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4 text-emerald-600 mr-1.5" />
                </x-slot:icon>
                Export Excel (.xls)
            </x-ui.button>
            @endif

            <x-ui.button
                type="button"
                onclick="window.print()"
                variant="primary"
                size="sm"
            >
                <x-slot:icon>
                    <x-heroicon-o-printer class="w-4 h-4 mr-1.5" />
                </x-slot:icon>
                Cetak Laporan
            </x-ui.button>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="border-b border-slate-200/80 print:hidden overflow-x-auto scrollbar-none">
        <nav class="flex items-center gap-1 sm:gap-2 -mb-px min-w-max" aria-label="Tabs Laporan">
            <button
                wire:click="setTab('stock')"
                type="button"
                class="inline-flex items-center gap-2 px-4 py-3 text-xs font-semibold border-b-2 transition cursor-pointer {{ $activeTab === 'stock' ? 'border-blue-600 text-blue-700 bg-blue-50/50 rounded-t-xl' : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-50 rounded-t-xl' }}"
            >
                <x-heroicon-o-cube class="w-4 h-4 {{ $activeTab === 'stock' ? 'text-blue-600' : 'text-slate-400' }}" />
                <span>1. Posisi Stok Barang</span>
            </button>

            <button
                wire:click="setTab('in')"
                type="button"
                class="inline-flex items-center gap-2 px-4 py-3 text-xs font-semibold border-b-2 transition cursor-pointer {{ $activeTab === 'in' ? 'border-emerald-600 text-emerald-700 bg-emerald-50/50 rounded-t-xl' : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-50 rounded-t-xl' }}"
            >
                <x-heroicon-o-arrow-down-tray class="w-4 h-4 {{ $activeTab === 'in' ? 'text-emerald-600' : 'text-slate-400' }}" />
                <span>2. Barang Masuk (Stock In)</span>
            </button>

            <button
                wire:click="setTab('out')"
                type="button"
                class="inline-flex items-center gap-2 px-4 py-3 text-xs font-semibold border-b-2 transition cursor-pointer {{ $activeTab === 'out' ? 'border-rose-600 text-rose-700 bg-rose-50/50 rounded-t-xl' : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-50 rounded-t-xl' }}"
            >
                <x-heroicon-o-arrow-up-tray class="w-4 h-4 {{ $activeTab === 'out' ? 'text-rose-600' : 'text-slate-400' }}" />
                <span>3. Barang Keluar (Stock Out)</span>
            </button>
        </nav>
    </div>

    @if($activeTab === 'stock')
    <div class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 print:hidden">
            <x-kpi-card
                label="Total Ragam Barang"
                :value="$stockSummary['total_products']"
                unit="SKU"
                tone="blue"
                :description="count($stockSummary['unit_totals'] ?? []) . ' ragam satuan terdaftar'"
            >
                <x-slot:icon>
                    <x-heroicon-o-archive-box class="w-5 h-5" />
                </x-slot:icon>
            </x-kpi-card>

            <x-kpi-card
                label="Fast Moving (Paling Laku)"
                :value="$stockSummary['fast_moving'] ?? 0"
                unit="item"
                tone="emerald"
                description="Perputaran mutasi tinggi"
            >
                <x-slot:icon>
                    <x-heroicon-o-arrow-trending-up class="w-5 h-5" />
                </x-slot:icon>
            </x-kpi-card>

            <x-kpi-card
                label="Medium Moving"
                :value="$stockSummary['medium_moving'] ?? 0"
                unit="item"
                tone="slate"
                description="Perputaran keluar-masuk stabil"
            >
                <x-slot:icon>
                    <x-heroicon-o-arrows-right-left class="w-5 h-5" />
                </x-slot:icon>
            </x-kpi-card>

            <x-kpi-card
                label="Slow Moving"
                :value="$stockSummary['slow_moving'] ?? 0"
                unit="item"
                tone="amber"
                description="Jarang bergerak keluar"
            >
                <x-slot:icon>
                    <x-heroicon-o-clock class="w-5 h-5" />
                </x-slot:icon>
            </x-kpi-card>

            <x-kpi-card
                label="Non-Moving (Stok Diam)"
                :value="$stockSummary['non_moving'] ?? 0"
                unit="item"
                tone="rose"
                description="Tanpa riwayat transaksi"
            >
                <x-slot:icon>
                    <x-heroicon-o-archive-box-x-mark class="w-5 h-5" />
                </x-slot:icon>
            </x-kpi-card>
        </div>

        {{-- Filter Toolbar & Periode Analisis Mutasi --}}
        <div class="bg-white border border-slate-200/80 p-4 sm:p-5 rounded-2xl shadow-2xs print:hidden space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 leading-snug">Urutan Ranking Data</label>
                    <x-searchable-select
                        wire:model.live="stockSortBy"
                        :options="[
                            ['value' => 'turnover', 'label' => 'Paling Cepat (Total In + Out)'],
                            ['value' => 'most_sold', 'label' => 'Paling Laku (Total Keluar)'],
                            ['value' => 'most_in', 'label' => 'Paling Sering Masuk'],
                            ['value' => 'stock_desc', 'label' => 'Stok Fisik Terbanyak'],
                            ['value' => 'stock_asc', 'label' => 'Stok Fisik Tersedikit'],
                            ['value' => 'name', 'label' => 'Nama Barang (A-Z)']
                        ]"
                        placeholder="Urutan Data"
                        searchPlaceholder="Cari urutan..."
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 leading-snug">Status Kecepatan Perputaran</label>
                    <x-searchable-select
                        wire:model.live="stockVelocity"
                        :options="[
                            ['value' => '', 'label' => 'Semua Perputaran'],
                            ['value' => 'FAST_MOVING', 'label' => 'Fast Moving (Paling Cepat)'],
                            ['value' => 'MEDIUM_MOVING', 'label' => 'Medium Moving (Sedang)'],
                            ['value' => 'SLOW_MOVING', 'label' => 'Slow Moving (Lambat)'],
                            ['value' => 'NON_MOVING', 'label' => 'Non-Moving (Stok Diam)']
                        ]"
                        placeholder="Semua Perputaran"
                        searchPlaceholder="Cari kecepatan perputaran..."
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 leading-snug">Filter Kategori</label>
                    <x-searchable-select
                        wire:model.live="stockCategory"
                        :options="collect([['value' => '', 'label' => 'Semua Kategori']])->concat($categories)"
                        placeholder="Semua Kategori"
                        searchPlaceholder="Cari kategori..."
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 leading-snug">Status Ketersediaan</label>
                    <x-searchable-select
                        wire:model.live="stockStatus"
                        :options="[
                            ['value' => '', 'label' => 'Semua Status Stok'],
                            ['value' => 'safe', 'label' => 'Stok Aman'],
                            ['value' => 'low', 'label' => 'Stok Menipis'],
                            ['value' => 'out', 'label' => 'Stok Habis']
                        ]"
                        placeholder="Semua Status Stok"
                        searchPlaceholder="Cari status stok..."
                    />
                </div>
            </div>

            {{-- Redesigned Date Range Filter: Periode Analisis Mutasi --}}
            <div class="pt-4 border-t border-slate-100 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5">
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-3.5 bg-blue-600 rounded-full"></span>
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                            Periode Analisis Mutasi
                        </h3>
                    </div>
                    <div class="flex items-center gap-3">
                        @if($stockStartDate || $stockEndDate)
                            <button
                                type="button"
                                wire:click="$set('stockStartDate', null); $set('stockEndDate', null);"
                                class="inline-flex items-center gap-1 text-[11px] font-semibold text-rose-600 hover:text-rose-700 hover:underline cursor-pointer transition"
                            >
                                <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
                                <span>Reset ke Seluruh Waktu</span>
                            </button>
                        @endif
                        <span class="text-[11px] text-slate-400 hidden sm:inline">
                            *Kosongkan tanggal untuk menganalisis seluruh data mutasi sejak awal
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 max-w-2xl">
                    <x-ui.input
                        wire:model.live="stockStartDate"
                        type="date"
                        label="Dari Tanggal"
                    >
                        <x-slot:icon>
                            <x-heroicon-o-calendar class="w-4 h-4 text-slate-400" />
                        </x-slot:icon>
                    </x-ui.input>

                    <x-ui.input
                        wire:model.live="stockEndDate"
                        type="date"
                        label="Sampai Tanggal"
                    >
                        <x-slot:icon>
                            <x-heroicon-o-calendar class="w-4 h-4 text-slate-400" />
                        </x-slot:icon>
                    </x-ui.input>
                </div>

                <p class="text-[11px] text-slate-400 sm:hidden">
                    *Kosongkan tanggal untuk menganalisis seluruh data mutasi sejak awal.
                </p>
            </div>
        </div>

        <x-ui.table
            loadingTarget="stockSortBy,stockVelocity,stockCategory,stockStatus,stockStartDate,stockEndDate,setTab"
            class="bmi-report-table print:border-none print:shadow-none print:rounded-none [&>div]:print:overflow-visible"
        >
            <x-slot:head>
                <x-ui.th align="center" class="w-10 print:w-[4%]">Rank</x-ui.th>
                <x-ui.th class="w-24 print:w-[10%] whitespace-nowrap">SKU</x-ui.th>
                <x-ui.th class="min-w-[150px] print:w-[24%]">Nama Barang</x-ui.th>
                <x-ui.th class="w-24 print:w-[11%] whitespace-nowrap">Kategori</x-ui.th>
                <x-ui.th align="right" class="w-16 print:w-[7%] whitespace-nowrap text-emerald-700 print:text-black">Masuk (+)</x-ui.th>
                <x-ui.th align="right" class="w-16 print:w-[7%] whitespace-nowrap text-rose-700 print:text-black">Keluar (-)</x-ui.th>
                <x-ui.th align="right" class="w-20 print:w-[8%] whitespace-nowrap">Total Mutasi</x-ui.th>
                <x-ui.th align="right" class="w-20 print:w-[9%] whitespace-nowrap">Stok Terkini</x-ui.th>
                <x-ui.th align="center" class="w-20 print:w-[8%] whitespace-nowrap">Status</x-ui.th>
                <x-ui.th align="center" class="w-24 print:w-[12%] whitespace-nowrap">Perputaran</x-ui.th>
            </x-slot:head>

            @forelse($stockProducts as $p)
            <x-ui.tr wire:key="stock-item-{{ $p->id }}">
                <x-ui.td align="center" class="font-mono font-semibold text-slate-700 print:text-black print:text-[8.5pt]">
                    {{ $p->rank ?? '-' }}
                </x-ui.td>
                <x-ui.td class="font-mono font-semibold text-slate-900 print:text-black whitespace-nowrap print:text-[8.5pt]">
                    {{ $p->sku }}
                </x-ui.td>
                <x-ui.td class="print:text-[8.5pt]">
                    <div class="font-medium text-slate-900 print:text-black leading-snug">{{ $p->name }}</div>
                    <div class="text-[11px] print:text-[7.5pt] text-slate-400 print:text-slate-500 mt-0.5">Batas Min: {{ $p->minimum_stock }} {{ $p->unit->symbol ?? '' }}</div>
                </x-ui.td>
                <x-ui.td class="text-slate-600 print:text-black whitespace-nowrap print:text-[8.5pt]">
                    {{ $p->category->name ?? '-' }}
                </x-ui.td>
                <x-ui.td align="right" :numeric="true" class="font-semibold text-emerald-600 print:text-black print:text-[8.5pt]">
                    {{ $p->total_in > 0 ? '+'.$p->total_in : '-' }}
                </x-ui.td>
                <x-ui.td align="right" :numeric="true" class="font-semibold text-rose-600 print:text-black print:text-[8.5pt]">
                    {{ $p->total_out > 0 ? '-'.$p->total_out : '-' }}
                </x-ui.td>
                <x-ui.td align="right" :numeric="true" class="font-bold text-slate-900 print:text-black print:text-[8.5pt]">
                    {{ $p->total_movement }}
                </x-ui.td>
                <x-ui.td align="right" :numeric="true" class="font-bold text-slate-900 print:text-black print:text-[8.5pt]">
                    {{ $p->current_stock }} <span class="text-[11px] print:text-[7.5pt] text-slate-400 print:text-slate-500 font-normal">{{ $p->unit->symbol ?? '' }}</span>
                </x-ui.td>
                <x-ui.td align="center" class="print:text-[8.5pt]">
                    @if($p->isOutOfStock())
                    <x-ui.badge variant="danger" size="xs" class="print:border print:border-slate-400 print:text-black print:bg-transparent">Habis</x-ui.badge>
                    @elseif($p->isLowStock())
                    <x-ui.badge variant="warning" size="xs" class="print:border print:border-slate-400 print:text-black print:bg-transparent">Menipis</x-ui.badge>
                    @else
                    <x-ui.badge variant="success" size="xs" class="print:border print:border-slate-400 print:text-black print:bg-transparent">Aman</x-ui.badge>
                    @endif
                </x-ui.td>
                <x-ui.td align="center" class="print:text-[8.5pt]">
                    @if($p->velocity_status === 'FAST_MOVING')
                    <x-ui.badge variant="success" size="xs" class="gap-1.5 print:border print:border-slate-400 print:text-black print:bg-transparent">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 print:hidden"></span>
                        Fast Moving
                    </x-ui.badge>
                    @elseif($p->velocity_status === 'MEDIUM_MOVING')
                    <x-ui.badge variant="neutral" size="xs" class="gap-1.5 print:border print:border-slate-400 print:text-black print:bg-transparent">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-500 print:hidden"></span>
                        Medium
                    </x-ui.badge>
                    @elseif($p->velocity_status === 'SLOW_MOVING')
                    <x-ui.badge variant="warning" size="xs" class="gap-1.5 print:border print:border-slate-400 print:text-black print:bg-transparent">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600 print:hidden"></span>
                        Slow
                    </x-ui.badge>
                    @else
                    <x-ui.badge variant="danger" size="xs" class="gap-1.5 print:border print:border-slate-400 print:text-black print:bg-transparent">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 print:hidden"></span>
                        Non-Moving
                    </x-ui.badge>
                    @endif
                </x-ui.td>
            </x-ui.tr>
            @empty
            <x-ui.tr>
                <x-ui.td colspan="10" class="p-0">
                    <x-ui.empty-state
                        title="Tidak Ada Data Barang"
                        description="Tidak ada data barang sesuai kriteria ranking & filter."
                    >
                        <x-slot:icon>
                            <x-heroicon-o-inbox class="w-5 h-5 text-slate-400" />
                        </x-slot:icon>
                    </x-ui.empty-state>
                </x-ui.td>
            </x-ui.tr>
            @endforelse

            <x-slot:tfoot>
                <tr class="bg-slate-50/90 font-bold text-slate-800 border-t-2 border-slate-200 print:border-t print:border-slate-400">
                    <td colspan="4" class="py-3 px-3 text-right uppercase tracking-wider text-[11px] text-slate-600 print:text-black print:text-[8pt] align-middle">
                        Total Akumulasi (Per Satuan):
                    </td>
                    <td colspan="4" class="py-2.5 px-3 align-middle">
                        <div class="flex flex-wrap items-center justify-end gap-1.5 sm:gap-2">
                            @forelse($stockSummary['unit_totals'] ?? [] as $uSymbol => $uData)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white border border-slate-200/90 text-xs font-mono font-bold text-slate-900 shadow-2xs">
                                    <span>{{ number_format($uData['stock'], 0, ',', '.') }}</span>
                                    <span class="text-[10px] font-sans font-semibold text-slate-500 uppercase">{{ $uSymbol }}</span>
                                </span>
                            @empty
                                <span class="text-xs text-slate-400">-</span>
                            @endforelse
                        </div>
                    </td>
                    <td colspan="2" class="py-3 px-3 text-center text-slate-500 font-semibold print:text-slate-600 text-[11px] print:text-[8pt] align-middle">
                        {{ count($stockProducts) }} Jenis Barang
                    </td>
                </tr>
            </x-slot:tfoot>
        </x-ui.table>
    </div>
    @endif

    @if($activeTab === 'in')
    <div class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end bg-white border border-slate-200/80 p-4 sm:p-5 rounded-2xl shadow-2xs print:hidden">
            <x-ui.input
                wire:model.live="startDate"
                type="date"
                label="Dari Tanggal"
            >
                <x-slot:icon>
                    <x-heroicon-o-calendar class="w-4 h-4 text-slate-400" />
                </x-slot:icon>
            </x-ui.input>

            <x-ui.input
                wire:model.live="endDate"
                type="date"
                label="Sampai Tanggal"
            >
                <x-slot:icon>
                    <x-heroicon-o-calendar class="w-4 h-4 text-slate-400" />
                </x-slot:icon>
            </x-ui.input>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5 leading-snug">Filter Supplier</label>
                <x-searchable-select
                    wire:model.live="supplierId"
                    :options="collect([['value' => '', 'label' => 'Semua Supplier']])->concat($suppliers)"
                    placeholder="Semua Supplier"
                    searchPlaceholder="Cari supplier..."
                />
            </div>
        </div>

        <x-ui.table
            loadingTarget="startDate,endDate,supplierId,setTab"
            class="bmi-report-table print:border-none print:shadow-none print:rounded-none [&>div]:print:overflow-visible"
        >
            <x-slot:head>
                <x-ui.th class="w-44 print:w-[16%] whitespace-nowrap">No. Faktur</x-ui.th>
                <x-ui.th class="w-28 print:w-[11%] whitespace-nowrap">Tanggal</x-ui.th>
                <x-ui.th class="w-48 print:w-[18%] whitespace-nowrap">Supplier</x-ui.th>
                <x-ui.th class="min-w-[240px] print:w-[35%]">Daftar Barang Diterima</x-ui.th>
                <x-ui.th align="right" class="w-28 print:w-[10%] whitespace-nowrap">Total Unit</x-ui.th>
                <x-ui.th class="w-36 print:w-[10%] whitespace-nowrap">Dicatat Oleh</x-ui.th>
            </x-slot:head>

            @php $totalUnitMasuk = 0; @endphp
            @forelse($stockInTransactions as $tx)
            @php $unitSub = $tx->items->sum('quantity'); $totalUnitMasuk += $unitSub; @endphp
            <x-ui.tr wire:key="in-tx-{{ $tx->id }}">
                <x-ui.td class="font-mono font-bold text-emerald-600 print:text-black whitespace-nowrap print:text-[8.5pt]">
                    {{ $tx->transaction_no }}
                </x-ui.td>
                <x-ui.td class="text-slate-600 print:text-black whitespace-nowrap print:text-[8.5pt]">
                    {{ $tx->transaction_date->format('d/m/Y') }}
                </x-ui.td>
                <x-ui.td class="font-medium text-slate-900 print:text-black print:text-[8.5pt]">
                    {{ $tx->supplier->name ?? '-' }}
                </x-ui.td>
                <x-ui.td class="text-slate-600 print:text-black print:text-[8.5pt]">
                    <ul class="space-y-1">
                        @foreach($tx->items as $it)
                        <li class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0 print:hidden"></span>
                            <span>{{ $it->product->name }}</span>
                            <span class="font-semibold text-slate-900 print:text-black tabular-nums">({{ number_format($it->quantity) }} {{ $it->product->unit->symbol }})</span>
                        </li>
                        @endforeach
                    </ul>
                </x-ui.td>
                <x-ui.td align="right" :numeric="true" class="font-bold text-slate-900 print:text-black print:text-[8.5pt]">
                    +{{ number_format($unitSub) }}
                </x-ui.td>
                <x-ui.td class="text-slate-500 print:text-black whitespace-nowrap print:text-[8.5pt]">
                    {{ $tx->creator->name }}
                </x-ui.td>
            </x-ui.tr>
            @empty
            <x-ui.tr>
                <x-ui.td colspan="6" class="p-0">
                    <x-ui.empty-state
                        title="Tidak Ada Data Transaksi Masuk"
                        description="Tidak ada data transaksi masuk pada rentang tanggal atau supplier terpilih."
                    >
                        <x-slot:icon>
                            <x-heroicon-o-inbox class="w-5 h-5 text-slate-400" />
                        </x-slot:icon>
                    </x-ui.empty-state>
                </x-ui.td>
            </x-ui.tr>
            @endforelse

            <x-slot:tfoot>
                @if(count($stockInTransactions) > 0)
                <tr class="border-t border-slate-200 print:border-gray-400 font-bold bg-slate-50/70 print:bg-gray-100">
                    <x-ui.td colspan="4" align="right" class="text-slate-700 print:text-black font-semibold print:text-[8.5pt] align-middle">
                        Total Masuk (Per Satuan):
                    </x-ui.td>
                    <x-ui.td align="right" class="py-2.5 px-3 align-middle">
                        <div class="flex flex-wrap items-center justify-end gap-1.5">
                            @foreach($inUnitTotals as $uSym => $qty)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-200/90 text-xs font-mono font-bold text-emerald-800 shadow-2xs">
                                    <span>+{{ number_format($qty, 0, ',', '.') }}</span>
                                    <span class="text-[10px] font-sans font-semibold text-emerald-600 uppercase">{{ $uSym }}</span>
                                </span>
                            @endforeach
                        </div>
                    </x-ui.td>
                    <x-ui.td class="text-center text-slate-500 font-semibold print:text-slate-600 text-[11px] print:text-[8pt] align-middle">
                        {{ count($stockInTransactions) }} Faktur
                    </x-ui.td>
                </tr>
                @endif
            </x-slot:tfoot>
        </x-ui.table>
    </div>
    @endif

    @if($activeTab === 'out')
    <div class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end bg-white border border-slate-200/80 p-4 sm:p-5 rounded-2xl shadow-2xs print:hidden">
            <x-ui.input
                wire:model.live="startDate"
                type="date"
                label="Dari Tanggal"
            >
                <x-slot:icon>
                    <x-heroicon-o-calendar class="w-4 h-4 text-slate-400" />
                </x-slot:icon>
            </x-ui.input>

            <x-ui.input
                wire:model.live="endDate"
                type="date"
                label="Sampai Tanggal"
            >
                <x-slot:icon>
                    <x-heroicon-o-calendar class="w-4 h-4 text-slate-400" />
                </x-slot:icon>
            </x-ui.input>

            <x-ui.input
                wire:model.live.debounce.300ms="recipient"
                type="text"
                label="Cari Penerima / Divisi"
                placeholder="Nama divisi / orang..."
            >
                <x-slot:icon>
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 text-slate-400" />
                </x-slot:icon>
            </x-ui.input>
        </div>

        <x-ui.table
            loadingTarget="startDate,endDate,recipient,setTab"
            class="bmi-report-table print:border-none print:shadow-none print:rounded-none [&>div]:print:overflow-visible"
        >
            <x-slot:head>
                <x-ui.th class="w-44 print:w-[16%] whitespace-nowrap">No. Transaksi</x-ui.th>
                <x-ui.th class="w-28 print:w-[11%] whitespace-nowrap">Tanggal</x-ui.th>
                <x-ui.th class="w-48 print:w-[18%] whitespace-nowrap">Penerima / Pemohon</x-ui.th>
                <x-ui.th class="min-w-[240px] print:w-[35%]">Rincian Barang Keluar</x-ui.th>
                <x-ui.th align="right" class="w-28 print:w-[10%] whitespace-nowrap">Total Unit</x-ui.th>
                <x-ui.th class="w-36 print:w-[10%] whitespace-nowrap">Dicatat Oleh</x-ui.th>
            </x-slot:head>

            @php $totalUnitKeluar = 0; @endphp
            @forelse($stockOutTransactions as $tx)
            @php $unitSub = $tx->items->sum('quantity'); $totalUnitKeluar += $unitSub; @endphp
            <x-ui.tr wire:key="out-tx-{{ $tx->id }}">
                <x-ui.td class="font-mono font-bold text-rose-600 print:text-black whitespace-nowrap print:text-[8.5pt]">
                    {{ $tx->transaction_no }}
                </x-ui.td>
                <x-ui.td class="text-slate-600 print:text-black whitespace-nowrap print:text-[8.5pt]">
                    {{ $tx->transaction_date->format('d/m/Y') }}
                </x-ui.td>
                <x-ui.td class="font-medium text-slate-900 print:text-black print:text-[8.5pt]">
                    {{ $tx->recipient ?? '-' }}
                </x-ui.td>
                <x-ui.td class="text-slate-600 print:text-black print:text-[8.5pt]">
                    <ul class="space-y-1">
                        @foreach($tx->items as $it)
                        <li class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0 print:hidden"></span>
                            <span>{{ $it->product->name }}</span>
                            <span class="font-semibold text-slate-900 print:text-black tabular-nums">({{ number_format($it->quantity) }} {{ $it->product->unit->symbol }})</span>
                        </li>
                        @endforeach
                    </ul>
                </x-ui.td>
                <x-ui.td align="right" :numeric="true" class="font-bold text-rose-600 print:text-black print:text-[8.5pt]">
                    -{{ number_format($unitSub) }}
                </x-ui.td>
                <x-ui.td class="text-slate-500 print:text-black whitespace-nowrap print:text-[8.5pt]">
                    {{ $tx->creator->name }}
                </x-ui.td>
            </x-ui.tr>
            @empty
            <x-ui.tr>
                <x-ui.td colspan="6" class="p-0">
                    <x-ui.empty-state
                        title="Tidak Ada Data Transaksi Keluar"
                        description="Tidak ada data transaksi keluar pada rentang tanggal terpilih."
                    >
                        <x-slot:icon>
                            <x-heroicon-o-inbox class="w-5 h-5 text-slate-400" />
                        </x-slot:icon>
                    </x-ui.empty-state>
                </x-ui.td>
            </x-ui.tr>
            @endforelse

            <x-slot:tfoot>
                @if(count($stockOutTransactions) > 0)
                <tr class="border-t border-slate-200 print:border-gray-400 font-bold bg-slate-50/70 print:bg-gray-100">
                    <x-ui.td colspan="4" align="right" class="text-slate-700 print:text-black font-semibold print:text-[8.5pt] align-middle">
                        Total Keluar (Per Satuan):
                    </x-ui.td>
                    <x-ui.td align="right" class="py-2.5 px-3 align-middle">
                        <div class="flex flex-wrap items-center justify-end gap-1.5">
                            @foreach($outUnitTotals as $uSym => $qty)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-rose-50 border border-rose-200/90 text-xs font-mono font-bold text-rose-800 shadow-2xs">
                                    <span>-{{ number_format($qty, 0, ',', '.') }}</span>
                                    <span class="text-[10px] font-sans font-semibold text-rose-600 uppercase">{{ $uSym }}</span>
                                </span>
                            @endforeach
                        </div>
                    </x-ui.td>
                    <x-ui.td class="text-center text-slate-500 font-semibold print:text-slate-600 text-[11px] print:text-[8pt] align-middle">
                        {{ count($stockOutTransactions) }} Transaksi
                    </x-ui.td>
                </tr>
                @endif
            </x-slot:tfoot>
        </x-ui.table>
    </div>
    @endif
</div>
