@props([
    'product',
    'stockCardData',
    'startDate' => null,
    'endDate' => null,
])

@php
    $periodLabel = match (true) {
        filled($startDate) && filled($endDate) => \Carbon\Carbon::parse($startDate)->format('d/m/Y') . ' s/d ' . \Carbon\Carbon::parse($endDate)->format('d/m/Y'),
        filled($startDate) => 'Mulai ' . \Carbon\Carbon::parse($startDate)->format('d/m/Y') . ' s/d Sekarang',
        filled($endDate) => 'Awal s/d ' . \Carbon\Carbon::parse($endDate)->format('d/m/Y'),
        default => 'Semua Riwayat Transaksi',
    };
    $unitSymbol = $product->unit->symbol ?? '';
@endphp

<div class="hidden print:block text-slate-900 bg-white w-full">
    {{-- Printed Document Header --}}
    <div class="border-b-2 border-slate-800 pb-3 mb-4 flex items-end justify-between gap-4">
        <div>
            <p class="text-[10pt] font-bold uppercase tracking-wider text-slate-700">Berkah Mandiri</p>
            <h1 class="text-[15pt] font-bold tracking-tight text-slate-950 leading-tight mt-0.5">KARTU STOK BARANG</h1>
        </div>
        <div class="text-right text-[8.5pt] text-slate-700 leading-snug">
            <div><span class="font-semibold text-slate-900">Periode Laporan:</span> {{ $periodLabel }}</div>
        </div>
    </div>

    {{-- Product Information & Stock Summary Grid --}}
    <div class="grid grid-cols-12 gap-3 mb-4 text-[8.5pt]">
        {{-- Product Metadata --}}
        <div class="col-span-7 border border-slate-400 rounded p-2.5">
            <table class="w-full text-[8.5pt] leading-snug">
                <tbody>
                    <tr>
                        <td class="py-0.5 pr-2 font-semibold text-slate-600 w-28">Kode SKU</td>
                        <td class="py-0.5 px-1 text-slate-500 w-2">:</td>
                        <td class="py-0.5 font-mono font-bold text-slate-950">{{ $product->sku }}</td>
                    </tr>
                    <tr>
                        <td class="py-0.5 pr-2 font-semibold text-slate-600">Nama Barang</td>
                        <td class="py-0.5 px-1 text-slate-500">:</td>
                        <td class="py-0.5 font-bold text-slate-950">{{ $product->name }}</td>
                    </tr>
                    <tr>
                        <td class="py-0.5 pr-2 font-semibold text-slate-600">Kategori</td>
                        <td class="py-0.5 px-1 text-slate-500">:</td>
                        <td class="py-0.5 text-slate-900">{{ $product->category->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="py-0.5 pr-2 font-semibold text-slate-600">Satuan</td>
                        <td class="py-0.5 px-1 text-slate-500">:</td>
                        <td class="py-0.5 text-slate-900">{{ $product->unit->name ?? '-' }} ({{ $unitSymbol ?: '-' }})</td>
                    </tr>
                    <tr>
                        <td class="py-0.5 pr-2 font-semibold text-slate-600">Batas Minimum</td>
                        <td class="py-0.5 px-1 text-slate-500">:</td>
                        <td class="py-0.5 text-slate-900 tabular-nums">{{ number_format($product->minimum_stock, 0, ',', '.') }} {{ $unitSymbol }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Stock Summary --}}
        <div class="col-span-5 border border-slate-400 rounded p-2.5 flex flex-col justify-between">
            <div class="text-[8pt] font-bold uppercase tracking-wider text-slate-700 border-b border-slate-300 pb-1 mb-1.5">
                Ringkasan Mutasi &amp; Saldo
            </div>
            <table class="w-full text-[8.5pt] leading-snug">
                <tbody>
                    @if ($startDate)
                        <tr>
                            <td class="py-0.5 pr-2 font-medium text-slate-600">Saldo Awal Periode</td>
                            <td class="py-0.5 text-right font-mono font-semibold text-slate-900 tabular-nums">
                                {{ number_format($stockCardData['initial_balance'], 0, ',', '.') }} {{ $unitSymbol }}
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td class="py-0.5 pr-2 font-medium text-slate-600">Total Masuk (+)</td>
                        <td class="py-0.5 text-right font-mono font-bold text-slate-900 tabular-nums">
                            +{{ number_format($stockCardData['total_in'], 0, ',', '.') }} {{ $unitSymbol }}
                        </td>
                    </tr>
                    <tr>
                        <td class="py-0.5 pr-2 font-medium text-slate-600">Total Keluar (-)</td>
                        <td class="py-0.5 text-right font-mono font-bold text-slate-900 tabular-nums">
                            -{{ number_format($stockCardData['total_out'], 0, ',', '.') }} {{ $unitSymbol }}
                        </td>
                    </tr>
                    <tr class="border-t border-slate-300">
                        <td class="pt-1 pr-2 font-bold text-slate-950">Saldo Akhir</td>
                        <td class="pt-1 text-right font-mono text-[10pt] font-extrabold text-slate-950 tabular-nums">
                            {{ number_format($stockCardData['final_balance'], 0, ',', '.') }} {{ $unitSymbol }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Stock Ledger Table --}}
    <table class="bmi-print-table w-full">
        <thead>
            <tr>
                <th style="width: 11%; text-align: left;">Tanggal</th>
                <th style="width: 16%; text-align: left;">No. Transaksi</th>
                <th style="width: 7%; text-align: center;">Tipe</th>
                <th style="width: 20%; text-align: left;">Pihak (Supplier / Penerima)</th>
                <th style="width: 9%; text-align: right;">Masuk (+)</th>
                <th style="width: 9%; text-align: right;">Keluar (-)</th>
                <th style="width: 9%; text-align: right;">Saldo</th>
                <th style="width: 19%; text-align: left;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @if ($startDate)
                <tr>
                    <td colspan="4" class="italic font-medium">
                        Saldo Awal Sebelum {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }}
                    </td>
                    <td style="text-align: right;">-</td>
                    <td style="text-align: right;">-</td>
                    <td style="text-align: right; font-weight: 700;" class="font-mono tabular-nums">
                        {{ number_format($stockCardData['initial_balance'], 0, ',', '.') }}
                    </td>
                    <td>Saldo bawaan periode sebelumnya</td>
                </tr>
            @endif

            @forelse ($stockCardData['ledger'] as $row)
                <tr>
                    <td class="tabular-nums whitespace-nowrap">{{ $row['date'] }}</td>
                    <td class="font-mono font-semibold">{{ $row['transaction_no'] }}</td>
                    <td style="text-align: center; font-weight: 700;">{{ $row['type'] }}</td>
                    <td>{{ $row['partner'] }}</td>
                    <td style="text-align: right;" class="font-mono tabular-nums {{ $row['in_qty'] > 0 ? 'font-bold' : '' }}">
                        {{ $row['in_qty'] > 0 ? '+' . number_format($row['in_qty'], 0, ',', '.') : '-' }}
                    </td>
                    <td style="text-align: right;" class="font-mono tabular-nums {{ $row['out_qty'] > 0 ? 'font-bold' : '' }}">
                        {{ $row['out_qty'] > 0 ? '-' . number_format($row['out_qty'], 0, ',', '.') : '-' }}
                    </td>
                    <td style="text-align: right; font-weight: 700;" class="font-mono tabular-nums">
                        {{ number_format($row['balance'], 0, ',', '.') }}
                    </td>
                    <td class="whitespace-normal break-words">{{ $row['note'] ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 14px;">
                        Belum ada catatan mutasi transaksi untuk barang ini pada rentang waktu yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Print Footer --}}
    <div class="mt-3 pt-2 border-t border-slate-300 flex items-center justify-between text-[8pt] text-slate-600">
        <span>Berkah Mandiri Inventory</span>
        <span>Dicetak pada: {{ now()->format('d/m/Y H:i') }} ({{ config('app.timezone') }})</span>
    </div>
</div>
