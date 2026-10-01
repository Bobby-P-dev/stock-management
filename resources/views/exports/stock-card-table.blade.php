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
                    <x:Name>Kartu Stok - ' . e($product->sku) . '</x:Name>
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
        .info-label { background-color: #f1f5f9; font-weight: bold; color: #334155; width: 150px; }
        .info-value { background-color: #ffffff; color: #0f172a; }
        .tfoot { font-weight: bold; background-color: #f1f5f9; border-top: 2px solid #334155; }
        .type-in { background-color: #dcfce7; color: #166534; font-weight: bold; text-align: center; }
        .type-out { background-color: #fee2e2; color: #991b1b; font-weight: bold; text-align: center; }
        .type-opname { background-color: #e0e7ff; color: #3730a3; font-weight: bold; text-align: center; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="9" class="title">BUKU KARTU STOK INVENTARIS GUDANG</td>
        </tr>
        <tr>
            <td colspan="9" class="subtitle">Periode: {{ $startDate ?? 'Semua Riwayat' }} s/d {{ $endDate ?? 'Semua Riwayat' }} &bull; Dicetak: {{ $generatedAt }} &bull; Oleh: {{ $user->name }} ({{ ucfirst($user->role) }})</td>
        </tr>
        <tr><td colspan="9" style="border: none;"></td></tr>

        <tr>
            <td class="info-label" colspan="2">Kode SKU:</td>
            <td class="info-value text-sku" colspan="2">{{ $product->sku }}</td>
            <td class="info-label" colspan="2">Kategori:</td>
            <td class="info-value" colspan="3">{{ $product->category->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label" colspan="2">Nama Barang:</td>
            <td class="info-value" colspan="2" style="font-weight: bold;">{{ $product->name }}</td>
            <td class="info-label" colspan="2">Satuan:</td>
            <td class="info-value" colspan="3">{{ $product->unit->name ?? '-' }} ({{ $product->unit->symbol ?? '-' }})</td>
        </tr>
        <tr>
            <td class="info-label" colspan="2">Batas Minimum Stok:</td>
            <td class="info-value text-center" colspan="2">{{ $product->minimum_stock }}</td>
            <td class="info-label" colspan="2">Stok Gudang Terkini:</td>
            <td class="info-value text-right" colspan="3" style="font-weight: bold; color: #4338ca;">{{ $product->current_stock }} {{ $product->unit->symbol ?? '' }}</td>
        </tr>
        <tr>
            <td class="info-label" colspan="2">Saldo Awal Periode:</td>
            <td class="info-value text-right" colspan="2" style="font-weight: bold;">{{ number_format($initialBalance, 0, ',', '.') }}</td>
            <td class="info-label" colspan="2">Akumulasi Mutasi:</td>
            <td class="info-value" colspan="3">
                Masuk: <strong style="color: #047857;">+{{ number_format($totalIn, 0, ',', '.') }}</strong> &bull;
                Keluar: <strong style="color: #dc2626;">-{{ number_format($totalOut, 0, ',', '.') }}</strong> &bull;
                Saldo Akhir: <strong style="color: #1e1b4b;">{{ number_format($finalBalance, 0, ',', '.') }}</strong>
            </td>
        </tr>

        <tr><td colspan="9" style="border: none;"></td></tr>

        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th style="width: 100px;">Tanggal</th>
                <th style="width: 140px;">No. Bukti Transaksi</th>
                <th style="width: 100px;">Tipe Mutasi</th>
                <th style="width: 200px;">Pihak Terkait / Divisi</th>
                <th style="width: 100px;">Masuk (+)</th>
                <th style="width: 100px;">Keluar (-)</th>
                <th style="width: 110px;">Saldo Berjalan</th>
                <th style="width: 220px;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @if($startDate)
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td class="text-center">-</td>
                <td class="text-center">{{ date('d/m/Y', strtotime($startDate)) }}</td>
                <td class="text-doc">SALDO-AWAL</td>
                <td class="text-center">AWAL</td>
                <td>Saldo awal tercatat sebelum tanggal {{ date('d/m/Y', strtotime($startDate)) }}</td>
                <td class="text-right">-</td>
                <td class="text-right">-</td>
                <td class="text-right">{{ number_format($initialBalance, 0, ',', '.') }}</td>
                <td style="color: #64748b;">Saldo bawaan periode sebelumnya</td>
            </tr>
            @endif

            @forelse($ledger as $idx => $row)
            @php
                $rowClass = ($idx % 2 === 1) ? 'background-color: #f8fafc;' : '';
                $typeClass = match($row['type']) {
                    'IN' => 'type-in',
                    'OUT' => 'type-out',
                    default => 'type-opname',
                };
            @endphp
            <tr style="{{ $rowClass }}">
                <td class="text-center">{{ $idx + 1 }}</td>
                <td class="text-center">{{ $row['date'] }}</td>
                <td class="text-doc">{{ $row['transaction_no'] }}</td>
                <td class="{{ $typeClass }}">{{ $row['type'] }}</td>
                <td>{{ $row['partner'] }}</td>
                <td class="text-right" style="font-weight: {{ $row['in_qty'] > 0 ? 'bold' : 'normal' }}; color: {{ $row['in_qty'] > 0 ? '#047857' : '#000000' }};">
                    {{ $row['in_qty'] > 0 ? '+'.number_format($row['in_qty'], 0, ',', '.') : '-' }}
                </td>
                <td class="text-right" style="font-weight: {{ $row['out_qty'] > 0 ? 'bold' : 'normal' }}; color: {{ $row['out_qty'] > 0 ? '#dc2626' : '#000000' }};">
                    {{ $row['out_qty'] > 0 ? '-'.number_format($row['out_qty'], 0, ',', '.') : '-' }}
                </td>
                <td class="text-right" style="font-weight: bold; background-color: #f8fafc;">
                    {{ number_format($row['balance'], 0, ',', '.') }}
                </td>
                <td style="color: #64748b;">{{ $row['note'] ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center" style="padding: 16px; color: #64748b;">Tidak ada catatan mutasi transaksi pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="tfoot">
                <td colspan="5" class="text-right" style="padding: 8px;">Ringkasan Akhir Periode:</td>
                <td class="text-right" style="font-size: 11pt; color: #047857;">+{{ number_format($totalIn, 0, ',', '.') }}</td>
                <td class="text-right" style="font-size: 11pt; color: #dc2626;">-{{ number_format($totalOut, 0, ',', '.') }}</td>
                <td class="text-right" style="font-size: 11pt; color: #1e1b4b; background-color: #e2e8f0;">{{ number_format($finalBalance, 0, ',', '.') }}</td>
                <td>{{ $product->unit->symbol ?? 'Unit' }}</td>
            </tr>
        </tfoot>
    </table>

    <br><br>
    <table style="border: none;">
        <tr style="border: none;">
            <td colspan="4" style="border: none; text-align: center;">
                <p>Petugas Kartu Stok,</p>
                <br><br><br>
                <p><strong>({{ $user->name }})</strong></p>
                <p style="font-size: 9pt; color: #64748b;">Operator Sistem Stok</p>
            </td>
            <td colspan="5" style="border: none; text-align: center;">
                <p>Kepala Gudang / Supervisor Logistik,</p>
                <br><br><br>
                <p><strong>( ............................................ )</strong></p>
                <p style="font-size: 9pt; color: #64748b;">Tanda Tangan & Nama Terang</p>
            </td>
        </tr>
    </table>
</body>
</html>
