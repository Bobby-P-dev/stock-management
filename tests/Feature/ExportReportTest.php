<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportReportTest extends TestCase
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
            'name' => 'Admin Export',
            'email' => 'admin_export@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Makanan Ringan',
            'is_active' => true,
        ]);

        $unit = Unit::create([
            'name' => 'Bungkus',
            'symbol' => 'BKS',
            'is_active' => true,
        ]);

        $supplier = Supplier::create([
            'code' => 'SUP-EXP-001',
            'name' => 'PT Snack Sejahtera',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'sku' => 'PRD-SNK-001',
            'name' => 'Keripik Singkong Balado',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'minimum_stock' => 10,
            'current_stock' => 100,
            'is_active' => true,
        ]);

        $this->stockService->recordStockIn(
            [
                'transaction_date' => now()->toDateString(),
                'supplier_id' => $supplier->id,
                'note' => 'Pasokan awal',
            ],
            [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 20,
                    'unit_price' => 5000,
                ],
            ],
            $this->admin->id
        );

        $this->stockService->recordStockOut(
            [
                'transaction_date' => now()->toDateString(),
                'recipient' => 'Divisi Penjualan',
                'note' => 'Display toko',
            ],
            [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                ],
            ],
            $this->admin->id
        );
    }

    public function test_can_export_stock_report_as_excel_table(): void
    {
        $response = $this->actingAs($this->admin)->get(route('export.stock'));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/vnd.ms-excel', $response->headers->get('Content-Type'));

        $content = $response->getContent();
        $this->assertStringContainsString('<table', $content);
        $this->assertStringContainsString('Kode SKU', $content);
        $this->assertStringContainsString('PRD-SNK-001', $content);
        $this->assertStringContainsString('Keripik Singkong Balado', $content);
    }

    public function test_can_export_stock_in_report_as_excel_table(): void
    {
        $response = $this->actingAs($this->admin)->get(route('export.stock-in'));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/vnd.ms-excel', $response->headers->get('Content-Type'));

        $content = $response->getContent();
        $this->assertStringContainsString('<table', $content);
        $this->assertStringContainsString('No. Faktur', $content);
        $this->assertStringContainsString('PT Snack Sejahtera', $content);
    }

    public function test_can_export_stock_out_report_as_excel_table(): void
    {
        $response = $this->actingAs($this->admin)->get(route('export.stock-out'));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/vnd.ms-excel', $response->headers->get('Content-Type'));

        $content = $response->getContent();
        $this->assertStringContainsString('<table', $content);
        $this->assertStringContainsString('No. Transaksi', $content);
        $this->assertStringContainsString('Divisi Penjualan', $content);
    }

    public function test_can_export_stock_card_as_excel_table(): void
    {
        $response = $this->actingAs($this->admin)->get(route('export.stock-card', $this->product->id));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/vnd.ms-excel', $response->headers->get('Content-Type'));

        $content = $response->getContent();
        $this->assertStringContainsString('<table', $content);
        $this->assertStringContainsString('BUKU KARTU STOK INVENTARIS GUDANG', $content);
        $this->assertStringContainsString('PRD-SNK-001', $content);
    }

    public function test_can_export_stock_opname_document_as_excel_table(): void
    {
        $opname = $this->stockService->recordStockOpname(
            [
                'opname_date' => now()->toDateString(),
                'note' => 'Pemeriksaan akhir periode',
            ],
            [
                [
                    'product_id' => $this->product->id,
                    'physical_stock' => 110,
                    'reason' => 'SELISIH_HITUNG',
                    'item_notes' => null,
                ],
            ],
            $this->admin->id
        );

        $response = $this->actingAs($this->admin)->get(route('export.opname', $opname->id));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/vnd.ms-excel', $response->headers->get('Content-Type'));

        $content = $response->getContent();
        $this->assertStringContainsString('<table', $content);
        $this->assertStringContainsString('BERITA ACARA HASIL STOCK OPNAME GUDANG', $content);
        $this->assertStringContainsString($opname->opname_no, $content);
    }
}
