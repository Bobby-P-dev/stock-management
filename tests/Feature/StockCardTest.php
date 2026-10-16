<?php

namespace Tests\Feature;

use App\Livewire\StockCard;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
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
}
