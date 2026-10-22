<?php

namespace Tests\Feature;

use App\Livewire\Opnames\StockOpnameCreate;
use App\Livewire\Opnames\StockOpnameEdit;
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
                    'physical_stock' => 46,
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

        $this->assertEquals(46, $this->product->fresh()->current_stock);
    }

    public function test_stock_opname_updates_stock_card_ledger_and_running_balance(): void
    {
        $this->stockService->recordStockOpname(
            [
                'opname_date' => now()->toDateString(),
                'note' => 'Penyesuaian stok opname',
            ],
            [
                [
                    'product_id' => $this->product->id,
                    'physical_stock' => 45,
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
                    'physical_stock' => 55,
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
        Livewire::test(StockOpnameEdit::class, ['opname' => $opname])->assertStatus(200);
    }

    public function test_stock_opname_index_renders_polished_layout_and_filters_correctly(): void
    {
        $earlyOpname = $this->stockService->recordStockOpname(
            [
                'opname_date' => '2026-10-10',
                'note' => 'Audit stok awal pekan',
            ],
            [
                [
                    'product_id' => $this->product->id,
                    'physical_stock' => 50,
                    'reason' => 'SESUAI',
                    'item_notes' => 'Kondisi barang baik',
                ],
            ],
            $this->admin->id
        );

        $lateOpname = $this->stockService->recordStockOpname(
            [
                'opname_date' => '2026-10-20',
                'note' => 'Pemeriksaan akhir periode',
            ],
            [
                [
                    'product_id' => $this->product->id,
                    'physical_stock' => 48,
                    'reason' => 'RUSAK',
                    'item_notes' => 'Dua kemasan bocor',
                ],
            ],
            $this->admin->id
        );

        $this->actingAs($this->admin);

        Livewire::test(StockOpnameIndex::class)
            ->assertSee('Stock Opname & Penyesuaian Stok', false)
            ->assertSee('Catat Stock Opname Baru')
            ->assertSee($earlyOpname->opname_no)
            ->assertSee($lateOpname->opname_no)
            ->assertSee('Audit stok awal pekan')
            ->assertSee('Pemeriksaan akhir periode')
            ->set('search', $earlyOpname->opname_no)
            ->assertSee($earlyOpname->opname_no)
            ->assertDontSee($lateOpname->opname_no)
            ->set('search', '')
            ->set('startDate', '2026-10-15')
            ->assertDontSee($earlyOpname->opname_no)
            ->assertSee($lateOpname->opname_no)
            ->set('startDate', '')
            ->set('endDate', '2026-10-15')
            ->assertSee($earlyOpname->opname_no)
            ->assertDontSee($lateOpname->opname_no)
            ->set('endDate', '')
            ->set('search', 'Admin Opname')
            ->assertSee($earlyOpname->opname_no)
            ->assertSee($lateOpname->opname_no)
            ->set('search', 'Pemeriksaan akhir periode')
            ->assertDontSee($earlyOpname->opname_no)
            ->assertSee($lateOpname->opname_no)
            ->set('search', 'DOKUMEN_TIDAK_ADA')
            ->assertSee('Belum ada riwayat dokumen Stock Opname');
    }

    public function test_stock_opname_detail_renders_polished_layout_and_kpi_cards(): void
    {
        $opname = $this->stockService->recordStockOpname(
            [
                'opname_date' => '2026-10-22',
                'note' => 'Audit stok fisik gudang utama',
            ],
            [
                [
                    'product_id' => $this->product->id,
                    'physical_stock' => 46,
                    'reason' => 'RUSAK',
                    'item_notes' => '4 bungkus rusak terkena air',
                ],
            ],
            $this->admin->id
        );

        $this->actingAs($this->admin);

        Livewire::test(StockOpnameShow::class, ['opname' => $opname])
            ->assertSee('Detail Stock Opname')
            ->assertSee('Kembali ke Daftar Opname')
            ->assertSee('Edit Opname')
            ->assertSee('Hapus')
            ->assertSee('Export Excel (.xls)')
            ->assertSee('Cetak Berita Acara')
            ->assertSeeHtml('href="'.route('opnames.index').'"')
            ->assertSeeHtml('href="'.route('opnames.edit', $opname->id).'"')
            ->assertSeeHtml('href="'.route('export.opname', $opname->id).'"')
            ->assertSeeHtml('onclick="window.print()"')
            ->assertSee($opname->opname_no)
            ->assertSee('Berkah Mandiri Inventory')
            ->assertSee('Berita Acara Stock Opname')
            ->assertSeeInOrder(['Total Diperiksa', '1 Barang', 'Stok Sesuai', '0 Barang', 'Kurang / Rusak', '1 Barang', 'Lebih / Surplus', '0 Barang'])
            ->assertSee($this->product->sku)
            ->assertSee($this->product->name)
            ->assertSee('Bahan Baku')
            ->assertSee('Barang Rusak')
            ->assertSee('4 bungkus rusak terkena air');
    }

    public function test_can_update_stock_opname_and_reconcile_product_stock(): void
    {
        $opname = $this->stockService->recordStockOpname(
            [
                'opname_date' => now()->toDateString(),
                'note' => 'Audit awal',
            ],
            [
                [
                    'product_id' => $this->product->id,
                    'physical_stock' => 45,
                    'reason' => 'RUSAK',
                    'item_notes' => 'Rusak 5',
                ],
            ],
            $this->admin->id
        );

        $this->assertEquals(45, $this->product->fresh()->current_stock);

        Livewire::actingAs($this->admin)
            ->test(StockOpnameEdit::class, ['opname' => $opname])
            ->set('items.0.physical_stock', 48)
            ->set('items.0.reason', 'RUSAK')
            ->set('items.0.item_notes', 'Hanya rusak 2')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('opnames.show', $opname->id));

        $this->assertEquals(48, $this->product->fresh()->current_stock);
        $this->assertDatabaseHas('stock_opname_items', [
            'stock_opname_id' => $opname->id,
            'product_id' => $this->product->id,
            'physical_stock' => 48,
            'difference' => -2,
        ]);
    }

    public function test_can_delete_stock_opname_and_revert_product_stock(): void
    {
        $opname = $this->stockService->recordStockOpname(
            [
                'opname_date' => now()->toDateString(),
                'note' => 'Audit salah input',
            ],
            [
                [
                    'product_id' => $this->product->id,
                    'physical_stock' => 40,
                    'reason' => 'RUSAK',
                    'item_notes' => 'Salah hitung',
                ],
            ],
            $this->admin->id
        );

        $this->assertEquals(40, $this->product->fresh()->current_stock);

        Livewire::actingAs($this->admin)
            ->test(StockOpnameIndex::class)
            ->call('delete', $opname->id);

        $this->assertDatabaseMissing('stock_opnames', ['id' => $opname->id]);
        $this->assertDatabaseMissing('stock_opname_items', ['stock_opname_id' => $opname->id]);

        // Stock reverted from 40 back to 50
        $this->assertEquals(50, $this->product->fresh()->current_stock);
    }

    public function test_stock_opname_create_renders_polished_layout_and_handles_dynamic_rows(): void
    {
        $this->actingAs($this->admin);

        $test = Livewire::test(StockOpnameCreate::class)
            ->assertSee('Formulir Pemeriksaan Stock Opname')
            ->assertSee('Audit Fisik')
            ->assertSee('Daftar Opname')
            ->assertSee('Informasi Pelaksanaan Opname')
            ->assertSee('Tanggal Perhitungan Fisik')
            ->assertSee('Catatan / Berita Acara Singkat')
            ->assertSee('Daftar Barang yang Diperiksa')
            ->assertSee('Tambah Baris Barang')
            ->assertSee('Simpan & Sesuaikan Stok Sistem', false)
            ->assertSeeHtml('href="'.route('opnames.index').'"')
            ->assertCount('items', 1);

        $test->call('addItem')
            ->assertCount('items', 2);

        $test->set('items.0.product_id', $this->product->id)
            ->assertSet('items.0.system_stock', 50)
            ->assertSet('items.0.physical_stock', 50)
            ->assertSet('items.0.difference', 0)
            ->assertSet('items.0.reason', 'SESUAI');

        $test->set('items.0.physical_stock', 42)
            ->assertSet('items.0.difference', -8)
            ->assertSet('items.0.reason', 'RUSAK');

        $test->call('removeItem', 1)
            ->assertCount('items', 1);

        $test->call('save')
            ->assertHasNoErrors();

        $this->assertEquals(42, $this->product->fresh()->current_stock);
        $this->assertDatabaseHas('stock_opname_items', [
            'product_id' => $this->product->id,
            'physical_stock' => 42,
            'difference' => -8,
            'reason' => 'RUSAK',
        ]);
    }

    public function test_stock_opname_edit_renders_polished_layout_and_updates_rows(): void
    {
        $opname = $this->stockService->recordStockOpname(
            [
                'opname_date' => '2026-10-22',
                'note' => 'Audit awal bulan',
            ],
            [
                [
                    'product_id' => $this->product->id,
                    'physical_stock' => 45,
                    'reason' => 'RUSAK',
                    'item_notes' => '5 kantong rusak',
                ],
            ],
            $this->admin->id
        );

        $this->actingAs($this->admin);

        Livewire::test(StockOpnameEdit::class, ['opname' => $opname])
            ->assertSee('Edit Berita Acara Stock Opname')
            ->assertSee('Koreksi Audit')
            ->assertSee($opname->opname_no)
            ->assertSee('Kembali ke Detail')
            ->assertSeeHtml('href="'.route('opnames.show', $opname->id).'"')
            ->assertSee('Informasi Dokumen Opname')
            ->assertSee('Tanggal Pemeriksaan Fisik')
            ->assertSee('Catatan / Keterangan Berita Acara')
            ->assertSee('Daftar Barang yang Diperiksa')
            ->assertSee('Tambah Baris Barang')
            ->assertSee('Perbarui & Sesuaikan Stok Sistem', false)
            ->assertCount('items', 1)
            ->set('items.0.physical_stock', 47)
            ->assertSet('items.0.difference', -3)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('opnames.show', $opname->id));

        $this->assertEquals(47, $this->product->fresh()->current_stock);
    }

    public function test_stock_opname_create_validates_required_fields_and_duplicate_products(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(StockOpnameCreate::class)
            ->set('opname_date', '')
            ->set('items.0.product_id', '')
            ->set('items.0.physical_stock', -1)
            ->call('save')
            ->assertHasErrors([
                'opname_date' => 'required',
                'items.0.product_id' => 'required',
                'items.0.physical_stock' => 'min',
            ]);

        Livewire::test(StockOpnameCreate::class)
            ->set('items.0.product_id', $this->product->id)
            ->set('items.0.physical_stock', 50)
            ->call('addItem')
            ->set('items.1.product_id', $this->product->id)
            ->set('items.1.physical_stock', 50)
            ->call('save')
            ->assertHasErrors(['items']);
    }
}
