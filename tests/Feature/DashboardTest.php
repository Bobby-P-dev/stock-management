<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $petugas;

    private Category $category;

    private Unit $unit;

    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Gudang',
            'email' => 'admin@gudang.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->petugas = User::create([
            'name' => 'Petugas Gudang',
            'email' => 'petugas@gudang.test',
            'password' => bcrypt('password'),
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'name' => 'Bahan Baku',
            'is_active' => true,
        ]);

        $this->unit = Unit::create([
            'name' => 'Pieces',
            'symbol' => 'PCS',
            'is_active' => true,
        ]);

        $this->supplier = Supplier::create([
            'code' => 'SUP-001',
            'name' => 'PT Sumber Makmur',
            'is_active' => true,
        ]);
    }

    public function test_dashboard_renders_compact_header_kpi_cards_and_sections(): void
    {
        $zeroStockProduct = Product::create([
            'sku' => 'SKU-ZERO-01',
            'name' => 'Barang Kosong Uji',
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'minimum_stock' => 10,
            'current_stock' => 0,
            'is_active' => true,
        ]);

        $lowStockProduct = Product::create([
            'sku' => 'SKU-LOW-02',
            'name' => 'Barang Menipis Uji',
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'minimum_stock' => 10,
            'current_stock' => 4,
            'is_active' => true,
        ]);

        $tx = app(StockService::class)->recordStockIn(
            [
                'transaction_date' => now()->toDateString(),
                'supplier_id' => $this->supplier->id,
            ],
            [
                ['product_id' => $lowStockProduct->id, 'quantity' => 2],
            ],
            $this->admin->id
        );

        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Dashboard Gudang')
            ->assertSee('Pantau persediaan dan aktivitas operasional gudang.')
            ->assertSee(route('transactions.in'))
            ->assertSee(route('transactions.out'))
            ->assertSee(route('products.index'))
            ->assertSee(route('transactions.index'))
            ->assertSee(route('stock-card', ['productId' => $zeroStockProduct->id]))
            ->assertSee(route('transactions.show', $tx->id))
            ->assertSee('Total Jenis Barang')
            ->assertSee('Barang Perlu Restock')
            ->assertSee('Barang Masuk Hari Ini')
            ->assertSee('Barang Keluar Hari Ini')
            ->assertSee('Stok Perlu Perhatian')
            ->assertSee('Aktivitas Mutasi Terbaru')
            ->assertSee($zeroStockProduct->sku)
            ->assertSee('Stok Habis')
            ->assertSee($lowStockProduct->sku)
            ->assertSee('Menipis')
            ->assertSee('Kartu Stok')
            ->assertSee($tx->transaction_no);
    }

    public function test_dashboard_shows_empty_states_when_no_alerts_or_transactions(): void
    {
        $response = $this->actingAs($this->petugas)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Stok Barang Aman')
            ->assertSee('Belum Ada Aktivitas')
            ->assertDontSee(route('users.index'));
    }

    public function test_dashboard_audit_reconciliation_action_works_and_dispatches_swal(): void
    {
        $product = Product::create([
            'sku' => 'SKU-AUDIT-01',
            'name' => 'Barang Audit',
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'minimum_stock' => 5,
            'current_stock' => 0,
            'is_active' => true,
        ]);

        app(StockService::class)->recordStockIn(
            [
                'transaction_date' => now()->toDateString(),
                'supplier_id' => $this->supplier->id,
            ],
            [
                ['product_id' => $product->id, 'quantity' => 12],
            ],
            $this->admin->id
        );

        Livewire::actingAs($this->admin)
            ->test(Dashboard::class)
            ->assertSet('hasAudited', false)
            ->call('runAudit')
            ->assertSet('hasAudited', true)
            ->assertDispatched('swal')
            ->assertSee('SKU-AUDIT-01')
            ->assertSee('Sinkron (Sesuai)');
    }

    public function test_inactive_user_cannot_execute_run_audit(): void
    {
        $inactiveUser = User::create([
            'name' => 'Nonaktif User',
            'email' => 'nonaktif@gudang.test',
            'password' => bcrypt('password'),
            'role' => 'petugas',
            'is_active' => false,
        ]);

        Livewire::actingAs($inactiveUser)
            ->test(Dashboard::class)
            ->call('runAudit')
            ->assertForbidden();
    }

    public function test_layout_renders_dynamic_breadcrumbs_and_profile_dropdown(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('AG')
            ->assertSee('Admin Gudang')
            ->assertSee('Administrator')
            ->assertSee('Keluar');

        $this->assertSame(1, substr_count($response->getContent(), '@click="confirmLogout()"'));

        $this->actingAs($this->admin)
            ->get(route('products.index'))
            ->assertOk()
            ->assertSee('Master Data')
            ->assertSee('Data Barang');

        $this->actingAs($this->admin)
            ->get(route('transactions.in'))
            ->assertOk()
            ->assertSee('Transaksi Stok')
            ->assertSee('Barang Masuk');
    }

    public function test_logout_invalidates_session_and_redirects_to_login(): void
    {
        $response = $this->actingAs($this->admin)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
