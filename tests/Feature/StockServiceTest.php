<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $petugas;

    private Category $category;

    private Unit $unit;

    private Supplier $supplier;

    private Product $product;

    private StockService $stockService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockService = app(StockService::class);

        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->petugas = User::create([
            'name' => 'Petugas Test',
            'email' => 'petugas@test.com',
            'password' => bcrypt('password'),
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'name' => 'Kategori Uji',
            'is_active' => true,
        ]);

        $this->unit = Unit::create([
            'name' => 'Pieces',
            'symbol' => 'PCS',
            'is_active' => true,
        ]);

        $this->supplier = Supplier::create([
            'code' => 'SUP-TEST',
            'name' => 'Supplier Uji',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'sku' => 'SKU-TEST-001',
            'name' => 'Barang Uji Coba',
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'minimum_stock' => 5,
            'current_stock' => 0,
            'is_active' => true,
        ]);
    }

    public function test_stock_in_increases_product_stock_and_records_transaction(): void
    {
        $tx = $this->stockService->recordStockIn(
            [
                'transaction_date' => '2026-09-30',
                'supplier_id' => $this->supplier->id,
                'note' => 'Penambahan stok masuk pertama',
            ],
            [
                ['product_id' => $this->product->id, 'quantity' => 50, 'unit_price' => 10000],
            ],
            $this->admin->id
        );

        $this->assertDatabaseHas('stock_transactions', [
            'id' => $tx->id,
            'type' => 'IN',
            'created_by' => $this->admin->id,
        ]);

        $this->assertDatabaseHas('stock_transaction_items', [
            'stock_transaction_id' => $tx->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
        ]);

        $this->product->refresh();
        $this->assertEquals(50, $this->product->current_stock);
    }

    public function test_stock_out_decreases_product_stock_and_records_transaction(): void
    {
        // Berikan stok awal 40
        $this->stockService->recordStockIn(
            ['transaction_date' => '2026-09-30', 'supplier_id' => $this->supplier->id],
            [['product_id' => $this->product->id, 'quantity' => 40]],
            $this->admin->id
        );

        // Keluarkan stok 15
        $outTx = $this->stockService->recordStockOut(
            [
                'transaction_date' => '2026-09-30',
                'recipient' => 'Divisi Operasional',
                'note' => 'Pengambilan barang',
            ],
            [
                ['product_id' => $this->product->id, 'quantity' => 15],
            ],
            $this->petugas->id
        );

        $this->assertDatabaseHas('stock_transactions', [
            'id' => $outTx->id,
            'type' => 'OUT',
            'recipient' => 'Divisi Operasional',
        ]);

        $this->product->refresh();
        $this->assertEquals(25, $this->product->current_stock);
    }

    public function test_stock_out_prevents_negative_stock_and_throws_validation_exception(): void
    {
        // Stok saat ini 10
        $this->stockService->recordStockIn(
            ['transaction_date' => '2026-09-30', 'supplier_id' => $this->supplier->id],
            [['product_id' => $this->product->id, 'quantity' => 10]],
            $this->admin->id
        );

        $this->expectException(ValidationException::class);

        // Coba keluarkan stok 15 (melebihi 10) -> Harus gagal
        $this->stockService->recordStockOut(
            [
                'transaction_date' => '2026-09-30',
                'recipient' => 'Divisi Operasional',
            ],
            [
                ['product_id' => $this->product->id, 'quantity' => 15],
            ],
            $this->petugas->id
        );

        // Pastikan stok tidak berubah (tetap 10)
        $this->product->refresh();
        $this->assertEquals(10, $this->product->current_stock);
    }

    public function test_stock_card_calculates_chronological_running_balance_correctly(): void
    {
        // 1. Masuk 50 -> Saldo 50
        $this->stockService->recordStockIn(
            ['transaction_date' => '2026-09-01', 'supplier_id' => $this->supplier->id],
            [['product_id' => $this->product->id, 'quantity' => 50]],
            $this->admin->id
        );

        // 2. Keluar 10 -> Saldo 40
        $this->stockService->recordStockOut(
            ['transaction_date' => '2026-09-05', 'recipient' => 'Divisi A'],
            [['product_id' => $this->product->id, 'quantity' => 10]],
            $this->petugas->id
        );

        // 3. Masuk 30 -> Saldo 70
        $this->stockService->recordStockIn(
            ['transaction_date' => '2026-09-12', 'supplier_id' => $this->supplier->id],
            [['product_id' => $this->product->id, 'quantity' => 30]],
            $this->admin->id
        );

        // 4. Keluar 20 -> Saldo 50
        $this->stockService->recordStockOut(
            ['transaction_date' => '2026-09-20', 'recipient' => 'Divisi B'],
            [['product_id' => $this->product->id, 'quantity' => 20]],
            $this->petugas->id
        );

        $card = $this->stockService->getStockCard($this->product->id);

        $this->assertEquals(80, $card['total_in']);
        $this->assertEquals(30, $card['total_out']);
        $this->assertEquals(50, $card['final_balance']);
        $this->assertCount(4, $card['ledger']);

        // Verifikasi saldo per baris
        $this->assertEquals(50, $card['ledger'][0]['balance']);
        $this->assertEquals(40, $card['ledger'][1]['balance']);
        $this->assertEquals(70, $card['ledger'][2]['balance']);
        $this->assertEquals(50, $card['ledger'][3]['balance']);
    }

    public function test_petugas_cannot_access_admin_user_management(): void
    {
        $response = $this->actingAs($this->petugas)->get('/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_access_user_management(): void
    {
        $response = $this->actingAs($this->admin)->get('/users');

        $response->assertStatus(200);
    }
}
