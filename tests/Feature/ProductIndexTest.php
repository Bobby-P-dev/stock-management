<?php

namespace Tests\Feature;

use App\Livewire\Products\ProductIndex;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductIndexTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $petugas;

    private Category $categoryA;

    private Category $categoryB;

    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Inventaris',
            'email' => 'admin@inventaris.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->petugas = User::create([
            'name' => 'Petugas Inventaris',
            'email' => 'petugas@inventaris.test',
            'password' => bcrypt('password'),
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $this->categoryA = Category::create([
            'name' => 'Elektronik',
            'is_active' => true,
        ]);

        $this->categoryB = Category::create([
            'name' => 'Kabel & Jaringan',
            'is_active' => true,
        ]);

        $this->unit = Unit::create([
            'name' => 'Roll',
            'symbol' => 'ROLL',
            'is_active' => true,
        ]);
    }

    public function test_product_index_renders_table_statuses_and_role_based_actions(): void
    {
        $safeProduct = Product::create([
            'sku' => 'PRD-SAFE-01',
            'name' => 'Kabel UTP Cat6 305M',
            'category_id' => $this->categoryB->id,
            'unit_id' => $this->unit->id,
            'minimum_stock' => 5,
            'current_stock' => 20,
            'is_active' => true,
        ]);

        $lowProduct = Product::create([
            'sku' => 'PRD-LOW-02',
            'name' => 'Switch Gigabit 24 Port',
            'category_id' => $this->categoryA->id,
            'unit_id' => $this->unit->id,
            'minimum_stock' => 5,
            'current_stock' => 3,
            'is_active' => false,
        ]);

        $outProduct = Product::create([
            'sku' => 'PRD-OUT-03',
            'name' => 'Patch Cord 2M',
            'category_id' => $this->categoryB->id,
            'unit_id' => $this->unit->id,
            'minimum_stock' => 10,
            'current_stock' => 0,
            'is_active' => true,
        ]);

        $adminResponse = $this->actingAs($this->admin)->get(route('products.index'));

        $adminResponse->assertOk()
            ->assertSee('Katalog Master Barang')
            ->assertSee('Tambah Barang Baru')
            ->assertSee($safeProduct->sku)
            ->assertSee($lowProduct->sku)
            ->assertSee($outProduct->sku)
            ->assertSee('Aman')
            ->assertSee('Menipis')
            ->assertSee('Habis')
            ->assertSee('Non-aktif')
            ->assertSee(route('stock-card', ['productId' => $safeProduct->id]))
            ->assertSee('Edit')
            ->assertSee('Opsi lainnya');

        $petugasResponse = $this->actingAs($this->petugas)->get(route('products.index'));

        $petugasResponse->assertOk()
            ->assertDontSee('Tambah Barang Baru')
            ->assertSee(route('stock-card', ['productId' => $safeProduct->id]))
            ->assertDontSee('Opsi lainnya');
    }

    public function test_product_index_search_filters_and_row_actions_work(): void
    {
        $product = Product::create([
            'sku' => 'PRD-ACT-01',
            'name' => 'Router Enterprise',
            'category_id' => $this->categoryA->id,
            'unit_id' => $this->unit->id,
            'minimum_stock' => 5,
            'current_stock' => 10,
            'is_active' => true,
        ]);

        Livewire::actingAs($this->admin)
            ->test(ProductIndex::class)
            ->set('search', 'TIDAK-ADA-BARANG')
            ->assertSee('Tidak Ada Barang yang Cocok')
            ->set('search', 'PRD-ACT-01')
            ->assertSee('Router Enterprise')
            ->call('toggleActive', $product->id)
            ->assertDispatched('swal');

        $this->assertFalse($product->fresh()->is_active);
    }

    public function test_add_and_edit_product_modal_lifecycle_validation_and_state_reset(): void
    {
        $existingProduct = Product::create([
            'sku' => 'PRD-EXIST-01',
            'name' => 'Switch Manageable 16 Port',
            'category_id' => $this->categoryA->id,
            'unit_id' => $this->unit->id,
            'minimum_stock' => 8,
            'current_stock' => 12,
            'is_active' => false,
        ]);

        // 1. Open Tambah Barang Baru modal & verify header, description, defaults, and footer buttons
        $component = Livewire::actingAs($this->admin)
            ->test(ProductIndex::class)
            ->assertSet('showModal', false)
            ->call('create')
            ->assertSet('showModal', true)
            ->assertSet('editingProductId', null)
            ->assertSet('minimum_stock', 5)
            ->assertSet('is_active', true)
            ->assertSee('Tambah Barang Baru')
            ->assertSee('Lengkapi informasi barang untuk pengelolaan inventaris.')
            ->assertSee('Simpan Barang')
            ->assertSee('Batal');

        // 2. Submit invalid fields, verify inline validation errors & preserved user input
        $component
            ->set('sku', '')
            ->set('name', 'Kabel Fiber Optik Sementara')
            ->set('category_id', null)
            ->set('unit_id', null)
            ->set('minimum_stock', -1)
            ->call('save')
            ->assertHasErrors(['sku', 'category_id', 'unit_id', 'minimum_stock'])
            ->assertSet('showModal', true)
            ->assertSet('name', 'Kabel Fiber Optik Sementara')
            ->assertSee('aria-invalid="true"', false);

        // 3. Close modal via closeModal action and reopen without stale state or errors
        $component
            ->call('closeModal')
            ->assertSet('showModal', false)
            ->assertHasNoErrors()
            ->call('create')
            ->assertSet('showModal', true)
            ->assertSet('sku', '')
            ->assertSet('name', '')
            ->assertSet('minimum_stock', 5)
            ->assertSet('is_active', true)
            ->assertHasNoErrors();

        // 4. Create a valid product
        $component
            ->set('sku', 'PRD-NEW-100')
            ->set('name', 'Access Point Wi-Fi 6')
            ->set('category_id', $this->categoryB->id)
            ->set('unit_id', $this->unit->id)
            ->set('minimum_stock', 10)
            ->set('is_active', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false)
            ->assertDispatched('swal');

        $this->assertDatabaseHas('products', [
            'sku' => 'PRD-NEW-100',
            'name' => 'Access Point Wi-Fi 6',
            'category_id' => $this->categoryB->id,
            'unit_id' => $this->unit->id,
            'minimum_stock' => 10,
            'current_stock' => 0,
            'is_active' => true,
        ]);

        // 5. Open Edit modal for existing product, verify prefilled values and Edit Barang UI
        $component
            ->call('edit', $existingProduct->id)
            ->assertSet('showModal', true)
            ->assertSet('editingProductId', $existingProduct->id)
            ->assertSet('sku', 'PRD-EXIST-01')
            ->assertSet('name', 'Switch Manageable 16 Port')
            ->assertSet('category_id', $this->categoryA->id)
            ->assertSet('unit_id', $this->unit->id)
            ->assertSet('minimum_stock', 8)
            ->assertSet('is_active', false)
            ->assertSee('Edit Barang')
            ->assertSee('Lengkapi informasi barang untuk pengelolaan inventaris.')
            ->assertSee('Simpan Perubahan')
            ->assertSee('Batal');

        // 6. Update and save product
        $component
            ->set('name', 'Switch Manageable 16 Port PoE')
            ->set('category_id', $this->categoryB->id)
            ->set('minimum_stock', 12)
            ->set('is_active', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false)
            ->assertDispatched('swal');

        $this->assertDatabaseHas('products', [
            'id' => $existingProduct->id,
            'sku' => 'PRD-EXIST-01',
            'name' => 'Switch Manageable 16 Port PoE',
            'category_id' => $this->categoryB->id,
            'minimum_stock' => 12,
            'is_active' => true,
        ]);
    }
}
