<?php

namespace Tests\Feature;

use App\Livewire\Transactions\StockInCreate;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockInTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Supplier $supplier;

    private Product $product1;

    private Product $product2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Stock In',
            'email' => 'admin_in@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Bahan Baku',
            'is_active' => true,
        ]);

        $unit = Unit::create([
            'name' => 'Pcs',
            'symbol' => 'PCS',
            'is_active' => true,
        ]);

        $this->supplier = Supplier::create([
            'code' => 'SUP-IN-001',
            'name' => 'PT Makmur Sentosa',
            'is_active' => true,
        ]);

        $this->product1 = Product::create([
            'sku' => 'PRD-IN-001',
            'name' => 'Beras Pandan Wangi 5kg',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'minimum_stock' => 5,
            'current_stock' => 10,
            'is_active' => true,
        ]);

        $this->product2 = Product::create([
            'sku' => 'PRD-IN-002',
            'name' => 'Minyak Goreng 2L',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'minimum_stock' => 5,
            'current_stock' => 15,
            'is_active' => true,
        ]);
    }

    public function test_stock_in_create_page_renders_successfully(): void
    {
        Livewire::actingAs($this->admin)
            ->test(StockInCreate::class)
            ->assertStatus(200)
            ->assertSee('Formulir Barang Masuk')
            ->assertSee('Rincian Barang yang Diterima')
            ->assertSee('Informasi Transaksi')
            ->assertSee('Simpan Transaksi Masuk');
    }

    public function test_can_add_and_remove_item_rows(): void
    {
        Livewire::actingAs($this->admin)
            ->test(StockInCreate::class)
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
            ->test(StockInCreate::class)
            ->set('supplier_id', null)
            ->set('items.0.product_id', '')
            ->set('items.0.quantity', 0)
            ->call('save')
            ->assertHasErrors([
                'supplier_id' => 'required',
                'items.0.product_id' => 'required',
                'items.0.quantity' => 'min',
            ]);
    }

    public function test_prevents_duplicate_products_in_form(): void
    {
        Livewire::actingAs($this->admin)
            ->test(StockInCreate::class)
            ->set('transaction_date', now()->toDateString())
            ->set('supplier_id', $this->supplier->id)
            ->set('items.0.product_id', $this->product1->id)
            ->set('items.0.quantity', 5)
            ->call('addItem')
            ->set('items.1.product_id', $this->product1->id)
            ->set('items.1.quantity', 10)
            ->call('save')
            ->assertHasErrors(['items']);
    }

    public function test_can_save_stock_in_transaction_successfully(): void
    {
        Livewire::actingAs($this->admin)
            ->test(StockInCreate::class)
            ->set('transaction_date', now()->toDateString())
            ->set('supplier_id', $this->supplier->id)
            ->set('note', 'Penerimaan surat jalan #SJ-8899')
            ->set('items.0.product_id', $this->product1->id)
            ->set('items.0.quantity', 20)
            ->set('items.0.unit_price', 65000)
            ->call('addItem')
            ->set('items.1.product_id', $this->product2->id)
            ->set('items.1.quantity', 30)
            ->set('items.1.unit_price', 32000)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSessionHas('success');

        $this->assertEquals(30, $this->product1->fresh()->current_stock);
        $this->assertEquals(45, $this->product2->fresh()->current_stock);

        $this->assertDatabaseHas('stock_transactions', [
            'type' => 'IN',
            'supplier_id' => $this->supplier->id,
            'note' => 'Penerimaan surat jalan #SJ-8899',
        ]);
    }
}
