<div class="space-y-5 min-w-0">
    {{-- Compact Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                Dashboard Gudang
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Pantau persediaan dan aktivitas operasional gudang.
            </p>
        </div>

        <div class="flex items-center gap-2.5 w-full sm:w-auto shrink-0">
            <a
                href="{{ route('transactions.in') }}"
                class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-2xs transition cursor-pointer"
            >
                <x-heroicon-o-arrow-down-tray class="w-4 h-4 shrink-0" />
                <span>Barang Masuk</span>
            </a>

            <a
                href="{{ route('transactions.out') }}"
                class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-semibold text-xs shadow-2xs transition cursor-pointer"
            >
                <x-heroicon-o-arrow-up-tray class="w-4 h-4 text-slate-500 shrink-0" />
                <span>Barang Keluar</span>
            </a>
        </div>
    </div>

    {{-- Statistics Overview (5 KPI Cards with Asset Valuation) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
        <x-kpi-card
            label="Valuasi Aset Gudang"
            :value="'Rp ' . number_format($stats['total_inventory_valuation'] ?? 0, 0, ',', '.')"
            description="Estimasi total modal barang fisik"
            tone="emerald"
        >
            <x-slot:icon>
                <x-heroicon-o-banknotes class="w-4 h-4" />
            </x-slot:icon>
        </x-kpi-card>

        <x-kpi-card
            label="Total Jenis Barang"
            :value="$stats['total_products']"
            :description="$stats['active_products'] . ' barang aktif terdaftar'"
            tone="blue"
        >
            <x-slot:icon>
                <x-heroicon-o-archive-box class="w-4 h-4" />
            </x-slot:icon>
        </x-kpi-card>

        <x-kpi-card
            label="Barang Perlu Restock"
            :value="$stats['low_stock_count']"
            :description="$stats['out_of_stock_count'] > 0 ? $stats['out_of_stock_count'] . ' barang habis, perlu segera restock' : 'Stok berada ≤ batas minimum'"
            tone="amber"
        >
            <x-slot:icon>
                <x-heroicon-o-exclamation-triangle class="w-4 h-4" />
            </x-slot:icon>
        </x-kpi-card>

        <x-kpi-card
            label="Barang Masuk Hari Ini"
            :value="$stats['stock_in_today']"
            description="Faktur transaksi penerimaan hari ini"
            tone="emerald"
        >
            <x-slot:icon>
                <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
            </x-slot:icon>
        </x-kpi-card>

        <x-kpi-card
            label="Barang Keluar Hari Ini"
            :value="$stats['stock_out_today']"
            description="Faktur transaksi pengeluaran hari ini"
            tone="rose"
        >
            <x-slot:icon>
                <x-heroicon-o-arrow-up-tray class="w-4 h-4" />
            </x-slot:icon>
        </x-kpi-card>
    </div>

    {{-- Main Two-Column Content (55% / 45% on Desktop) --}}
    <div class="grid grid-cols-1 lg:grid-cols-[11fr_9fr] gap-5 items-start">
        {{-- Left Column: Stok Perlu Perhatian / Barang Perlu Restock --}}
        <section class="min-w-0 bg-white border border-slate-200 rounded-xl shadow-2xs overflow-hidden">
            <div class="px-4 sm:px-5 py-4 border-b border-slate-200 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-sm font-semibold text-slate-900">Stok Perlu Perhatian</h2>
                        @if($stats['low_stock_count'] > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200 tabular-nums">
                                {{ $stats['low_stock_count'] }} item
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">Barang perlu restock karena stok berada di bawah batas minimum</p>
                </div>
                <a
                    href="{{ route('products.index') }}"
                    class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-700 shrink-0 transition"
                >
                    Lihat Semua
                    <x-heroicon-o-arrow-right class="w-3.5 h-3.5" />
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50/70 text-slate-500 border-b border-slate-200">
                            <th class="px-4 py-2.5 font-semibold whitespace-nowrap">SKU</th>
                            <th class="px-4 py-2.5 font-semibold">Nama Barang</th>
                            <th class="px-4 py-2.5 font-semibold text-right whitespace-nowrap">Stok Tersedia</th>
                            <th class="px-4 py-2.5 font-semibold text-right whitespace-nowrap">Batas Minimum</th>
                            <th class="px-4 py-2.5 font-semibold text-center whitespace-nowrap">Status</th>
                            <th class="px-4 py-2.5 font-semibold text-right whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($stats['low_stock_items'] as $item)
                            <tr wire:key="low-stock-{{ $item->id }}" class="hover:bg-slate-50/80 transition">
                                <td class="px-4 py-3 font-mono text-[11px] font-semibold text-slate-600 whitespace-nowrap">
                                    {{ $item->sku }}
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-900 min-w-[160px] max-w-[260px] leading-snug">
                                    {{ $item->name }}
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap font-semibold tabular-nums {{ $item->current_stock <= 0 ? 'text-rose-600' : 'text-amber-600' }}">
                                    {{ $item->current_stock }} <span class="text-slate-400 font-normal">{{ $item->unit?->symbol }}</span>
                                </td>
                                <td class="px-4 py-3 text-right text-slate-600 whitespace-nowrap tabular-nums">
                                    {{ $item->minimum_stock }} <span class="text-slate-400">{{ $item->unit?->symbol }}</span>
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    @if($item->current_stock <= 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            Stok Habis
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            Menipis
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a
                                        href="{{ route('stock-card', ['productId' => $item->id]) }}"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold text-blue-600 hover:text-blue-700 hover:bg-blue-50 transition"
                                    >
                                        Kartu Stok
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center">
                                    <div class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100 mb-2">
                                        <x-heroicon-o-check-circle class="w-5 h-5" />
                                    </div>
                                    <p class="text-xs font-semibold text-slate-800">Stok Barang Aman</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Tidak ada barang aktif yang berada di bawah batas minimum saat ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Right Column: Aktivitas Mutasi Terbaru --}}
        <section class="min-w-0 bg-white border border-slate-200 rounded-xl shadow-2xs overflow-hidden">
            <div class="px-4 sm:px-5 py-4 border-b border-slate-200 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <h2 class="text-sm font-semibold text-slate-900">Aktivitas Mutasi Terbaru</h2>
                    <p class="text-xs text-slate-500 mt-0.5">5 transaksi penerimaan dan pengeluaran terakhir</p>
                </div>
                <a
                    href="{{ route('transactions.index') }}"
                    class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-700 shrink-0 transition"
                >
                    Lihat Semua
                    <x-heroicon-o-arrow-right class="w-3.5 h-3.5" />
                </a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($stats['recent_transactions'] as $tx)
                    <div wire:key="recent-tx-{{ $tx->id }}" class="px-4 sm:px-5 py-3 flex items-center justify-between gap-3 hover:bg-slate-50/80 transition">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="inline-flex items-center justify-center w-9 h-6 rounded-md text-[11px] font-bold shrink-0 {{ $tx->type === 'IN' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                {{ $tx->type }}
                            </span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-semibold text-slate-900 font-mono truncate">{{ $tx->transaction_no }}</span>
                                    <span class="text-[11px] text-slate-400 shrink-0 tabular-nums">{{ $tx->transaction_date?->format('d/m/Y') ?? '-' }}</span>
                                </div>
                                <p class="text-xs text-slate-500 truncate mt-0.5">
                                    {{ $tx->type === 'IN' ? 'Supplier: ' . ($tx->supplier?->name ?? '-') : 'Tujuan: ' . ($tx->recipient ?? '-') }}
                                </p>
                            </div>
                        </div>

                        <a
                            href="{{ route('transactions.show', $tx->id) }}"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium text-slate-600 hover:text-blue-600 hover:bg-slate-100/80 shrink-0 transition"
                            title="Lihat detail transaksi"
                        >
                            <span>Detail</span>
                            <x-heroicon-o-chevron-right class="w-3.5 h-3.5" />
                        </a>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center">
                        <div class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 text-slate-500 border border-slate-200 mb-2">
                            <x-heroicon-o-clipboard-document-list class="w-5 h-5" />
                        </div>
                        <p class="text-xs font-semibold text-slate-800">Belum Ada Aktivitas</p>
                        <p class="text-xs text-slate-500 mt-0.5">Transaksi barang masuk dan keluar terbaru akan tampil di sini.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    {{-- Pemeriksaan & Rekonsiliasi Saldo Stok --}}
    <section class="min-w-0 bg-white border border-slate-200 rounded-xl shadow-2xs overflow-hidden">
        <div class="px-4 sm:px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 {{ $hasAudited ? 'border-b border-slate-200' : '' }}">
            <div class="min-w-0">
                <h2 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                    <x-heroicon-o-shield-check class="w-4 h-4 text-emerald-600 shrink-0" />
                    <span>Pemeriksaan & Rekonsiliasi Saldo Stok</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Verifikasi kecocokan antara saldo stok tercatat saat ini dengan akumulasi riwayat mutasi barang masuk dan keluar.
                </p>
            </div>

            <button
                @click="window.SwalConfirm({
                    title: 'Jalankan Rekonsiliasi Saldo Stok?',
                    text: 'Sistem akan memeriksa kecocokan antara stok tercatat dengan seluruh riwayat mutasi barang masuk, keluar, dan stock opname.',
                    icon: 'question',
                    confirmText: 'Ya, Periksa Sekarang',
                    cancelText: 'Batal',
                    variant: 'blue',
                    onConfirm: () => $wire.runAudit()
                })"
                wire:loading.attr="disabled"
                wire:target="runAudit"
                type="button"
                class="inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-semibold shadow-2xs transition cursor-pointer shrink-0 disabled:opacity-60 disabled:cursor-not-allowed"
            >
                <span wire:loading.remove wire:target="runAudit" class="inline-flex items-center gap-1.5">
                    <x-heroicon-o-arrow-path class="w-3.5 h-3.5 text-slate-500" />
                    Jalankan Rekonsiliasi
                </span>
                <span wire:loading wire:target="runAudit" class="inline-flex items-center gap-2 text-blue-600">
                    <svg class="animate-spin h-3.5 w-3.5 text-blue-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    Memeriksa Riwayat...
                </span>
            </button>
        </div>

        @if($hasAudited)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50/70 text-slate-500 border-b border-slate-200">
                            <th class="px-4 sm:px-5 py-2.5 font-semibold whitespace-nowrap">SKU</th>
                            <th class="px-4 sm:px-5 py-2.5 font-semibold">Nama Barang</th>
                            <th class="px-4 sm:px-5 py-2.5 font-semibold text-center whitespace-nowrap">Stok Tercatat</th>
                            <th class="px-4 sm:px-5 py-2.5 font-semibold text-center whitespace-nowrap">Hasil Riwayat Mutasi</th>
                            <th class="px-4 sm:px-5 py-2.5 font-semibold text-center whitespace-nowrap">Status Rekonsiliasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($auditResults as $row)
                            <tr wire:key="audit-row-{{ $row['sku'] }}" class="hover:bg-slate-50/80 transition">
                                <td class="px-4 sm:px-5 py-3 font-mono text-[11px] font-semibold text-slate-600 whitespace-nowrap">{{ $row['sku'] }}</td>
                                <td class="px-4 sm:px-5 py-3 text-slate-900 font-medium min-w-[160px]">{{ $row['name'] }}</td>
                                <td class="px-4 sm:px-5 py-3 text-center font-semibold text-slate-800 tabular-nums">{{ $row['actual'] }}</td>
                                <td class="px-4 sm:px-5 py-3 text-center font-semibold text-slate-800 tabular-nums">{{ $row['calculated'] }}</td>
                                <td class="px-4 sm:px-5 py-3 text-center whitespace-nowrap">
                                    @if($row['is_matched'])
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <x-heroicon-s-check-circle class="w-3.5 h-3.5 text-emerald-600" />
                                            Sinkron (Sesuai)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            <x-heroicon-s-x-circle class="w-3.5 h-3.5 text-rose-600" />
                                            Selisih (Perlu Cek)
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-slate-500">
                                    Tidak ada data barang aktif untuk direkonsiliasi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
