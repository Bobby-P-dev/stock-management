<?php

namespace Tests\Feature;

use App\Livewire\Reports\ReportIndex;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportRankingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Product $productFast;

    private Product $productMedium;

    private Product $productSlow;

    private Product $productNonMoving;

    private StockService $stockService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockService = app(StockService::class);

        $this->admin = User::create([
            'name' => 'Admin Gudang',
            'email' => 'admin_gudang@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Kategori Utama',
            'is_active' => true,
        ]);

        $unit = Unit::create([
            'name' => 'Pieces',
            'symbol' => 'PCS',
            'is_active' => true,
        ]);

        $supplier = Supplier::create([
            'code' => 'SUP-RANK-01',
            'name' => 'Supplier Rank 1',
            'is_active' => true,
        ]);

        // Produk 1: Sangat Sering Mutasi (Fast Moving)
        $this->productFast = Product::create([
            'sku' => 'PRD-FAST-01',
            'name' => 'Barang Paling Laku',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'minimum_stock' => 10,
            'current_stock' => 0,
            'is_active' => true,
        ]);

        // Produk 2: Mutasi Sedang (Medium Moving)
        $this->productMedium = Product::create([
            'sku' => 'PRD-MED-02',
            'name' => 'Barang Sedang',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'minimum_stock' => 10,
            'current_stock' => 0,
            'is_active' => true,
        ]);

        // Produk 3: Mutasi Rendah (Slow Moving)
        $this->productSlow = Product::create([
            'sku' => 'PRD-SLOW-03',
            'name' => 'Barang Lambat',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'minimum_stock' => 10,
            'current_stock' => 0,
            'is_active' => true,
        ]);

        // Produk 4: Tidak Pernah Mutasi (Non-Moving)
        $this->productNonMoving = Product::create([
            'sku' => 'PRD-DEAD-04',
            'name' => 'Barang Mengendap',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'minimum_stock' => 5,
            'current_stock' => 20,
            'is_active' => true,
        ]);

        // Catat mutasi masuk
        $this->stockService->recordStockIn(
            [
                'transaction_date' => now()->toDateString(),
                'supplier_id' => $supplier->id,
                'note' => 'Pasokan pertama',
            ],
            [
                ['product_id' => $this->productFast->id, 'quantity' => 100],
                ['product_id' => $this->productMedium->id, 'quantity' => 40],
                ['product_id' => $this->productSlow->id, 'quantity' => 10],
            ],
            $this->admin->id
        );

        // Catat mutasi keluar (penjualan / pemakaian)
        $this->stockService->recordStockOut(
            [
                'transaction_date' => now()->toDateString(),
                'recipient' => 'Pelanggan Toko',
                'note' => 'Penjualan retail',
            ],
            [
                ['product_id' => $this->productFast->id, 'quantity' => 80],
                ['product_id' => $this->productMedium->id, 'quantity' => 20],
                ['product_id' => $this->productSlow->id, 'quantity' => 2],
            ],
            $this->admin->id
        );
    }

    public function test_products_are_ranked_by_turnover_descending(): void
    {
        $result = $this->stockService->getProductsWithMovementAnalysis();

        $products = $result['products'];

        // Produk Fast harus di peringkat #1 (Total In 100 + Out 80 = 180)
        $this->assertEquals($this->productFast->id, $products->first()->id);
        $this->assertEquals(1, $products->first()->rank);
        $this->assertEquals(180, $products->first()->total_movement);
        $this->assertEquals('FAST_MOVING', $products->first()->velocity_status);

        // Produk Non-Moving harus berstatus NON_MOVING
        $nonMoving = $products->firstWhere('id', $this->productNonMoving->id);
        $this->assertNotNull($nonMoving);
        $this->assertEquals(0, $nonMoving->total_movement);
        $this->assertEquals('NON_MOVING', $nonMoving->velocity_status);

        // Verifikasi ringkasan KPI
        $this->assertEquals(4, $result['summary']['total_products']);
        $this->assertEquals(1, $result['summary']['fast_moving']);
        $this->assertEquals(1, $result['summary']['non_moving']);
    }

    public function test_can_sort_by_most_sold_product(): void
    {
        $result = $this->stockService->getProductsWithMovementAnalysis(sortBy: 'most_sold');

        $first = $result['products']->first();
        $this->assertEquals($this->productFast->id, $first->id);
        $this->assertEquals(80, $first->total_out);
    }

    public function test_can_filter_by_velocity_status(): void
    {
        $resultFast = $this->stockService->getProductsWithMovementAnalysis(velocityStatus: 'FAST_MOVING');
        $this->assertCount(1, $resultFast['products']);
        $this->assertEquals('PRD-FAST-01', $resultFast['products']->first()->sku);

        $resultNonMoving = $this->stockService->getProductsWithMovementAnalysis(velocityStatus: 'NON_MOVING');
        $this->assertCount(1, $resultNonMoving['products']);
        $this->assertEquals('PRD-DEAD-04', $resultNonMoving['products']->first()->sku);
    }

    public function test_report_index_component_renders_ranking_and_kpi(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ReportIndex::class)
            ->set('activeTab', 'stock')
            ->assertSee('Fast Moving (Paling Laku)')
            ->assertSee('Non-Moving (Stok Diam)')
            ->assertSee('PRD-FAST-01')
            ->assertSee('Barang Paling Laku')
            ->assertSee('Fast Moving');
    }

    public function test_export_stock_includes_movement_ranking(): void
    {
        $response = $this->actingAs($this->admin)->get(route('export.stock', ['sort_by' => 'turnover']));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/vnd.ms-excel', $response->headers->get('Content-Type'));

        $content = $response->getContent();
        $this->assertStringContainsString('LAPORAN POSISI STOK & RANKING PERPUTARAN BARANG', $content);
        $this->assertStringContainsString('Fast Moving', $content);
        $this->assertStringContainsString('PRD-FAST-01', $content);
    }
}
