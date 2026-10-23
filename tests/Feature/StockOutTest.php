<?php

namespace Tests\Feature;

use App\Livewire\Transactions\StockOutCreate;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockOutTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Product $product1;

    private Product $product2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Stock Out',
            'email' => 'admin_out@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Alat Tulis Kantor',
            'is_active' => true,
        ]);

        $unit = Unit::create([
            'name' => 'Pcs',
            'symbol' => 'PCS',
            'is_active' => true,
        ]);

        $this->product1 = Product::create([
            'sku' => 'PRD-OUT-001',
            'name' => 'Buku Catatan Eksekutif',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'minimum_stock' => 5,
            'current_stock' => 20,
            'is_active' => true,
        ]);

        $this->product2 = Product::create([
            'sku' => 'PRD-OUT-002',
            'name' => 'Pulpen Gel Hitam 0.5mm',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'minimum_stock' => 10,
            'current_stock' => 15,
            'is_active' => true,
        ]);
    }

    public function test_stock_out_create_page_renders_successfully(): void
    {
        Livewire::actingAs($this->admin)
            ->test(StockOutCreate::class)
            ->assertStatus(200)
            ->assertSee('Formulir Barang Keluar')
            ->assertSee('Rincian Barang yang Dikeluarkan')
            ->assertSee('Informasi Pengeluaran')
            ->assertSee('Simpan Transaksi Keluar');
    }

    public function test_can_add_and_remove_item_rows(): void
    {
        Livewire::actingAs($this->admin)
            ->test(StockOutCreate::class)
            ->assertCount('items', 1)
            ->call('addItem')
            ->assertCount('items', 2)
            ->call('addItem')
            ->assertCount('items', 3)
            ->call('removeItem', 1)
            ->assertCount('items', 2)
            ->call('removeItem', 0)
            ->assertCount('items', 1)
            ->call('removeItem', 0)
            ->assertCount('items', 1);
    }

    public function test_validates_required_fields(): void
    {
        Livewire::actingAs($this->admin)
            ->test(StockOutCreate::class)
            ->set('recipient', '')
            ->set('items.0.product_id', '')
            ->set('items.0.quantity', 0)
            ->call('save')
            ->assertHasErrors([
                'recipient' => 'required',
                'items.0.product_id' => 'required',
                'items.0.quantity' => 'min',
            ]);
    }

    public function test_prevents_duplicate_products_in_form(): void
    {
        Livewire::actingAs($this->admin)
            ->test(StockOutCreate::class)
            ->set('transaction_date', now()->toDateString())
            ->set('recipient', 'Bpk. Ahmad')
            ->set('items.0.product_id', $this->product1->id)
            ->set('items.0.quantity', 5)
            ->call('addItem')
            ->set('items.1.product_id', $this->product1->id)
            ->set('items.1.quantity', 3)
            ->call('save')
            ->assertHasErrors(['items']);
    }

    public function test_prevents_stock_out_when_insufficient_stock(): void
    {
        Livewire::actingAs($this->admin)
            ->test(StockOutCreate::class)
            ->set('transaction_date', now()->toDateString())
            ->set('recipient', 'Ibu Siti')
            ->set('items.0.product_id', $this->product1->id)
            ->set('items.0.quantity', 50) // current_stock is 20
            ->call('save')
            ->assertHasErrors(['items.0.quantity']);

        // Stock remains unchanged
        $this->assertEquals(20, $this->product1->fresh()->current_stock);
    }

    public function test_can_save_stock_out_transaction_successfully(): void
    {
        Livewire::actingAs($this->admin)
            ->test(StockOutCreate::class)
            ->set('transaction_date', now()->toDateString())
            ->set('recipient', 'Bpk. Budi (Pelanggan)')
            ->set('note', 'Pembelian ATK pribadi')
            ->set('items.0.product_id', $this->product1->id)
            ->set('items.0.quantity', 5)
            ->call('addItem')
            ->set('items.1.product_id', $this->product2->id)
            ->set('items.1.quantity', 10)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSessionHas('success');

        $this->assertEquals(15, $this->product1->fresh()->current_stock);
        $this->assertEquals(5, $this->product2->fresh()->current_stock);

        $this->assertDatabaseHas('stock_transactions', [
            'type' => 'OUT',
            'recipient' => 'Bpk. Budi (Pelanggan)',
            'note' => 'Pembelian ATK pribadi',
        ]);
    }

    public function test_stock_out_barcode_scanning_combined_modes(): void
    {
        $this->product1->update(['barcode' => '899333111222']);

        $component = Livewire::actingAs($this->admin)->test(StockOutCreate::class);

        // Scan once: item added with qty = 1
        $component->call('handleBarcodeScan', '899333111222');
        $this->assertEquals($this->product1->id, $component->get('items.0.product_id'));
        $this->assertEquals(1, $component->get('items.0.quantity'));

        // Scan again: qty increments to 2
        $component->call('handleBarcodeScan', '899333111222');
        $this->assertEquals(2, $component->get('items.0.quantity'));

        // Scan 3rd time: qty increments to 3
        $component->call('handleBarcodeScan', '899333111222');
        $this->assertEquals(3, $component->get('items.0.quantity'));

        // Manual edit works
        $component->set('items.0.quantity', 5);
        $this->assertEquals(5, $component->get('items.0.quantity'));
    }

    public function test_stock_out_rejects_unregistered_or_out_of_stock_barcode(): void
    {
        $zeroStockProduct = Product::create([
            'sku' => 'PRD-ZERO-01',
            'barcode' => '888000111222',
            'name' => 'Barang Kosong',
            'category_id' => $this->product1->category_id,
            'unit_id' => $this->product1->unit_id,
            'minimum_stock' => 5,
            'current_stock' => 0,
            'is_active' => true,
        ]);

        $component = Livewire::actingAs($this->admin)->test(StockOutCreate::class);

        // 1. Unregistered barcode
        $component->call('handleBarcodeScan', '999999999999');
        $component->assertDispatched('swal');
        $this->assertEmpty($component->get('items.0.product_id'));

        // 2. Barcode with 0 stock
        $component->call('handleBarcodeScan', '888000111222');
        $component->assertDispatched('swal');
        $this->assertEmpty($component->get('items.0.product_id'));
    }
}
