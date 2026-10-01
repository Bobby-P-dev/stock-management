<?php

namespace App\Http\Controllers;

use App\Models\StockOpname;
use App\Models\StockTransaction;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ExportController extends Controller
{
    /**
     * Export Laporan Posisi Stok Barang Gudang dalam format Tabel Excel (.xls) lengkap dengan Ranking Perputaran Mutasi.
     */
    public function exportStock(Request $request, StockService $stockService): Response
    {
        $categoryId = $request->query('category') ? (int) $request->query('category') : null;
        $status = $request->query('status');
        $sortBy = $request->query('sort_by') ?? 'turnover';
        $velocity = $request->query('velocity');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $analysis = $stockService->getProductsWithMovementAnalysis(
            startDate: $startDate ?: null,
            endDate: $endDate ?: null,
            categoryId: $categoryId,
            stockStatus: $status ?: null,
            velocityStatus: $velocity ?: null,
            sortBy: $sortBy
        );

        $products = $analysis['products'];
        $summary = $analysis['summary'];

        $filename = 'laporan-posisi-stok-ranking-'.date('Ymd_His').'.xls';

        $user = $request->user() ?? (object) ['name' => 'Petugas Sistem', 'role' => 'admin'];

        $html = view('exports.stock-table', [
            'products' => $products,
            'summary' => $summary,
            'user' => $user,
            'generatedAt' => now()->format('d/m/Y H:i:s'),
            'sortBy' => $sortBy,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ])->render();

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Export Laporan Rekapitulasi Barang Masuk (Stock In) dalam format Tabel Excel (.xls).
     */
    public function exportStockIn(Request $request): Response
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $supplierId = $request->query('supplier_id');

        $transactions = StockTransaction::in()
            ->with(['supplier', 'creator', 'items.product.unit'])
            ->when($startDate, fn ($q) => $q->where('transaction_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('transaction_date', '<=', $endDate))
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->orderBy('transaction_date', 'asc')
            ->get();

        $filename = 'laporan-barang-masuk-'.($startDate ?? 'all').'_sd_'.($endDate ?? 'all').'.xls';

        $user = $request->user() ?? (object) ['name' => 'Petugas Sistem', 'role' => 'admin'];

        $html = view('exports.stock-in-table', [
            'transactions' => $transactions,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'user' => $user,
            'generatedAt' => now()->format('d/m/Y H:i:s'),
        ])->render();

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Export Laporan Rekapitulasi Barang Keluar (Stock Out) dalam format Tabel Excel (.xls).
     */
    public function exportStockOut(Request $request): Response
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $recipient = $request->query('recipient');

        $transactions = StockTransaction::out()
            ->with(['creator', 'items.product.unit'])
            ->when($startDate, fn ($q) => $q->where('transaction_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('transaction_date', '<=', $endDate))
            ->when($recipient, fn ($q) => $q->where('recipient', 'like', '%'.$recipient.'%'))
            ->orderBy('transaction_date', 'asc')
            ->get();

        $filename = 'laporan-barang-keluar-'.($startDate ?? 'all').'_sd_'.($endDate ?? 'all').'.xls';

        $user = $request->user() ?? (object) ['name' => 'Petugas Sistem', 'role' => 'admin'];

        $html = view('exports.stock-out-table', [
            'transactions' => $transactions,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'user' => $user,
            'generatedAt' => now()->format('d/m/Y H:i:s'),
        ])->render();

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Export Kartu Stok (Stock Card) Produk Spesifik dalam format Tabel Excel (.xls).
     */
    public function exportStockCard(int $productId, Request $request, StockService $stockService): Response
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $data = $stockService->getStockCard($productId, $startDate, $endDate);
        $product = $data['product'];

        $filename = 'kartu-stok-'.$product->sku.'-'.date('Ymd_His').'.xls';

        $user = $request->user() ?? (object) ['name' => 'Petugas Sistem', 'role' => 'admin'];

        $html = view('exports.stock-card-table', [
            'product' => $product,
            'ledger' => $data['ledger'],
            'initialBalance' => $data['initial_balance'],
            'totalIn' => $data['total_in'],
            'totalOut' => $data['total_out'],
            'finalBalance' => $data['final_balance'],
            'startDate' => $startDate,
            'endDate' => $endDate,
            'user' => $user,
            'generatedAt' => now()->format('d/m/Y H:i:s'),
        ])->render();

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Export Berita Acara Stock Opname dalam format Tabel Excel (.xls).
     */
    public function exportOpname(int $opnameId, Request $request): Response
    {
        $opname = StockOpname::with(['items.product.unit', 'items.product.category', 'creator'])
            ->findOrFail($opnameId);

        $filename = 'berita-acara-opname-'.$opname->opname_no.'.xls';

        $user = $request->user() ?? (object) ['name' => 'Petugas Sistem', 'role' => 'admin'];

        $html = view('exports.opname-table', [
            'opname' => $opname,
            'user' => $user,
            'generatedAt' => now()->format('d/m/Y H:i:s'),
        ])->render();

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
