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
                    <x:Name>Barang Masuk</x:Name>
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
        .tfoot { font-weight: bold; background-color: #f1f5f9; border-top: 2px solid #334155; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="12" class="title">LAPORAN REKAPITULASI BARANG MASUK (STOCK IN)</td>
        </tr>
        <tr>
            <td colspan="12" class="subtitle">Periode: {{ $startDate ?? 'Semua' }} s/d {{ $endDate ?? 'Semua' }} &bull; Dicetak: {{ $generatedAt }} &bull; Oleh: {{ $user->name }} ({{ ucfirst($user->role) }})</td>
        </tr>
        <tr><td colspan="12" style="border: none;"></td></tr>
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th style="width: 140px;">No. Faktur</th>
                <th style="width: 100px;">Tanggal Masuk</th>
                <th style="width: 180px;">Supplier</th>
                <th style="width: 110px;">Kode SKU</th>
                <th style="width: 220px;">Nama Barang</th>
                <th style="width: 80px;">Qty</th>
                <th style="width: 70px;">Satuan</th>
                <th style="width: 110px;">Harga Satuan (Rp)</th>
                <th style="width: 120px;">Subtotal (Rp)</th>
                <th style="width: 130px;">Petugas Penerima</th>
                <th style="width: 160px;">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $rowNo = 1;
                $totalQty = 0;
                $totalNilai = 0;
            @endphp
            @forelse($transactions as $tx)
                @foreach($tx->items as $item)
                    @php
                        $totalQty += $item->quantity;
                        $totalNilai += ($item->subtotal ?? ($item->quantity * ($item->unit_price ?? 0)));
                        $rowClass = ($rowNo % 2 === 0) ? 'background-color: #f8fafc;' : '';
                    @endphp
                    <tr style="{{ $rowClass }}">
                        <td class="text-center">{{ $rowNo++ }}</td>
                        <td class="text-doc">{{ $tx->transaction_no }}</td>
                        <td class="text-center">{{ $tx->transaction_date->format('d/m/Y') }}</td>
                        <td>{{ $tx->supplier->name ?? '-' }}</td>
                        <td class="text-sku">{{ $item->product->sku ?? '-' }}</td>
                        <td>{{ $item->product->name ?? '-' }}</td>
                        <td class="text-right" style="font-weight: bold;">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $item->product->unit->symbol ?? '-' }}</td>
                        <td class="text-right">{{ number_format($item->unit_price ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right" style="font-weight: bold;">{{ number_format($item->subtotal ?? 0, 0, ',', '.') }}</td>
                        <td>{{ $tx->creator->name ?? '-' }}</td>
                        <td style="color: #64748b;">{{ $tx->note ?? '-' }}</td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="12" class="text-center" style="padding: 16px; color: #64748b;">Tidak ada data transaksi barang masuk dalam periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        @if($totalQty > 0)
        <tfoot>
            <tr class="tfoot">
                <td colspan="6" class="text-right" style="padding: 8px;">Total Akumulasi Pembelian & Pasokan Masuk:</td>
                <td class="text-right" style="font-size: 11pt; color: #1e1b4b;">{{ number_format($totalQty, 0, ',', '.') }}</td>
                <td></td>
                <td></td>
                <td class="text-right" style="font-size: 11pt; color: #047857;">Rp {{ number_format($totalNilai, 0, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
        @endif
    </table>

    <br><br>
    <table style="border: none;">
        <tr style="border: none;">
            <td colspan="6" style="border: none; text-align: center;">
                <p>Petugas Logistik / Penerima,</p>
                <br><br><br>
                <p><strong>({{ $user->name }})</strong></p>
                <p style="font-size: 9pt; color: #64748b;">Staff Bagian Gudang</p>
            </td>
            <td colspan="6" style="border: none; text-align: center;">
                <p>Kepala Bagian Pengadaan & Gudang,</p>
                <br><br><br>
                <p><strong>( ............................................ )</strong></p>
                <p style="font-size: 9pt; color: #64748b;">Tanda Tangan & Nama Terang</p>
            </td>
        </tr>
    </table>
</body>
</html>
