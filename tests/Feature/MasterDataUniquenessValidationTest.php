<?php

namespace Tests\Feature;

use App\Livewire\Categories\CategoryIndex;
use App\Livewire\Products\ProductIndex;
use App\Livewire\Suppliers\SupplierIndex;
use App\Livewire\Units\UnitIndex;
use App\Livewire\Users\UserIndex;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class MasterDataUniquenessValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_category_uniqueness_validation_cases(): void
    {
        $existing = Category::create([
            'name' => 'Elektronik',
            'description' => 'Barang elektronik',
            'is_active' => true,
        ]);

        $second = Category::create([
            'name' => 'Furnitur',
            'description' => 'Perabotan kantor',
            'is_active' => true,
        ]);

        Livewire::actingAs($this->admin)
            ->test(CategoryIndex::class)
            ->call('create')
            ->set('name', 'Peralatan Medis')
            ->set('description', 'Alat kesehatan')
            ->call('save')
            ->assertHasNoErrors(['name']);

        $this->assertDatabaseHas('categories', ['name' => 'Peralatan Medis']);

        Livewire::actingAs($this->admin)
            ->test(CategoryIndex::class)
            ->call('create')
            ->set('name', 'Elektronik')
            ->call('save')
            ->assertHasErrors(['name' => 'unique']);

        Livewire::actingAs($this->admin)
            ->test(CategoryIndex::class)
            ->call('edit', $existing)
            ->set('description', 'Deskripsi diperbarui')
            ->call('save')
            ->assertHasNoErrors(['name']);

        $this->assertDatabaseHas('categories', [
            'id' => $existing->id,
            'name' => 'Elektronik',
            'description' => 'Deskripsi diperbarui',
        ]);

        Livewire::actingAs($this->admin)
            ->test(CategoryIndex::class)
            ->call('edit', $second)
            ->set('name', 'Elektronik')
            ->call('save')
            ->assertHasErrors(['name' => 'unique']);
    }

    public function test_product_uniqueness_validation_cases(): void
    {
        $category = Category::create(['name' => 'Komputer', 'is_active' => true]);
        $unit = Unit::create(['name' => 'Unit', 'symbol' => 'UNT', 'is_active' => true]);

        $existing = Product::create([
            'sku' => 'SKU-001',
            'name' => 'Laptop Asus',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'minimum_stock' => 2,
            'current_stock' => 5,
            'is_active' => true,
        ]);

        $second = Product::create([
            'sku' => 'SKU-002',
            'name' => 'Laptop Lenovo',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'minimum_stock' => 2,
            'current_stock' => 5,
            'is_active' => true,
        ]);

        Livewire::actingAs($this->admin)
            ->test(ProductIndex::class)
            ->call('create')
            ->set('sku', 'SKU-003')
            ->set('name', 'Laptop Dell')
            ->set('category_id', $category->id)
            ->set('unit_id', $unit->id)
            ->set('minimum_stock', 1)
            ->call('save')
            ->assertHasNoErrors(['sku']);

        $this->assertDatabaseHas('products', ['sku' => 'SKU-003']);

        Livewire::actingAs($this->admin)
            ->test(ProductIndex::class)
            ->call('create')
            ->set('sku', 'SKU-001')
            ->set('name', 'Laptop Tiruan')
            ->set('category_id', $category->id)
            ->set('unit_id', $unit->id)
            ->call('save')
            ->assertHasErrors(['sku' => 'unique']);

        Livewire::actingAs($this->admin)
            ->test(ProductIndex::class)
            ->call('edit', $existing)
            ->set('name', 'Laptop Asus ROG Updated')
            ->call('save')
            ->assertHasNoErrors(['sku']);

        $this->assertDatabaseHas('products', [
            'id' => $existing->id,
            'sku' => 'SKU-001',
            'name' => 'Laptop Asus ROG Updated',
        ]);

        Livewire::actingAs($this->admin)
            ->test(ProductIndex::class)
            ->call('edit', $second)
            ->set('sku', 'SKU-001')
            ->call('save')
            ->assertHasErrors(['sku' => 'unique']);
    }

    public function test_unit_uniqueness_validation_cases(): void
    {
        $existing = Unit::create(['name' => 'Kilogram', 'symbol' => 'KG', 'is_active' => true]);
        $second = Unit::create(['name' => 'Gram', 'symbol' => 'GR', 'is_active' => true]);

        Livewire::actingAs($this->admin)
            ->test(UnitIndex::class)
            ->call('create')
            ->set('name', 'Liter')
            ->set('symbol', 'LTR')
            ->call('save')
            ->assertHasNoErrors(['name', 'symbol']);

        $this->assertDatabaseHas('units', ['name' => 'Liter', 'symbol' => 'LTR']);

        Livewire::actingAs($this->admin)
            ->test(UnitIndex::class)
            ->call('create')
            ->set('name', 'Kilogram')
            ->set('symbol', 'KLO')
            ->call('save')
            ->assertHasErrors(['name' => 'unique']);

        Livewire::actingAs($this->admin)
            ->test(UnitIndex::class)
            ->call('create')
            ->set('name', 'Kilogram Baru')
            ->set('symbol', 'KG')
            ->call('save')
            ->assertHasErrors(['symbol' => 'unique']);

        Livewire::actingAs($this->admin)
            ->test(UnitIndex::class)
            ->call('edit', $existing)
            ->set('is_active', false)
            ->call('save')
            ->assertHasNoErrors(['name', 'symbol']);

        $this->assertDatabaseHas('units', [
            'id' => $existing->id,
            'name' => 'Kilogram',
            'symbol' => 'KG',
            'is_active' => false,
        ]);

        Livewire::actingAs($this->admin)
            ->test(UnitIndex::class)
            ->call('edit', $second)
            ->set('name', 'Kilogram')
            ->call('save')
            ->assertHasErrors(['name' => 'unique']);

        Livewire::actingAs($this->admin)
            ->test(UnitIndex::class)
            ->call('edit', $second)
            ->set('symbol', 'KG')
            ->call('save')
            ->assertHasErrors(['symbol' => 'unique']);
    }

    public function test_supplier_uniqueness_validation_cases(): void
    {
        $existing = Supplier::create([
            'code' => 'SUP-001',
            'name' => 'Supplier Alpha',
            'is_active' => true,
        ]);

        $second = Supplier::create([
            'code' => 'SUP-002',
            'name' => 'Supplier Beta',
            'is_active' => true,
        ]);

        Livewire::actingAs($this->admin)
            ->test(SupplierIndex::class)
            ->call('create')
            ->set('code', 'SUP-003')
            ->set('name', 'Supplier Gamma')
            ->call('save')
            ->assertHasNoErrors(['code']);

        $this->assertDatabaseHas('suppliers', ['code' => 'SUP-003']);

        Livewire::actingAs($this->admin)
            ->test(SupplierIndex::class)
            ->call('create')
            ->set('code', 'SUP-001')
            ->set('name', 'Supplier Duplikat')
            ->call('save')
            ->assertHasErrors(['code' => 'unique']);

        Livewire::actingAs($this->admin)
            ->test(SupplierIndex::class)
            ->call('edit', $existing)
            ->set('name', 'Supplier Alpha Perkasa')
            ->call('save')
            ->assertHasNoErrors(['code']);

        $this->assertDatabaseHas('suppliers', [
            'id' => $existing->id,
            'code' => 'SUP-001',
            'name' => 'Supplier Alpha Perkasa',
        ]);

        Livewire::actingAs($this->admin)
            ->test(SupplierIndex::class)
            ->call('edit', $second)
            ->set('code', 'SUP-001')
            ->call('save')
            ->assertHasErrors(['code' => 'unique']);
    }

    public function test_user_uniqueness_validation_cases(): void
    {
        $existing = User::create([
            'name' => 'User Alpha',
            'email' => 'alpha@test.com',
            'password' => bcrypt('password'),
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $second = User::create([
            'name' => 'User Beta',
            'email' => 'beta@test.com',
            'password' => bcrypt('password'),
            'role' => 'petugas',
            'is_active' => true,
        ]);

        Livewire::actingAs($this->admin)
            ->test(UserIndex::class)
            ->call('create')
            ->set('name', 'User Gamma')
            ->set('email', 'gamma@test.com')
            ->set('password', 'secret123')
            ->set('role', 'petugas')
            ->call('save')
            ->assertHasNoErrors(['email']);

        $this->assertDatabaseHas('users', ['email' => 'gamma@test.com']);

        Livewire::actingAs($this->admin)
            ->test(UserIndex::class)
            ->call('create')
            ->set('name', 'User Duplikat')
            ->set('email', 'alpha@test.com')
            ->set('password', 'secret123')
            ->call('save')
            ->assertHasErrors(['email' => 'unique']);

        Livewire::actingAs($this->admin)
            ->test(UserIndex::class)
            ->call('edit', $existing)
            ->set('name', 'User Alpha Updated')
            ->call('save')
            ->assertHasNoErrors(['email']);

        $this->assertDatabaseHas('users', [
            'id' => $existing->id,
            'email' => 'alpha@test.com',
            'name' => 'User Alpha Updated',
        ]);

        Livewire::actingAs($this->admin)
            ->test(UserIndex::class)
            ->call('edit', $second)
            ->set('email', 'alpha@test.com')
            ->call('save')
            ->assertHasErrors(['email' => 'unique']);
    }

    public function test_create_validation_queries_do_not_contain_empty_string_bindings_for_id(): void
    {
        DB::enableQueryLog();
        DB::flushQueryLog();

        Livewire::actingAs($this->admin)
            ->test(CategoryIndex::class)
            ->call('create')
            ->set('name', 'Unik Test Category')
            ->call('save');

        $queries = DB::getQueryLog();
        foreach ($queries as $q) {
            if (str_contains($q['query'], 'categories') && str_contains($q['query'], 'count')) {
                $this->assertStringNotContainsString('and `id` <> ?', $q['query']);
                $this->assertStringNotContainsString('and "id" <> $2', $q['query']);
                $this->assertNotContains('', $q['bindings']);
            }
        }
    }
}
