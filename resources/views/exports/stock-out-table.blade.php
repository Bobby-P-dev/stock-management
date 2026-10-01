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
                    <x:Name>Barang Keluar</x:Name>
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
            <td colspan="10" class="title">LAPORAN REKAPITULASI BARANG KELUAR (STOCK OUT)</td>
        </tr>
        <tr>
            <td colspan="10" class="subtitle">Periode: {{ $startDate ?? 'Semua' }} s/d {{ $endDate ?? 'Semua' }} &bull; Dicetak: {{ $generatedAt }} &bull; Oleh: {{ $user->name }} ({{ ucfirst($user->role) }})</td>
        </tr>
        <tr><td colspan="10" style="border: none;"></td></tr>
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th style="width: 140px;">No. Transaksi</th>
                <th style="width: 100px;">Tanggal Keluar</th>
                <th style="width: 180px;">Penerima / Divisi</th>
                <th style="width: 110px;">Kode SKU</th>
                <th style="width: 240px;">Nama Barang</th>
                <th style="width: 90px;">Qty Keluar</th>
                <th style="width: 80px;">Satuan</th>
                <th style="width: 140px;">Petugas Pengeluar</th>
                <th style="width: 200px;">Catatan / Keperluan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $rowNo = 1;
                $totalQty = 0;
            @endphp
            @forelse($transactions as $tx)
                @foreach($tx->items as $item)
                    @php
                        $totalQty += $item->quantity;
                        $rowClass = ($rowNo % 2 === 0) ? 'background-color: #f8fafc;' : '';
                    @endphp
                    <tr style="{{ $rowClass }}">
                        <td class="text-center">{{ $rowNo++ }}</td>
                        <td class="text-doc">{{ $tx->transaction_no }}</td>
                        <td class="text-center">{{ $tx->transaction_date->format('d/m/Y') }}</td>
                        <td style="font-weight: 500;">{{ $tx->recipient }}</td>
                        <td class="text-sku">{{ $item->product->sku ?? '-' }}</td>
                        <td>{{ $item->product->name ?? '-' }}</td>
                        <td class="text-right" style="font-weight: bold; color: #dc2626;">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $item->product->unit->symbol ?? '-' }}</td>
                        <td>{{ $tx->creator->name ?? '-' }}</td>
                        <td style="color: #64748b;">{{ $tx->note ?? '-' }}</td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 16px; color: #64748b;">Tidak ada data transaksi pengeluaran barang dalam periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        @if($totalQty > 0)
        <tfoot>
            <tr class="tfoot">
                <td colspan="6" class="text-right" style="padding: 8px;">Total Kuantitas Barang Keluar:</td>
                <td class="text-right" style="font-size: 11pt; color: #dc2626;">{{ number_format($totalQty, 0, ',', '.') }}</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
        @endif
    </table>

    <br><br>
    <table style="border: none;">
        <tr style="border: none;">
            <td colspan="5" style="border: none; text-align: center;">
                <p>Petugas Pengeluar Barang,</p>
                <br><br><br>
                <p><strong>({{ $user->name }})</strong></p>
                <p style="font-size: 9pt; color: #64748b;">Staff Bagian Gudang</p>
            </td>
            <td colspan="5" style="border: none; text-align: center;">
                <p>Kepala Bagian Operasional Gudang,</p>
                <br><br><br>
                <p><strong>( ............................................ )</strong></p>
                <p style="font-size: 9pt; color: #64748b;">Tanda Tangan & Nama Terang</p>
            </td>
        </tr>
    </table>
</body>
</html>
