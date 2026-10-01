<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <!--[if gte mso 9]>
    @php
    echo '<xml>
        <x:ExcelWorkbook>
            <x:ExcelWorksheets>
                <x:ExcelWorksheet>
                    <x:Name>Posisi & Ranking Stok</x:Name>
                    <x:WorksheetOptions>
                        <x:DisplayGridlines/>
                    </x:WorksheetOptions>
                </x:ExcelWorksheet>
            </x:ExcelWorksheets>
        </x:ExcelWorkbook>
    </xml>';
    @endphp
    <![endif]-->
    <style>
        body { font-family: 'Calibri', 'Arial', sans-serif; font-size: 11pt; }
        .title { font-size: 15pt; font-weight: bold; text-align: center; }
        .subtitle { font-size: 10pt; color: #475569; text-align: center; margin-bottom: 12px; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        th { background-color: #4338ca; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #312e81; padding: 7px; font-size: 10pt; }
        td { border: 1px solid #94a3b8; padding: 6px; font-size: 10pt; vertical-align: middle; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-sku { mso-number-format: '\@'; text-align: center; font-weight: bold; color: #3730a3; }
        .badge-safe { background-color: #dcfce7; color: #166534; font-weight: bold; text-align: center; }
        .badge-low { background-color: #fef3c7; color: #92400e; font-weight: bold; text-align: center; }
        .badge-out { background-color: #fee2e2; color: #991b1b; font-weight: bold; text-align: center; }
        .badge-fast { background-color: #dcfce7; color: #166534; font-weight: bold; text-align: center; }
        .badge-medium { background-color: #e0e7ff; color: #3730a3; font-weight: bold; text-align: center; }
        .badge-slow { background-color: #fef3c7; color: #92400e; font-weight: bold; text-align: center; }
        .badge-non { background-color: #f1f5f9; color: #64748b; font-weight: normal; text-align: center; }
        .tfoot { font-weight: bold; background-color: #f1f5f9; border-top: 2px solid #334155; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="12" class="title">LAPORAN POSISI STOK & RANKING PERPUTARAN BARANG</td>
        </tr>
        <tr>
            <td colspan="12" class="subtitle">Berkah Mandiri Inventory &bull; Periode Mutasi: {{ $startDate ?? 'Seluruh Waktu' }} s/d {{ $endDate ?? 'Seluruh Waktu' }} &bull; Dicetak: {{ $generatedAt }} &bull; Oleh: {{ $user->name }} ({{ ucfirst($user->role) }})</td>
        </tr>
        <tr><td colspan="12" style="border: none;"></td></tr>
        <thead>
            <tr>
                <th style="width: 50px;">Rank</th>
                <th style="width: 120px;">Kode SKU</th>
                <th style="width: 240px;">Nama Barang</th>
                <th style="width: 130px;">Kategori</th>
                <th style="width: 70px;">Satuan</th>
                <th style="width: 90px;">Masuk (+)</th>
                <th style="width: 90px;">Keluar (-)</th>
                <th style="width: 100px;">Total Mutasi</th>
                <th style="width: 80px;">Batas Min</th>
                <th style="width: 100px;">Stok Terkini</th>
                <th style="width: 90px;">Status Stok</th>
                <th style="width: 120px;">Perputaran</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalUnit = 0;
                $sumIn = 0;
                $sumOut = 0;
                $sumMovement = 0;
            @endphp
            @forelse($products as $idx => $p)
            @php
                $totalUnit += $p->current_stock;
                $sumIn += ($p->total_in ?? 0);
                $sumOut += ($p->total_out ?? 0);
                $sumMovement += ($p->total_movement ?? 0);
                $rowClass = ($idx % 2 === 1) ? 'background-color: #f8fafc;' : '';

                $rankClass = 'text-center';

                $velClass = match($p->velocity_status ?? 'NON_MOVING') {
                    'FAST_MOVING' => 'badge-fast',
                    'MEDIUM_MOVING' => 'badge-medium',
                    'SLOW_MOVING' => 'badge-slow',
                    default => 'badge-non',
                };

                $velText = match($p->velocity_status ?? 'NON_MOVING') {
                    'FAST_MOVING' => 'Fast Moving',
                    'MEDIUM_MOVING' => 'Medium Moving',
                    'SLOW_MOVING' => 'Slow Moving',
                    default => 'Non-Moving',
                };
            @endphp
            <tr style="{{ $rowClass }}">
                <td class="text-center">{{ $p->rank ?? ($idx + 1) }}</td>
                <td class="text-sku">{{ $p->sku }}</td>
                <td>{{ $p->name }}</td>
                <td>{{ $p->category->name ?? '-' }}</td>
                <td class="text-center">{{ $p->unit->symbol ?? ($p->unit->name ?? '-') }}</td>
                <td class="text-right" style="color: #047857; font-weight: bold;">{{ number_format($p->total_in ?? 0, 0, ',', '.') }}</td>
                <td class="text-right" style="color: #dc2626; font-weight: bold;">{{ number_format($p->total_out ?? 0, 0, ',', '.') }}</td>
                <td class="text-right" style="font-weight: bold;">{{ number_format($p->total_movement ?? 0, 0, ',', '.') }}</td>
                <td class="text-center">{{ $p->minimum_stock }}</td>
                <td class="text-right" style="font-weight: bold;">{{ $p->current_stock }}</td>
                @if($p->isOutOfStock())
                <td class="badge-out">Habis</td>
                @elseif($p->isLowStock())
                <td class="badge-low">Menipis</td>
                @else
                <td class="badge-safe">Aman</td>
                @endif
                <td class="{{ $velClass }}">{{ $velText }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="12" class="text-center" style="padding: 16px; color: #64748b;">Tidak ada data barang yang tersedia.</td>
            </tr>
            @endforelse
        </tbody>
        @if(count($products) > 0)
        <tfoot>
            <tr class="tfoot">
                <td colspan="5" class="text-right" style="padding: 8px;">Total Akumulasi:</td>
                <td class="text-right" style="color: #047857;">+{{ number_format($sumIn, 0, ',', '.') }}</td>
                <td class="text-right" style="color: #dc2626;">-{{ number_format($sumOut, 0, ',', '.') }}</td>
                <td class="text-right" style="font-size: 11pt; color: #1e1b4b;">{{ number_format($sumMovement, 0, ',', '.') }}</td>
                <td></td>
                <td class="text-right" style="font-size: 11pt; color: #1e1b4b;">{{ number_format($totalUnit, 0, ',', '.') }}</td>
                <td colspan="2" class="text-center">{{ count($products) }} SKU Terdata</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <br><br>
    <table style="border: none;">
        <tr style="border: none;">
            <td colspan="6" style="border: none; text-align: center;">
                <p>Petugas Penanggung Jawab,</p>
                <br><br><br>
                <p><strong>({{ $user->name }})</strong></p>
                <p style="font-size: 9pt; color: #64748b;">Berkah Mandiri Inventory</p>
            </td>
            <td colspan="6" style="border: none; text-align: center;">
                <p>Kepala Gudang / Manajer Logistik,</p>
                <br><br><br>
                <p><strong>( ............................................ )</strong></p>
                <p style="font-size: 9pt; color: #64748b;">Tanda Tangan & Nama Terang</p>
            </td>
        </tr>
    </table>
</body>
</html>
