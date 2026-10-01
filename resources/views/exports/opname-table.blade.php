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
                    <x:Name>Opname ' . e($opname->opname_no) . '</x:Name>
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
        .text-doc { mso-number-format: '\@'; font-weight: bold; color: #0f172a; }
        .info-label { background-color: #f1f5f9; font-weight: bold; color: #334155; }
        .info-value { background-color: #ffffff; color: #0f172a; }
        .tfoot { font-weight: bold; background-color: #f1f5f9; border-top: 2px solid #334155; }
        .var-match { background-color: #f8fafc; color: #64748b; text-align: right; }
        .var-plus { background-color: #dcfce7; color: #166534; font-weight: bold; text-align: right; }
        .var-minus { background-color: #fee2e2; color: #991b1b; font-weight: bold; text-align: right; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="10" class="title">BERITA ACARA HASIL STOCK OPNAME GUDANG</td>
        </tr>
        <tr>
            <td colspan="10" class="subtitle">Dokumen Verifikasi Fisik & Rekonsiliasi Sistem &bull; Dicetak: {{ $generatedAt }} &bull; Oleh: {{ $user->name }} ({{ ucfirst($user->role) }})</td>
        </tr>
        <tr><td colspan="10" style="border: none;"></td></tr>

        <!-- Ringkasan Header Dokumen -->
        <tr>
            <td class="info-label" colspan="2">Nomor Berita Acara:</td>
            <td class="info-value text-doc" colspan="3">{{ $opname->opname_no }}</td>
            <td class="info-label" colspan="2">Tanggal Pelaksanaan:</td>
            <td class="info-value" colspan="3">{{ $opname->opname_date->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="info-label" colspan="2">Petugas Pelaksana:</td>
            <td class="info-value" colspan="3">{{ $opname->creator->name ?? '-' }}</td>
            <td class="info-label" colspan="2">Catatan Kegiatan:</td>
            <td class="info-value" colspan="3">{{ $opname->note ?? 'Pemeriksaan rutin berkala inventaris gudang' }}</td>
        </tr>

        <tr><td colspan="10" style="border: none;"></td></tr>

        <!-- Tabel Detail Barang -->
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th style="width: 110px;">Kode SKU</th>
                <th style="width: 220px;">Nama Barang</th>
                <th style="width: 140px;">Kategori</th>
                <th style="width: 70px;">Satuan</th>
                <th style="width: 110px;">Stok Sistem</th>
                <th style="width: 110px;">Stok Fisik</th>
                <th style="width: 110px;">Selisih (+/-)</th>
                <th style="width: 150px;">Alasan Selisih</th>
                <th style="width: 200px;">Catatan Investigasi</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalSistem = 0;
                $totalFisik = 0;
                $totalSelisih = 0;
                $itemsSelisihCount = 0;
            @endphp
            @forelse($opname->items as $idx => $item)
            @php
                $totalSistem += $item->system_stock;
                $totalFisik += $item->physical_stock;
                $totalSelisih += $item->difference;
                if ($item->difference != 0) {
                    $itemsSelisihCount++;
                }

                $varClass = 'var-match';
                if ($item->difference > 0) {
                    $varClass = 'var-plus';
                } elseif ($item->difference < 0) {
                    $varClass = 'var-minus';
                }

                $reasonText = match ($item->reason) {
                    'RUSAK' => 'Barang Rusak',
                    'HILANG' => 'Barang Hilang',
                    'KADALUWARSA' => 'Kadaluwarsa',
                    'SELISIH_HITUNG' => 'Koreksi Hitung',
                    'SESUAI' => 'Stok Sesuai',
                    default => 'Penyesuaian Fisik',
                };

                $rowClass = ($idx % 2 === 1) ? 'background-color: #f8fafc;' : '';
            @endphp
            <tr style="{{ $rowClass }}">
                <td class="text-center">{{ $idx + 1 }}</td>
                <td class="text-sku">{{ $item->product->sku ?? '-' }}</td>
                <td>{{ $item->product->name ?? '-' }}</td>
                <td>{{ $item->product->category->name ?? '-' }}</td>
                <td class="text-center">{{ $item->product->unit->symbol ?? '-' }}</td>
                <td class="text-right">{{ number_format($item->system_stock, 0, ',', '.') }}</td>
                <td class="text-right" style="font-weight: bold;">{{ number_format($item->physical_stock, 0, ',', '.') }}</td>
                <td class="{{ $varClass }}">
                    @if($item->difference > 0)
                        +{{ number_format($item->difference, 0, ',', '.') }}
                    @elseif($item->difference < 0)
                        {{ number_format($item->difference, 0, ',', '.') }}
                    @else
                        0
                    @endif
                </td>
                <td>{{ $reasonText }}</td>
                <td style="color: #64748b;">{{ $item->item_notes ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center" style="padding: 16px; color: #64748b;">Tidak ada rincian item dalam berita acara ini.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="tfoot">
                <td colspan="5" class="text-right" style="padding: 8px;">Total Akumulasi Fisik & Rekonsiliasi:</td>
                <td class="text-right">{{ number_format($totalSistem, 0, ',', '.') }}</td>
                <td class="text-right" style="color: #1e1b4b; font-size: 11pt;">{{ number_format($totalFisik, 0, ',', '.') }}</td>
                <td class="text-right" style="color: {{ $totalSelisih < 0 ? '#dc2626' : ($totalSelisih > 0 ? '#16a34a' : '#0f172a') }}; font-size: 11pt;">
                    {{ ($totalSelisih > 0 ? '+' : '') . number_format($totalSelisih, 0, ',', '.') }}
                </td>
                <td colspan="2" class="text-center" style="font-weight: normal; font-size: 9pt; color: #475569;">
                    {{ $itemsSelisihCount }} dari {{ count($opname->items) }} item mengalami selisih
                </td>
            </tr>
        </tfoot>
    </table>

    <br><br>
    <table style="border: none;">
        <tr style="border: none;">
            <td colspan="5" style="border: none; text-align: center;">
                <p>Petugas Pelaksana Stock Opname,</p>
                <br><br><br>
                <p><strong>({{ $opname->creator->name ?? $user->name }})</strong></p>
                <p style="font-size: 9pt; color: #64748b;">Staff Auditor Gudang</p>
            </td>
            <td colspan="5" style="border: none; text-align: center;">
                <p>Mengetahui / Mengesahkan (Kepala Gudang),</p>
                <br><br><br>
                <p><strong>( ............................................ )</strong></p>
                <p style="font-size: 9pt; color: #64748b;">Tanda Tangan & Nama Terang</p>
            </td>
        </tr>
    </table>
</body>
</html>
