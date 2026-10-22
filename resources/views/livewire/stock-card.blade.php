<div class="space-y-5 min-w-0">
    {{-- Page Header (Screen Only) --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:hidden">
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Kartu Stok (Stock Card)</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Riwayat pergerakan dan saldo persediaan barang.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 self-start sm:self-auto shrink-0">
            @if ($productId)
                <x-ui.button
                    variant="outline"
                    size="md"
                    :href="route('export.stock-card', ['productId' => $productId, 'start_date' => $startDate, 'end_date' => $endDate])"
                >
                    <x-slot:icon>
                        <x-heroicon-o-arrow-down-tray class="w-4 h-4 text-emerald-600" />
                    </x-slot:icon>
                    <span>Export Excel</span>
                </x-ui.button>
            @endif

            <x-ui.button
                variant="primary"
                size="md"
                type="button"
                onclick="window.print()"
            >
                <x-slot:icon>
                    <x-heroicon-o-printer class="w-4 h-4" />
                </x-slot:icon>
                <span>Cetak Kartu Stok</span>
            </x-ui.button>
        </div>
    </div>

    {{-- Filter Toolbar (Screen Only) --}}
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-2xs print:hidden">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 items-end">
            {{-- Pilih Produk Barang (Livewire Server-Side Search Dropdown) --}}
            <div
                x-data="{ open: false }"
                @click.outside="open = false"
                @keydown.escape.window="open = false"
                class="relative"
                :class="open ? 'z-30' : 'z-10'"
            >
                <x-ui.label for="stock-card-product-trigger" :required="true">
                    Pilih Produk Barang
                </x-ui.label>

                <button
                    id="stock-card-product-trigger"
                    type="button"
                    aria-haspopup="listbox"
                    :aria-expanded="open ? 'true' : 'false'"
                    @click="open = !open; if(open) { $nextTick(() => document.getElementById('stock-card-product-search')?.focus()); }"
                    class="w-full h-[42px] px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:border-slate-300 text-slate-900 text-xs font-medium flex items-center justify-between gap-2 shadow-2xs cursor-pointer focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 transition"
                >
                    <span class="truncate {{ $selectedProduct ? 'text-slate-800 font-medium' : 'text-slate-400 font-normal' }}">
                        {{ $selectedProduct ? $selectedProduct->sku . ' - ' . $selectedProduct->name : 'Pilih Produk Barang...' }}
                    </span>
                    <x-heroicon-o-chevron-down
                        class="w-4 h-4 text-slate-400 shrink-0 transition-transform duration-150"
                        ::class="open ? 'rotate-180' : ''"
                    />
                </button>

                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-98"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-98"
                    class="absolute z-40 left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden"
                    style="display: none;"
                >
                    <div class="px-3 py-2 border-b border-slate-200 bg-slate-50/70 flex items-center gap-2">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4 text-slate-400 shrink-0" />
                        <input
                            id="stock-card-product-search"
                            wire:key="stock-card-product-search"
                            type="text"
                            wire:model.live.debounce.250ms="productSearch"
                            placeholder="Cari SKU atau nama barang..."
                            class="w-full text-xs text-slate-800 placeholder:text-slate-400 focus:outline-none bg-transparent"
                            autocomplete="off"
                        >
                        @if ($productSearch)
                            <button
                                type="button"
                                wire:click="$set('productSearch', '')"
                                @click="$nextTick(() => document.getElementById('stock-card-product-search')?.focus())"
                                class="text-slate-400 hover:text-slate-600 p-0.5 shrink-0 transition cursor-pointer"
                                title="Hapus pencarian"
                            >
                                <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
                            </button>
                        @endif
                    </div>

                    <div class="max-h-56 overflow-y-auto" role="listbox">
                        @forelse ($products as $prod)
                            <button
                                type="button"
                                role="option"
                                aria-selected="{{ $productId === $prod->id ? 'true' : 'false' }}"
                                wire:key="stock-card-option-{{ $prod->id }}"
                                wire:click="selectProduct({{ $prod->id }})"
                                @click="open = false"
                                class="w-full px-3 py-2 text-left text-xs transition-colors flex items-center justify-between gap-2 cursor-pointer {{ $productId === $prod->id ? 'bg-blue-50 font-semibold text-blue-700' : 'text-slate-700 hover:bg-slate-50' }}"
                            >
                                <span class="truncate">{{ $prod->sku }} - {{ $prod->name }}</span>
                                @if ($productId === $prod->id)
                                    <span class="text-blue-600 text-xs font-bold shrink-0">✓</span>
                                @endif
                            </button>
                        @empty
                            <div class="py-6 px-4 text-center">
                                <x-heroicon-o-magnifying-glass class="w-6 h-6 mx-auto mb-1.5 text-slate-300" />
                                <div class="font-semibold text-slate-700 text-xs">Tidak ditemukan</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    Tidak ada barang yang cocok dengan kata kunci tersebut.
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Dari Tanggal --}}
            <div>
                <x-ui.input
                    id="stock-card-start-date"
                    label="Dari Tanggal"
                    wire:model.live="startDate"
                    type="date"
                    :showClear="$startDate !== ''"
                    clearAction="$set('startDate', '')"
                />
            </div>

            {{-- Sampai Tanggal --}}
            <div>
                <x-ui.input
                    id="stock-card-end-date"
                    label="Sampai Tanggal"
                    wire:model.live="endDate"
                    type="date"
                    :showClear="$endDate !== ''"
                    clearAction="$set('endDate', '')"
                />
            </div>
        </div>
    </div>

    @if ($stockCardData)
        @php
            $product = $stockCardData['product'];
            $unitSymbol = $product->unit->symbol ?? '';
        @endphp

        {{-- Screen Content: Product Information, KPI Summary & Movement Ledger --}}
        <div class="space-y-5 print:hidden">
            {{-- Product Information & Stock Summary Card --}}
            <div class="bg-white border border-slate-200 rounded-xl p-5 sm:p-6 shadow-2xs">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                    {{-- Left: Product Details --}}
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <x-ui.badge variant="mono" size="code">{{ $product->sku }}</x-ui.badge>
                            @if (! $product->is_active)
                                <x-ui.badge variant="neutral" size="xs">Non-aktif</x-ui.badge>
                            @endif
                        </div>

                        <h2 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight mt-2 break-words">
                            {{ $product->name }}
                        </h2>

                        <dl class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs">
                            <div class="flex items-center gap-1.5">
                                <dt class="text-slate-500">Kategori:</dt>
                                <dd class="font-semibold text-slate-800">{{ $product->category->name ?? '-' }}</dd>
                            </div>

                            <span class="hidden sm:inline text-slate-300 select-none" aria-hidden="true">&bull;</span>

                            <div class="flex items-center gap-1.5">
                                <dt class="text-slate-500">Satuan:</dt>
                                <dd class="font-semibold text-slate-800">{{ $product->unit->name ?? '-' }} ({{ $unitSymbol ?: '-' }})</dd>
                            </div>

                            <span class="hidden sm:inline text-slate-300 select-none" aria-hidden="true">&bull;</span>

                            <div class="flex items-center gap-1.5">
                                <dt class="text-slate-500">Batas Minimum:</dt>
                                <dd class="font-semibold text-slate-800 tabular-nums">{{ number_format($product->minimum_stock, 0, ',', '.') }} {{ $unitSymbol }}</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Right: 3 Compact KPI Summary Blocks --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 lg:shrink-0 lg:min-w-[430px]">
                        {{-- Total Masuk (Subtle Green) --}}
                        <div class="px-4 py-3 rounded-xl bg-emerald-50/60 border border-emerald-200/80 flex sm:flex-col items-center sm:items-start justify-between gap-2">
                            <span class="text-[11px] font-semibold text-emerald-700 uppercase tracking-wider">Total Masuk</span>
                            <div class="flex items-baseline gap-1.5 tabular-nums sm:mt-1">
                                <span class="text-lg sm:text-xl font-bold text-emerald-700">+{{ number_format($stockCardData['total_in'], 0, ',', '.') }}</span>
                                <span class="text-[11px] font-medium text-emerald-600">{{ $unitSymbol }}</span>
                            </div>
                        </div>

                        {{-- Total Keluar (Subtle Red) --}}
                        <div class="px-4 py-3 rounded-xl bg-rose-50/60 border border-rose-200/80 flex sm:flex-col items-center sm:items-start justify-between gap-2">
                            <span class="text-[11px] font-semibold text-rose-700 uppercase tracking-wider">Total Keluar</span>
                            <div class="flex items-baseline gap-1.5 tabular-nums sm:mt-1">
                                <span class="text-lg sm:text-xl font-bold text-rose-700">-{{ number_format($stockCardData['total_out'], 0, ',', '.') }}</span>
                                <span class="text-[11px] font-medium text-rose-600">{{ $unitSymbol }}</span>
                            </div>
                        </div>

                        {{-- Saldo Akhir (Primary Blue) --}}
                        <div class="px-4 py-3 rounded-xl bg-blue-50/70 border border-blue-200 flex sm:flex-col items-center sm:items-start justify-between gap-2">
                            <span class="text-[11px] font-semibold text-blue-700 uppercase tracking-wider">Saldo Akhir</span>
                            <div class="flex items-baseline gap-1.5 tabular-nums sm:mt-1">
                                <span class="text-lg sm:text-xl font-bold text-blue-700">{{ number_format($stockCardData['final_balance'], 0, ',', '.') }}</span>
                                <span class="text-[11px] font-medium text-blue-600">{{ $unitSymbol }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stock Movement Table --}}
            <x-ui.table loadingTarget="selectProduct,startDate,endDate">
                <x-slot:head>
                    <x-ui.th class="w-28">Tanggal</x-ui.th>
                    <x-ui.th class="w-40">No. Transaksi</x-ui.th>
                    <x-ui.th align="center" class="w-24">Tipe</x-ui.th>
                    <x-ui.th class="min-w-[180px]">Pihak (Supplier / Penerima)</x-ui.th>
                    <x-ui.th align="right" class="w-28 text-emerald-700">Masuk (+)</x-ui.th>
                    <x-ui.th align="right" class="w-28 text-rose-700">Keluar (-)</x-ui.th>
                    <x-ui.th align="right" class="w-32 text-blue-700">Saldo</x-ui.th>
                    <x-ui.th class="min-w-[220px]">Keterangan</x-ui.th>
                </x-slot:head>

                @if ($startDate)
                    <x-ui.tr class="bg-slate-50/70 hover:bg-slate-50/70">
                        <x-ui.td colspan="4" class="text-slate-600 font-medium italic">
                            Saldo Awal Sebelum {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }}
                        </x-ui.td>
                        <x-ui.td align="right" :numeric="true" class="text-slate-400">-</x-ui.td>
                        <x-ui.td align="right" :numeric="true" class="text-slate-400">-</x-ui.td>
                        <x-ui.td align="right" :numeric="true" class="font-bold text-slate-900 bg-slate-100/60">
                            {{ number_format($stockCardData['initial_balance'], 0, ',', '.') }}
                        </x-ui.td>
                        <x-ui.td class="text-slate-400 text-[11px]">Saldo bawaan periode sebelumnya</x-ui.td>
                    </x-ui.tr>
                @endif

                @forelse ($stockCardData['ledger'] as $row)
                    <x-ui.tr wire:key="ledger-{{ $loop->index }}-{{ $row['transaction_no'] }}">
                        <x-ui.td :numeric="true" class="text-slate-600 font-medium">
                            {{ $row['date'] }}
                        </x-ui.td>

                        <x-ui.td class="whitespace-nowrap">
                            <a
                                href="{{ $row['url'] ?? route('transactions.show', $row['transaction_id']) }}"
                                class="font-mono font-semibold text-blue-600 hover:text-blue-700 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 rounded"
                            >
                                {{ $row['transaction_no'] }}
                            </a>
                        </x-ui.td>

                        <x-ui.td align="center">
                            @if ($row['type'] === 'IN')
                                <x-ui.badge variant="success" size="xs">IN</x-ui.badge>
                            @elseif ($row['type'] === 'OUT')
                                <x-ui.badge variant="danger" size="xs">OUT</x-ui.badge>
                            @else
                                <x-ui.badge variant="info" size="xs">{{ $row['type'] }}</x-ui.badge>
                            @endif
                        </x-ui.td>

                        <x-ui.td class="text-slate-700 font-medium">
                            {{ $row['partner'] }}
                        </x-ui.td>

                        <x-ui.td align="right" :numeric="true" class="font-mono {{ $row['in_qty'] > 0 ? 'font-bold text-emerald-600' : 'text-slate-400' }}">
                            {{ $row['in_qty'] > 0 ? '+' . number_format($row['in_qty'], 0, ',', '.') : '-' }}
                        </x-ui.td>

                        <x-ui.td align="right" :numeric="true" class="font-mono {{ $row['out_qty'] > 0 ? 'font-bold text-rose-600' : 'text-slate-400' }}">
                            {{ $row['out_qty'] > 0 ? '-' . number_format($row['out_qty'], 0, ',', '.') : '-' }}
                        </x-ui.td>

                        <x-ui.td align="right" :numeric="true" class="font-mono text-sm font-bold text-blue-700 bg-blue-50/20">
                            {{ number_format($row['balance'], 0, ',', '.') }}
                        </x-ui.td>

                        <x-ui.td class="text-slate-600 text-[11px] leading-relaxed whitespace-normal break-words max-w-xs">
                            {{ $row['note'] ?: '-' }}
                        </x-ui.td>
                    </x-ui.tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <x-ui.empty-state
                                title="Belum Ada Riwayat Pergerakan Stok"
                                description="Belum ada catatan mutasi transaksi untuk barang ini pada rentang waktu yang dipilih."
                            >
                                <x-slot:icon>
                                    <x-heroicon-o-clipboard-document-list class="w-5 h-5" />
                                </x-slot:icon>
                            </x-ui.empty-state>
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>
        </div>

        {{-- Dedicated A4 Print Layout --}}
        <x-stock-card-print
            :product="$product"
            :stockCardData="$stockCardData"
            :startDate="$startDate"
            :endDate="$endDate"
        />
    @else
        <div class="bg-white border border-slate-200 rounded-xl shadow-2xs print:hidden">
            <x-ui.empty-state
                title="Pilih Produk Barang"
                description="Silakan pilih produk barang pada filter di atas untuk melihat rincian kartu stok."
            >
                <x-slot:icon>
                    <x-heroicon-o-archive-box class="w-5 h-5" />
                </x-slot:icon>
            </x-ui.empty-state>
        </div>
    @endif
</div>
