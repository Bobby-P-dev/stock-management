<?php

namespace Tests\Feature;

use App\Livewire\StockCard;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockCardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Category $category;

    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Stock Card',
            'email' => 'admin_stockcard@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'name' => 'Makanan Ringan',
            'is_active' => true,
        ]);

        $this->unit = Unit::create([
            'name' => 'Bungkus',
            'symbol' => 'BKS',
            'is_active' => true,
        ]);
    }

    public function test_stock_card_limits_initial_dropdown_products_to_five(): void
    {
        for ($i = 1; $i <= 8; $i++) {
            Product::create([
                'sku' => "SKU-00{$i}",
                'name' => "Produk Barang {$i}",
                'category_id' => $this->category->id,
                'unit_id' => $this->unit->id,
                'minimum_stock' => 5,
                'current_stock' => 20,
                'is_active' => true,
            ]);
        }

        Livewire::actingAs($this->admin)
            ->test(StockCard::class)
            ->assertStatus(200)
            ->assertViewHas('products', function ($products) {
                return $products->count() === 5;
            });
    }

    public function test_stock_card_search_filters_and_limits_products_to_five(): void
    {
        for ($i = 1; $i <= 7; $i++) {
            Product::create([
                'sku' => "BS-MKN-00{$i}",
                'name' => "Biskuit Cokelat Rasa {$i}",
                'category_id' => $this->category->id,
                'unit_id' => $this->unit->id,
                'minimum_stock' => 5,
                'current_stock' => 10,
                'is_active' => true,
            ]);
        }

        Product::create([
            'sku' => 'MN-KOP-001',
            'name' => 'Kopi Hitam Bubuk',
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'minimum_stock' => 5,
            'current_stock' => 10,
            'is_active' => true,
        ]);

        Livewire::actingAs($this->admin)
            ->test(StockCard::class)
            ->set('productSearch', 'Biskuit')
            ->assertStatus(200)
            ->assertViewHas('products', function ($products) {
                $hasOnlyMatching = $products->every(fn ($p) => str_contains($p->name, 'Biskuit'));

                return $products->count() === 5 && $hasOnlyMatching;
            })
            ->assertSee('Biskuit Cokelat Rasa 1')
            ->assertDontSee('Kopi Hitam Bubuk');
    }

    public function test_stock_card_select_product_updates_selected_id_and_clears_search(): void
    {
        $productA = Product::create([
            'sku' => 'SKU-AAA',
            'name' => 'Barang Alpha',
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'minimum_stock' => 5,
            'current_stock' => 15,
            'is_active' => true,
        ]);

        $productB = Product::create([
            'sku' => 'SKU-BBB',
            'name' => 'Barang Beta',
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'minimum_stock' => 5,
            'current_stock' => 25,
            'is_active' => true,
        ]);

        Livewire::actingAs($this->admin)
            ->test(StockCard::class)
            ->set('productSearch', 'Beta')
            ->call('selectProduct', $productB->id)
            ->assertSet('productId', $productB->id)
            ->assertSet('productSearch', '')
            ->assertViewHas('selectedProduct', function ($selected) use ($productB) {
                return $selected?->id === $productB->id;
            })
            ->assertSee('Barang Beta')
            ->assertSee('SKU-BBB');
    }

    public function test_stock_card_renders_accurate_ledger_totals_date_filter_and_print_layout(): void
    {
        $supplier = Supplier::create([
            'code' => 'SUP-SC-01',
            'name' => 'PT Distribusi Utama',
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku' => 'SKU-LEDGER-01',
            'name' => 'Kabel Coaxial RG6 300M',
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'minimum_stock' => 10,
            'current_stock' => 0,
            'is_active' => true,
        ]);

        $stockService = app(StockService::class);

        // Prior transaction before filter window (2026-10-01): +30
        $stockService->recordStockIn(
            [
                'transaction_date' => '2026-10-01',
                'supplier_id' => $supplier->id,
                'note' => 'Stok awal bulan Oktober',
            ],
            [
                [
                    'product_id' => $product->id,
                    'quantity' => 30,
                    'unit_price' => 10000,
                ],
            ],
            $this->admin->id
        );

        // Transaction inside filter window (2026-10-10): +15
        $txIn = $stockService->recordStockIn(
            [
                'transaction_date' => '2026-10-10',
                'supplier_id' => $supplier->id,
                'note' => 'Pengadaan gudang pusat tahap kedua dengan keterangan lengkap tanpa terpotong',
            ],
            [
                [
                    'product_id' => $product->id,
                    'quantity' => 15,
                    'unit_price' => 10000,
                ],
            ],
            $this->admin->id
        );

        // Transaction inside filter window (2026-10-15): -8
        $txOut = $stockService->recordStockOut(
            [
                'transaction_date' => '2026-10-15',
                'recipient' => 'Divisi Proyek Lapangan',
                'note' => 'Distribusi proyek instalasi gedung',
            ],
            [
                [
                    'product_id' => $product->id,
                    'quantity' => 8,
                ],
            ],
            $this->admin->id
        );

        Livewire::actingAs($this->admin)
            ->test(StockCard::class, ['productId' => $product->id])
            ->assertStatus(200)
            ->assertSee('Kartu Stok (Stock Card)')
            ->assertSee('Riwayat pergerakan dan saldo persediaan barang.')
            ->assertSee('Export Excel')
            ->assertSee('Cetak Kartu Stok')
            ->assertSee('KARTU STOK BARANG')
            ->assertSee('SKU-LEDGER-01')
            ->assertSee('Kabel Coaxial RG6 300M')
            ->assertSee('+45')
            ->assertSee('-8')
            ->assertSee('37')
            ->set('startDate', '2026-10-05')
            ->set('endDate', '2026-10-20')
            ->assertSee('Saldo Awal Sebelum 05-10-2026')
            ->assertSee('05/10/2026 s/d 20/10/2026')
            ->assertSee($txIn->transaction_no)
            ->assertSee($txOut->transaction_no)
            ->assertSee('Pengadaan gudang pusat tahap kedua dengan keterangan lengkap tanpa terpotong')
            ->assertViewHas('stockCardData', function (array $data) {
                return $data['initial_balance'] === 30
                    && $data['total_in'] === 15
                    && $data['total_out'] === 8
                    && $data['final_balance'] === 37
                    && count($data['ledger']) === 2
                    && $data['ledger'][0]['balance'] === 45
                    && $data['ledger'][1]['balance'] === 37;
            });
    }
}
