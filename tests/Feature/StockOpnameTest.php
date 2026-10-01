<?php

namespace Tests\Feature;

use App\Livewire\Opnames\StockOpnameCreate;
use App\Livewire\Opnames\StockOpnameIndex;
use App\Livewire\Opnames\StockOpnameShow;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockOpnameTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Product $product;

    private StockService $stockService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockService = app(StockService::class);

        $this->admin = User::create([
            'name' => 'Admin Opname',
            'email' => 'admin_opname@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Bahan Baku',
            'is_active' => true,
        ]);

        $unit = Unit::create([
            'name' => 'Kilogram',
            'symbol' => 'KG',
            'is_active' => true,
        ]);

        $supplier = Supplier::create([
            'code' => 'SUP-OPN-001',
            'name' => 'CV Sumber Makmur',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'sku' => 'PRD-OPN-001',
            'name' => 'Tepung Terigu Segitiga',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'minimum_stock' => 10,
            'current_stock' => 0,
            'is_active' => true,
        ]);

        // Catat saldo stok awal 50 unit via transaksi masuk resmi
        $this->stockService->recordStockIn(
            [
                'transaction_date' => now()->toDateString(),
                'supplier_id' => $supplier->id,
                'note' => 'Penerimaan stok awal',
            ],
            [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 50,
                    'unit_price' => 12000,
                ],
            ],
            $this->admin->id
        );
    }

    public function test_can_record_stock_opname_and_adjust_product_stock(): void
    {
        $opname = $this->stockService->recordStockOpname(
            [
                'opname_date' => now()->toDateString(),
                'note' => 'Audit stok fisik akhir bulan',
            ],
            [
                [
                    'product_id' => $this->product->id,
                    'physical_stock' => 46, // Selisih -4 (rusak/bocor)
                    'reason' => 'RUSAK',
                    'item_notes' => '4 bungkus rusak terkena air',
                ],
            ],
            $this->admin->id
        );

        $this->assertDatabaseHas('stock_opnames', [
            'id' => $opname->id,
            'opname_no' => $opname->opname_no,
        ]);

        $this->assertDatabaseHas('stock_opname_items', [
            'stock_opname_id' => $opname->id,
            'product_id' => $this->product->id,
            'system_stock' => 50,
            'physical_stock' => 46,
            'difference' => -4,
            'reason' => 'RUSAK',
        ]);

        // Stok produk di tabel products harus terupdate menjadi 46
        $this->assertEquals(46, $this->product->fresh()->current_stock);
    }

    public function test_stock_opname_updates_stock_card_ledger_and_running_balance(): void
    {
        // Catat opname
        $this->stockService->recordStockOpname(
            [
                'opname_date' => now()->toDateString(),
                'note' => 'Penyesuaian stok opname',
            ],
            [
                [
                    'product_id' => $this->product->id,
                    'physical_stock' => 45, // -5
                    'reason' => 'HILANG',
                    'item_notes' => 'Hilang saat pemindahan rak',
                ],
            ],
            $this->admin->id
        );

        $card = $this->stockService->getStockCard($this->product->id);

        $this->assertEquals(45, $card['final_balance']);
        $this->assertNotEmpty($card['ledger']);

        $lastRow = end($card['ledger']);
        $this->assertEquals('OPNAME', $lastRow['type']);
        $this->assertEquals(5, $lastRow['out_qty']);
        $this->assertEquals(45, $lastRow['balance']);
    }

    public function test_reconcile_stock_accounts_for_opname_difference(): void
    {
        $this->stockService->recordStockOpname(
            [
                'opname_date' => now()->toDateString(),
                'note' => 'Penyesuaian stok surplus',
            ],
            [
                [
                    'product_id' => $this->product->id,
                    'physical_stock' => 55, // +5 surplus
                    'reason' => 'SELISIH_HITUNG',
                    'item_notes' => 'Kelebihan penerimaan supplier yang belum tercatat',
                ],
            ],
            $this->admin->id
        );

        $reconcile = $this->stockService->reconcileStock($this->product->id);

        $this->assertTrue($reconcile['is_matched']);
        $this->assertEquals(55, $reconcile['actual_stock']);
        $this->assertEquals(55, $reconcile['calculated_stock']);
    }

    public function test_stock_opname_pages_can_be_rendered(): void
    {
        $opname = $this->stockService->recordStockOpname(
            [
                'opname_date' => now()->toDateString(),
                'note' => 'Audit berkala',
            ],
            [
                [
                    'product_id' => $this->product->id,
                    'physical_stock' => 50,
                    'reason' => 'SESUAI',
                    'item_notes' => null,
                ],
            ],
            $this->admin->id
        );

        $this->actingAs($this->admin);

        Livewire::test(StockOpnameIndex::class)->assertStatus(200);
        Livewire::test(StockOpnameCreate::class)->assertStatus(200);
        Livewire::test(StockOpnameShow::class, ['opname' => $opname])->assertStatus(200);
    }
}
