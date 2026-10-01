<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\StockTransaction;
use App\Models\StockTransactionItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    /**
     * Catat transaksi Barang Masuk (Stock In).
     *
     * @param array{
     *     transaction_date: string,
     *     supplier_id: int,
     *     note?: string|null
     * } $headerData
     * @param list<array{
     *     product_id: int,
     *     quantity: int,
     *     unit_price?: float|null
     * }> $itemsData
     */
    public function recordStockIn(array $headerData, array $itemsData, int $userId): StockTransaction
    {
        if (empty($itemsData)) {
            throw ValidationException::withMessages([
                'items' => 'Minimal harus ada 1 barang dalam transaksi masuk.',
            ]);
        }

        return DB::transaction(function () use ($headerData, $itemsData, $userId) {
            $transactionNo = $this->generateTransactionNumber('IN', $headerData['transaction_date']);

            $transaction = StockTransaction::create([
                'transaction_no' => $transactionNo,
                'type' => 'IN',
                'transaction_date' => $headerData['transaction_date'],
                'supplier_id' => $headerData['supplier_id'],
                'recipient' => null,
                'note' => $headerData['note'] ?? null,
                'created_by' => $userId,
            ]);

            foreach ($itemsData as $item) {
                $product = Product::where('id', $item['product_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $product->is_active) {
                    throw ValidationException::withMessages([
                        'items' => "Barang '{$product->name}' berstatus non-aktif dan tidak dapat digunakan.",
                    ]);
                }

                if ($item['quantity'] <= 0) {
                    throw ValidationException::withMessages([
                        'items' => "Kuantitas untuk '{$product->name}' harus lebih besar dari 0.",
                    ]);
                }

                $transaction->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'] ?? null,
                ]);

                $product->increment('current_stock', $item['quantity']);
            }

            return $transaction->load(['items.product', 'supplier', 'creator']);
        });
    }

    /**
     * Catat transaksi Barang Keluar (Stock Out).
     *
     * @param array{
     *     transaction_date: string,
     *     recipient: string,
     *     note?: string|null
     * } $headerData
     * @param list<array{
     *     product_id: int,
     *     quantity: int
     * }> $itemsData
     */
    public function recordStockOut(array $headerData, array $itemsData, int $userId): StockTransaction
    {
        if (empty($itemsData)) {
            throw ValidationException::withMessages([
                'items' => 'Minimal harus ada 1 barang dalam transaksi keluar.',
            ]);
        }

        return DB::transaction(function () use ($headerData, $itemsData, $userId) {
            $transactionNo = $this->generateTransactionNumber('OUT', $headerData['transaction_date']);

            $transaction = StockTransaction::create([
                'transaction_no' => $transactionNo,
                'type' => 'OUT',
                'transaction_date' => $headerData['transaction_date'],
                'supplier_id' => null,
                'recipient' => $headerData['recipient'],
                'note' => $headerData['note'] ?? null,
                'created_by' => $userId,
            ]);

            foreach ($itemsData as $item) {
                $product = Product::where('id', $item['product_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $product->is_active) {
                    throw ValidationException::withMessages([
                        'items' => "Barang '{$product->name}' berstatus non-aktif dan tidak dapat digunakan.",
                    ]);
                }

                if ($item['quantity'] <= 0) {
                    throw ValidationException::withMessages([
                        'items' => "Kuantitas untuk '{$product->name}' harus lebih besar dari 0.",
                    ]);
                }

                if ($product->current_stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "Stok barang '{$product->name}' tidak mencukupi. (Tersedia: {$product->current_stock}, Diminta: {$item['quantity']}).",
                    ]);
                }

                $transaction->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => null,
                ]);

                $product->decrement('current_stock', $item['quantity']);
            }

            return $transaction->load(['items.product', 'creator']);
        });
    }

    /**
     * Catat transaksi Stock Opname / Penyesuaian Fisik Stok.
     *
     * @param array{
     *     opname_date: string,
     *     note?: string|null
     * } $headerData
     * @param list<array{
     *     product_id: int,
     *     physical_stock: int,
     *     reason?: string|null,
     *     item_notes?: string|null
     * }> $itemsData
     */
    public function recordStockOpname(array $headerData, array $itemsData, int $userId): StockOpname
    {
        if (empty($itemsData)) {
            throw ValidationException::withMessages([
                'items' => 'Minimal harus ada 1 barang dalam pemeriksaan Stock Opname.',
            ]);
        }

        return DB::transaction(function () use ($headerData, $itemsData, $userId) {
            $opnameNo = $this->generateOpnameNumber($headerData['opname_date']);

            $opname = StockOpname::create([
                'opname_no' => $opnameNo,
                'opname_date' => $headerData['opname_date'],
                'status' => 'COMPLETED',
                'note' => $headerData['note'] ?? null,
                'created_by' => $userId,
            ]);

            foreach ($itemsData as $item) {
                $product = Product::where('id', $item['product_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $product->is_active) {
                    throw ValidationException::withMessages([
                        'items' => "Barang '{$product->name}' berstatus non-aktif dan tidak dapat diopname.",
                    ]);
                }

                $physicalStock = (int) $item['physical_stock'];
                if ($physicalStock < 0) {
                    throw ValidationException::withMessages([
                        'items' => "Stok fisik barang '{$product->name}' tidak boleh bernilai negatif.",
                    ]);
                }

                $systemStock = $product->current_stock;
                $difference = $physicalStock - $systemStock;

                $opname->items()->create([
                    'product_id' => $product->id,
                    'system_stock' => $systemStock,
                    'physical_stock' => $physicalStock,
                    'difference' => $difference,
                    'reason' => $item['reason'] ?? ($difference === 0 ? 'SESUAI' : 'LAINNYA'),
                    'item_notes' => $item['item_notes'] ?? null,
                ]);

                // Perbarui stok aktual produk ke hasil hitungan fisik
                $product->update(['current_stock' => $physicalStock]);
            }

            return $opname->load(['items.product.unit', 'items.product.category', 'creator']);
        });
    }

    /**
     * Hitung riwayat Kartu Stok (Stock Card) secara kronologis untuk suatu produk.
     *
     * @return array<string, mixed>
     */
    public function getStockCard(int $productId, ?string $startDate = null, ?string $endDate = null): array
    {
        $product = Product::with(['category', 'unit'])->findOrFail($productId);

        $initialBalance = 0;
        if ($startDate) {
            // Hitung mutasi sebelum tanggal awal
            $priorIn = (int) StockTransactionItem::where('product_id', $productId)
                ->whereHas('stockTransaction', function ($query) use ($startDate) {
                    $query->where('type', 'IN')->where('transaction_date', '<', $startDate);
                })
                ->sum('quantity');

            $priorOut = (int) StockTransactionItem::where('product_id', $productId)
                ->whereHas('stockTransaction', function ($query) use ($startDate) {
                    $query->where('type', 'OUT')->where('transaction_date', '<', $startDate);
                })
                ->sum('quantity');

            $priorOpnameDiff = (int) StockOpnameItem::where('product_id', $productId)
                ->whereHas('stockOpname', function ($query) use ($startDate) {
                    $query->where('opname_date', '<', $startDate);
                })
                ->sum('difference');

            $initialBalance = $priorIn - $priorOut + $priorOpnameDiff;
        }

        // 1. Ambil transaksi masuk & keluar pada periode terpilih
        $itemsQuery = StockTransactionItem::with(['stockTransaction.supplier', 'stockTransaction.creator'])
            ->where('product_id', $productId)
            ->whereHas('stockTransaction', function ($query) use ($startDate, $endDate) {
                if ($startDate) {
                    $query->where('transaction_date', '>=', $startDate);
                }
                if ($endDate) {
                    $query->where('transaction_date', '<=', $endDate);
                }
            })
            ->join('stock_transactions', 'stock_transaction_items.stock_transaction_id', '=', 'stock_transactions.id')
            ->select('stock_transaction_items.*');

        $transactionItems = $itemsQuery->get();

        // 2. Ambil penyesuaian stock opname pada periode terpilih
        $opnameItems = StockOpnameItem::with(['stockOpname.creator'])
            ->where('product_id', $productId)
            ->whereHas('stockOpname', function ($query) use ($startDate, $endDate) {
                if ($startDate) {
                    $query->where('opname_date', '>=', $startDate);
                }
                if ($endDate) {
                    $query->where('opname_date', '<=', $endDate);
                }
            })
            ->join('stock_opnames', 'stock_opname_items.stock_opname_id', '=', 'stock_opnames.id')
            ->select('stock_opname_items.*')
            ->get();

        // 3. Gabungkan seluruh mutasi dan urutkan secara kronologis
        $timeline = collect();

        foreach ($transactionItems as $txItem) {
            $timeline->push([
                'date_sort' => $txItem->stockTransaction->transaction_date->format('Y-m-d').' '.$txItem->created_at->format('H:i:s'),
                'kind' => 'tx',
                'item' => $txItem,
            ]);
        }

        foreach ($opnameItems as $opItem) {
            $timeline->push([
                'date_sort' => $opItem->stockOpname->opname_date->format('Y-m-d').' '.$opItem->created_at->format('H:i:s'),
                'kind' => 'opname',
                'item' => $opItem,
            ]);
        }

        $sortedTimeline = $timeline->sortBy('date_sort');

        $runningBalance = $initialBalance;
        $totalIn = 0;
        $totalOut = 0;
        $ledger = [];

        foreach ($sortedTimeline as $entry) {
            if ($entry['kind'] === 'tx') {
                /** @var StockTransactionItem $item */
                $item = $entry['item'];
                $trans = $item->stockTransaction;
                $type = $trans->type;
                $qty = $item->quantity;

                if ($type === 'IN') {
                    $runningBalance += $qty;
                    $totalIn += $qty;
                    $inQty = $qty;
                    $outQty = 0;
                    $partner = $trans->supplier?->name ?? '-';
                } else {
                    $runningBalance -= $qty;
                    $totalOut += $qty;
                    $inQty = 0;
                    $outQty = $qty;
                    $partner = $trans->recipient ?? '-';
                }

                $ledger[] = [
                    'transaction_id' => $trans->id,
                    'transaction_no' => $trans->transaction_no,
                    'date' => Carbon::parse($trans->transaction_date)->format('d-m-Y'),
                    'raw_date' => $trans->transaction_date,
                    'type' => $type,
                    'partner' => $partner,
                    'note' => $trans->note,
                    'unit_price' => $item->unit_price,
                    'in_qty' => $inQty,
                    'out_qty' => $outQty,
                    'balance' => $runningBalance,
                    'url' => route('transactions.show', $trans->id),
                ];
            } else {
                /** @var StockOpnameItem $opItem */
                $opItem = $entry['item'];
                $opname = $opItem->stockOpname;
                $diff = $opItem->difference;

                if ($diff > 0) {
                    $runningBalance += $diff;
                    $totalIn += $diff;
                    $inQty = $diff;
                    $outQty = 0;
                } elseif ($diff < 0) {
                    $absDiff = abs($diff);
                    $runningBalance -= $absDiff;
                    $totalOut += $absDiff;
                    $inQty = 0;
                    $outQty = $absDiff;
                } else {
                    $inQty = 0;
                    $outQty = 0;
                }

                $reasonText = match ($opItem->reason) {
                    'RUSAK' => 'Barang Rusak',
                    'HILANG' => 'Barang Hilang',
                    'KADALUWARSA' => 'Kadaluwarsa',
                    'SELISIH_HITUNG' => 'Koreksi Hitung',
                    'SESUAI' => 'Stok Sesuai',
                    default => 'Penyesuaian Fisik',
                };

                $ledger[] = [
                    'transaction_id' => $opname->id,
                    'transaction_no' => $opname->opname_no,
                    'date' => Carbon::parse($opname->opname_date)->format('d-m-Y'),
                    'raw_date' => $opname->opname_date,
                    'type' => 'OPNAME',
                    'partner' => 'Stock Opname ('.$reasonText.')',
                    'note' => "Sistem: {$opItem->system_stock} | Fisik: {$opItem->physical_stock} | Selisih: ".($diff > 0 ? "+{$diff}" : $diff),
                    'unit_price' => null,
                    'in_qty' => $inQty,
                    'out_qty' => $outQty,
                    'balance' => $runningBalance,
                    'url' => route('opnames.show', $opname->id),
                ];
            }
        }

        return [
            'product' => $product,
            'initial_balance' => $initialBalance,
            'total_in' => $totalIn,
            'total_out' => $totalOut,
            'final_balance' => $runningBalance,
            'ledger' => $ledger,
        ];
    }

    /**
     * Rekonsiliasi integritas stok: membandingkan current_stock dengan sum histori transaksi & opname.
     *
     * @return array{product: Product, actual_stock: int, calculated_stock: int, is_matched: bool}
     */
    public function reconcileStock(int $productId): array
    {
        $product = Product::findOrFail($productId);

        $totalIn = (int) StockTransactionItem::where('product_id', $productId)
            ->whereHas('stockTransaction', fn ($q) => $q->where('type', 'IN'))
            ->sum('quantity');

        $totalOut = (int) StockTransactionItem::where('product_id', $productId)
            ->whereHas('stockTransaction', fn ($q) => $q->where('type', 'OUT'))
            ->sum('quantity');

        $totalOpnameDiff = (int) StockOpnameItem::where('product_id', $productId)
            ->sum('difference');

        $calculated = $totalIn - $totalOut + $totalOpnameDiff;

        return [
            'product' => $product,
            'actual_stock' => $product->current_stock,
            'calculated_stock' => $calculated,
            'is_matched' => ($product->current_stock === $calculated),
        ];
    }

    /**
     * Ambil data statistik ringkas untuk widget Dashboard.
     *
     * @return array<string, mixed>
     */
    public function getDashboardStats(): array
    {
        $totalProducts = Product::count();
        $activeProducts = Product::active()->count();
        $lowStockCount = Product::active()->lowStock()->count();
        $outOfStockCount = Product::active()->where('current_stock', 0)->count();

        $today = Carbon::today();
        $totalStockInToday = StockTransaction::in()->whereDate('transaction_date', $today)->count();
        $totalStockOutToday = StockTransaction::out()->whereDate('transaction_date', $today)->count();

        $recentTransactions = StockTransaction::with(['creator', 'supplier', 'items.product'])
            ->latest('transaction_date')
            ->latest('id')
            ->limit(5)
            ->get();

        $lowStockItems = Product::with(['category', 'unit'])
            ->active()
            ->lowStock()
            ->orderBy('current_stock', 'asc')
            ->limit(5)
            ->get();

        return [
            'total_products' => $totalProducts,
            'active_products' => $activeProducts,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'stock_in_today' => $totalStockInToday,
            'stock_out_today' => $totalStockOutToday,
            'recent_transactions' => $recentTransactions,
            'low_stock_items' => $lowStockItems,
        ];
    }

    /**
     * Generate format nomor transaksi unik: SIN-YYYYMMDD-XXXX atau SOUT-YYYYMMDD-XXXX.
     */
    private function generateTransactionNumber(string $type, string $date): string
    {
        $prefix = $type === 'IN' ? 'SIN' : 'SOUT';
        $formattedDate = Carbon::parse($date)->format('Ymd');
        $basePrefix = "{$prefix}-{$formattedDate}-";

        $latest = StockTransaction::where('transaction_no', 'like', "{$basePrefix}%")
            ->orderBy('transaction_no', 'desc')
            ->value('transaction_no');

        if ($latest) {
            $lastSeq = (int) substr($latest, -4);
            $nextSeq = str_pad((string) ($lastSeq + 1), 4, '0', STR_PAD_LEFT);
        } else {
            $nextSeq = '0001';
        }

        return "{$basePrefix}{$nextSeq}";
    }

    /**
     * Generate format nomor dokumen stock opname unik: SOP-YYYYMMDD-XXXX.
     */
    private function generateOpnameNumber(string $date): string
    {
        $formattedDate = Carbon::parse($date)->format('Ymd');
        $basePrefix = "SOP-{$formattedDate}-";

        $latest = StockOpname::where('opname_no', 'like', "{$basePrefix}%")
            ->orderBy('opname_no', 'desc')
            ->value('opname_no');

        if ($latest) {
            $lastSeq = (int) substr($latest, -4);
            $nextSeq = str_pad((string) ($lastSeq + 1), 4, '0', STR_PAD_LEFT);
        } else {
            $nextSeq = '0001';
        }

        return "{$basePrefix}{$nextSeq}";
    }

    /**
     * Dapatkan data produk dengan ranking dan analisis perputaran mutasi (Fast Moving, Medium Moving, Slow Moving, Non-Moving).
     *
     * @return array{
     *     products: Collection<int, Product>,
     *     summary: array{
     *         total_products: int,
     *         fast_moving: int,
     *         medium_moving: int,
     *         slow_moving: int,
     *         non_moving: int
     *     }
     * }
     */
    public function getProductsWithMovementAnalysis(
        ?string $startDate = null,
        ?string $endDate = null,
        ?int $categoryId = null,
        ?string $stockStatus = null,
        ?string $velocityStatus = null,
        string $sortBy = 'turnover'
    ): array {
        $products = Product::with(['category', 'unit'])
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when($stockStatus === 'low', fn ($q) => $q->lowStock()->where('current_stock', '>', 0))
            ->when($stockStatus === 'out', fn ($q) => $q->where('current_stock', 0))
            ->when($stockStatus === 'safe', fn ($q) => $q->whereColumn('current_stock', '>', 'minimum_stock'))
            ->withSum(['transactionItems as total_in' => function ($q) use ($startDate, $endDate) {
                $q->whereHas('stockTransaction', function ($tx) use ($startDate, $endDate) {
                    $tx->where('type', 'IN')
                        ->when($startDate, fn ($t) => $t->where('transaction_date', '>=', $startDate))
                        ->when($endDate, fn ($t) => $t->where('transaction_date', '<=', $endDate));
                });
            }], 'quantity')
            ->withSum(['transactionItems as total_out' => function ($q) use ($startDate, $endDate) {
                $q->whereHas('stockTransaction', function ($tx) use ($startDate, $endDate) {
                    $tx->where('type', 'OUT')
                        ->when($startDate, fn ($t) => $t->where('transaction_date', '>=', $startDate))
                        ->when($endDate, fn ($t) => $t->where('transaction_date', '<=', $endDate));
                });
            }], 'quantity')
            ->withCount(['transactionItems as tx_count' => function ($q) use ($startDate, $endDate) {
                $q->whereHas('stockTransaction', function ($tx) use ($startDate, $endDate) {
                    $tx->when($startDate, fn ($t) => $t->where('transaction_date', '>=', $startDate))
                        ->when($endDate, fn ($t) => $t->where('transaction_date', '<=', $endDate));
                });
            }])
            ->get();

        // 1. Normalisasi data angka mutasi
        $products->each(function (Product $p) {
            $p->total_in = (int) ($p->total_in ?? 0);
            $p->total_out = (int) ($p->total_out ?? 0);
            $p->total_movement = $p->total_in + $p->total_out;
            $p->tx_count = (int) ($p->tx_count ?? 0);
        });

        // 2. Klasifikasi Kecepatan Perputaran (Velocity Classification)
        $movingProducts = $products->filter(fn (Product $p) => $p->total_movement > 0)
            ->sortByDesc('total_movement')
            ->values();

        $movingCount = $movingProducts->count();
        $fastThreshold = max(1, (int) round($movingCount / 3));
        $mediumThreshold = max($fastThreshold + 1, (int) round($movingCount * 2 / 3));

        $products->each(function (Product $p) use ($movingProducts, $movingCount, $fastThreshold, $mediumThreshold) {
            if ($p->total_movement === 0) {
                $p->velocity_status = 'NON_MOVING';
            } else {
                $pos = $movingProducts->search(fn (Product $item) => $item->id === $p->id);
                if ($pos !== false) {
                    $rankIndex = $pos + 1;
                    if ($rankIndex <= $fastThreshold || $movingCount === 1) {
                        $p->velocity_status = 'FAST_MOVING';
                    } elseif ($rankIndex <= $mediumThreshold) {
                        $p->velocity_status = 'MEDIUM_MOVING';
                    } else {
                        $p->velocity_status = 'SLOW_MOVING';
                    }
                } else {
                    $p->velocity_status = 'SLOW_MOVING';
                }
            }
        });

        // 3. Ringkasan KPI Status Perputaran
        $summary = [
            'total_products' => $products->count(),
            'fast_moving' => $products->where('velocity_status', 'FAST_MOVING')->count(),
            'medium_moving' => $products->where('velocity_status', 'MEDIUM_MOVING')->count(),
            'slow_moving' => $products->where('velocity_status', 'SLOW_MOVING')->count(),
            'non_moving' => $products->where('velocity_status', 'NON_MOVING')->count(),
        ];

        // 4. Pengurutan / Ranking Sesuai Opsi
        $sorted = match ($sortBy) {
            'most_sold' => $products->sort(function (Product $a, Product $b) {
                if ($b->total_out === $a->total_out) {
                    return $b->total_movement <=> $a->total_movement;
                }

                return $b->total_out <=> $a->total_out;
            }),
            'most_in' => $products->sort(function (Product $a, Product $b) {
                if ($b->total_in === $a->total_in) {
                    return $b->total_movement <=> $a->total_movement;
                }

                return $b->total_in <=> $a->total_in;
            }),
            'stock_desc' => $products->sortByDesc('current_stock'),
            'stock_asc' => $products->sortBy('current_stock'),
            'name' => $products->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE),
            default => $products->sort(function (Product $a, Product $b) {
                // Default 'turnover': Total In + Out terbanyak, lalu Total Out, lalu Stok
                if ($b->total_movement === $a->total_movement) {
                    if ($b->total_out === $a->total_out) {
                        return $b->current_stock <=> $a->current_stock;
                    }

                    return $b->total_out <=> $a->total_out;
                }

                return $b->total_movement <=> $a->total_movement;
            }),
        };

        // 5. Berikan nomor urut ranking (#1, #2, ...)
        $rank = 1;
        $ranked = $sorted->values()->map(function (Product $p) use (&$rank) {
            $p->rank = $rank++;

            return $p;
        });

        // 6. Filter status perputaran jika dipilih pengguna
        if ($velocityStatus) {
            $ranked = $ranked->where('velocity_status', $velocityStatus)->values();
        }

        return [
            'products' => $ranked,
            'summary' => $summary,
        ];
    }
}
