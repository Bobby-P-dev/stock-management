<?php

namespace Tests\Feature;

use App\Livewire\Transactions\TransactionIndex;
use App\Livewire\Transactions\TransactionShow;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\StockTransactionItem;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Supplier $supplier;

    private StockTransaction $transIn;

    private StockTransaction $transOut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Mutasi',
            'email' => 'admin_mutasi@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Elektronik',
            'is_active' => true,
        ]);

        $unit = Unit::create([
            'name' => 'Unit',
            'symbol' => 'UNIT',
            'is_active' => true,
        ]);

        $this->supplier = Supplier::create([
            'code' => 'SUP-001',
            'name' => 'PT Sumber Rejeki',
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku' => 'PRD-EL-001',
            'name' => 'Monitor LED 24 Inch',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'minimum_stock' => 5,
            'current_stock' => 10,
            'is_active' => true,
        ]);

        $this->transIn = StockTransaction::create([
            'transaction_no' => 'SIN-20261022-0001',
            'type' => 'IN',
            'transaction_date' => '2026-10-20',
            'supplier_id' => $this->supplier->id,
            'recipient' => null,
            'note' => 'Penerimaan batch 1',
            'created_by' => $this->admin->id,
        ]);

        StockTransactionItem::create([
            'stock_transaction_id' => $this->transIn->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 1500000,
        ]);

        $this->transOut = StockTransaction::create([
            'transaction_no' => 'SOUT-20261022-0002',
            'type' => 'OUT',
            'transaction_date' => '2026-10-22',
            'supplier_id' => null,
            'recipient' => 'Bpk. Ahmad (Pelanggan)',
            'note' => 'Penjualan toko',
            'created_by' => $this->admin->id,
        ]);

        StockTransactionItem::create([
            'stock_transaction_id' => $this->transOut->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 0,
        ]);
    }

    public function test_transaction_index_page_renders_successfully(): void
    {
        Livewire::actingAs($this->admin)
            ->test(TransactionIndex::class)
            ->assertStatus(200)
            ->assertSee('Riwayat Mutasi Stok')
            ->assertSee('Barang Masuk')
            ->assertSee('Barang Keluar')
            ->assertSee('SIN-20261022-0001')
            ->assertSee('SOUT-20261022-0002')
            ->assertSee('PT Sumber Rejeki')
            ->assertSee('Bpk. Ahmad (Pelanggan)');
    }

    public function test_can_filter_transactions_by_search(): void
    {
        Livewire::actingAs($this->admin)
            ->test(TransactionIndex::class)
            ->set('search', 'Ahmad')
            ->assertSee('SOUT-20261022-0002')
            ->assertDontSee('SIN-20261022-0001');

        Livewire::actingAs($this->admin)
            ->test(TransactionIndex::class)
            ->set('search', 'Rejeki')
            ->assertSee('SIN-20261022-0001')
            ->assertDontSee('SOUT-20261022-0002');
    }

    public function test_can_filter_transactions_by_type(): void
    {
        Livewire::actingAs($this->admin)
            ->test(TransactionIndex::class)
            ->set('typeFilter', 'IN')
            ->assertSee('SIN-20261022-0001')
            ->assertDontSee('SOUT-20261022-0002');

        Livewire::actingAs($this->admin)
            ->test(TransactionIndex::class)
            ->set('typeFilter', 'OUT')
            ->assertSee('SOUT-20261022-0002')
            ->assertDontSee('SIN-20261022-0001');
    }

    public function test_can_filter_transactions_by_date_range(): void
    {
        Livewire::actingAs($this->admin)
            ->test(TransactionIndex::class)
            ->set('startDate', '2026-10-21')
            ->assertSee('SOUT-20261022-0002')
            ->assertDontSee('SIN-20261022-0001');
    }

    public function test_can_reset_filters(): void
    {
        Livewire::actingAs($this->admin)
            ->test(TransactionIndex::class)
            ->set('search', 'Ahmad')
            ->set('typeFilter', 'OUT')
            ->set('startDate', '2026-10-20')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('typeFilter', '')
            ->assertSet('startDate', '')
            ->assertSee('SIN-20261022-0001')
            ->assertSee('SOUT-20261022-0002');
    }

    public function test_transaction_show_page_renders_successfully(): void
    {
        Livewire::actingAs($this->admin)
            ->test(TransactionShow::class, ['transaction' => $this->transIn])
            ->assertStatus(200)
            ->assertSee('Detail Dokumen Mutasi')
            ->assertSee('SIN-20261022-0001')
            ->assertSee('PT Sumber Rejeki')
            ->assertSee('Monitor LED 24 Inch')
            ->assertSee('Barang Masuk (Stock In)');

        Livewire::actingAs($this->admin)
            ->test(TransactionShow::class, ['transaction' => $this->transOut])
            ->assertStatus(200)
            ->assertSee('Detail Dokumen Mutasi')
            ->assertSee('SOUT-20261022-0002')
            ->assertSee('Bpk. Ahmad (Pelanggan)')
            ->assertSee('Monitor LED 24 Inch')
            ->assertSee('Barang Keluar (Stock Out)');
    }
}
