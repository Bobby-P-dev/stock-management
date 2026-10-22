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
use App\Services\StockService;
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

    public function test_category_index_listing_search_modal_toggle_and_delete_restrictions(): void
    {
        $unit = Unit::create(['name' => 'Pcs', 'symbol' => 'PCS', 'is_active' => true]);

        $linkedCategory = Category::create([
            'name' => 'Jaringan & Server',
            'description' => 'Perangkat jaringan utama',
            'is_active' => true,
        ]);

        $emptyCategory = Category::create([
            'name' => 'Kategori Sementara',
            'description' => 'Belum ada produk',
            'is_active' => false,
        ]);

        Product::create([
            'sku' => 'NET-001',
            'name' => 'Switch Core 48 Port',
            'category_id' => $linkedCategory->id,
            'unit_id' => $unit->id,
            'minimum_stock' => 2,
            'current_stock' => 5,
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)
            ->get(route('categories.index'))
            ->assertOk()
            ->assertSee('Kategori Barang')
            ->assertSee('Klasifikasi kelompok barang untuk mempermudah inventarisasi dan pelaporan.')
            ->assertSee('Tambah Kategori')
            ->assertSee('Jaringan & Server')
            ->assertSee('Kategori Sementara')
            ->assertSee('Aktif')
            ->assertSee('Non-aktif')
            ->assertSee('Edit')
            ->assertSee('Opsi lainnya');

        $component = Livewire::actingAs($this->admin)
            ->test(CategoryIndex::class)
            ->set('search', 'TIDAK-ADA-KATEGORI')
            ->assertSee('Tidak Ada Kategori yang Cocok')
            ->set('search', 'Jaringan')
            ->assertSee('Jaringan & Server')
            ->assertDontSee('Kategori Sementara')
            ->set('search', '');

        // Validation failure & clean state reset on closeModal
        $component
            ->call('create')
            ->assertSet('showModal', true)
            ->assertSee('Tambah Kategori Baru')
            ->assertSee('Simpan Kategori')
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name'])
            ->call('closeModal')
            ->assertSet('showModal', false)
            ->assertHasNoErrors();

        // Toggle active status
        $component
            ->call('toggleActive', $linkedCategory->id)
            ->assertDispatched('swal');

        $this->assertFalse($linkedCategory->fresh()->is_active);

        // Delete restriction for category linked to products
        $component
            ->call('delete', $linkedCategory->id)
            ->assertDispatched('swal');

        $this->assertDatabaseHas('categories', ['id' => $linkedCategory->id]);

        // Delete unused category succeeds
        $component
            ->call('delete', $emptyCategory->id)
            ->assertDispatched('swal');

        $this->assertDatabaseMissing('categories', ['id' => $emptyCategory->id]);
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

    public function test_unit_index_listing_search_modal_toggle_and_delete_restrictions(): void
    {
        $category = Category::create(['name' => 'Kabel', 'is_active' => true]);

        $linkedUnit = Unit::create([
            'name' => 'Roll Besar',
            'symbol' => 'RLL',
            'is_active' => true,
        ]);

        $emptyUnit = Unit::create([
            'name' => 'Kotak Kecil',
            'symbol' => 'KTK',
            'is_active' => false,
        ]);

        Product::create([
            'sku' => 'KBL-001',
            'name' => 'Kabel Fiber 100M',
            'category_id' => $category->id,
            'unit_id' => $linkedUnit->id,
            'minimum_stock' => 2,
            'current_stock' => 5,
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)
            ->get(route('units.index'))
            ->assertOk()
            ->assertSee('Satuan Barang')
            ->assertSee('Daftar satuan ukuran untuk kuantitas produk (PCS, BOX, UNIT, dll).')
            ->assertSee('Tambah Satuan')
            ->assertSee('Roll Besar')
            ->assertSee('RLL')
            ->assertSee('Kotak Kecil')
            ->assertSee('KTK')
            ->assertSee('Aktif')
            ->assertSee('Non-aktif')
            ->assertSee('Edit')
            ->assertSee('Opsi lainnya');

        $component = Livewire::actingAs($this->admin)
            ->test(UnitIndex::class)
            ->set('search', 'TIDAK-ADA-SATUAN')
            ->assertSee('Tidak Ada Satuan yang Cocok')
            ->set('search', 'RLL')
            ->assertSee('Roll Besar')
            ->assertDontSee('Kotak Kecil')
            ->set('search', '');

        // Validation failure & clean state reset on closeModal
        $component
            ->call('create')
            ->assertSet('showModal', true)
            ->assertSee('Tambah Satuan Baru')
            ->assertSee('Simpan Satuan')
            ->set('name', '')
            ->set('symbol', '')
            ->call('save')
            ->assertHasErrors(['name', 'symbol'])
            ->call('closeModal')
            ->assertSet('showModal', false)
            ->assertHasNoErrors();

        // Toggle active status
        $component
            ->call('toggleActive', $linkedUnit->id)
            ->assertDispatched('swal');

        $this->assertFalse($linkedUnit->fresh()->is_active);

        // Delete restriction for unit linked to products
        $component
            ->call('delete', $linkedUnit->id)
            ->assertDispatched('swal');

        $this->assertDatabaseHas('units', ['id' => $linkedUnit->id]);

        // Delete unused unit succeeds
        $component
            ->call('delete', $emptyUnit->id)
            ->assertDispatched('swal');

        $this->assertDatabaseMissing('units', ['id' => $emptyUnit->id]);
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

    public function test_supplier_index_listing_search_modal_toggle_and_delete_restrictions(): void
    {
        $category = Category::create(['name' => 'Perangkat', 'is_active' => true]);
        $unit = Unit::create(['name' => 'Unit', 'symbol' => 'UNT', 'is_active' => true]);

        $linkedSupplier = Supplier::create([
            'code' => 'SUP-LNK-01',
            'name' => 'PT Mitra Nusantara',
            'phone' => '081234567890',
            'email' => 'mitra@nusantara.test',
            'address' => 'Jl. Industri Raya No. 10, Jakarta',
            'is_active' => true,
        ]);

        $emptySupplier = Supplier::create([
            'code' => 'SUP-EMP-02',
            'name' => 'CV Sumber Makmur',
            'phone' => '089876543210',
            'is_active' => false,
        ]);

        $product = Product::create([
            'sku' => 'PRG-001',
            'name' => 'Modem Fiber',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'minimum_stock' => 5,
            'current_stock' => 10,
            'is_active' => true,
        ]);

        app(StockService::class)->recordStockIn(
            [
                'transaction_date' => now()->toDateString(),
                'supplier_id' => $linkedSupplier->id,
                'note' => 'Faktur perdana',
            ],
            [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'unit_price' => 100000,
                ],
            ],
            $this->admin->id
        );

        $this->actingAs($this->admin)
            ->get(route('suppliers.index'))
            ->assertOk()
            ->assertSee('Mitra Supplier')
            ->assertSee('Daftar vendor penyedia barang masuk (Stock In) dan kontak penanggung jawab.')
            ->assertSee('Tambah Supplier')
            ->assertSee('SUP-LNK-01')
            ->assertSee('PT Mitra Nusantara')
            ->assertSee('081234567890')
            ->assertSee('mitra@nusantara.test')
            ->assertSee('Jl. Industri Raya No. 10, Jakarta')
            ->assertSee('SUP-EMP-02')
            ->assertSee('CV Sumber Makmur')
            ->assertSee('Aktif')
            ->assertSee('Non-aktif')
            ->assertSee('Edit')
            ->assertSee('Opsi lainnya');

        $component = Livewire::actingAs($this->admin)
            ->test(SupplierIndex::class)
            ->set('search', 'TIDAK-ADA-SUPPLIER')
            ->assertSee('Tidak Ada Supplier yang Cocok')
            ->set('search', '081234567890')
            ->assertSee('PT Mitra Nusantara')
            ->assertDontSee('CV Sumber Makmur')
            ->set('search', '');

        // Validation failure & clean state reset on closeModal
        $component
            ->call('create')
            ->assertSet('showModal', true)
            ->assertSee('Tambah Supplier Baru')
            ->assertSee('Simpan Supplier')
            ->set('code', '')
            ->set('name', '')
            ->set('email', 'bukan-email-valid')
            ->call('save')
            ->assertHasErrors(['code', 'name', 'email'])
            ->call('closeModal')
            ->assertSet('showModal', false)
            ->assertHasNoErrors();

        // Toggle active status
        $component
            ->call('toggleActive', $linkedSupplier->id)
            ->assertDispatched('swal');

        $this->assertFalse($linkedSupplier->fresh()->is_active);

        // Delete restriction for supplier linked to Stock In transactions
        $component
            ->call('delete', $linkedSupplier->id)
            ->assertDispatched('swal');

        $this->assertDatabaseHas('suppliers', ['id' => $linkedSupplier->id]);

        // Delete unused supplier succeeds
        $component
            ->call('delete', $emptySupplier->id)
            ->assertDispatched('swal');

        $this->assertDatabaseMissing('suppliers', ['id' => $emptySupplier->id]);
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

    public function test_master_data_crud_and_delete_protection_dispatch_swal_events(): void
    {
        $category = Category::create(['name' => 'Kategori Uji', 'is_active' => true]);
        $unit = Unit::create(['name' => 'Satuan Uji', 'symbol' => 'SUJ', 'is_active' => true]);
        $product = Product::create([
            'sku' => 'SKU-SWAL-01',
            'name' => 'Barang Uji Swal',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'minimum_stock' => 2,
            'current_stock' => 0,
            'is_active' => true,
        ]);

        // Category used by product cannot be deleted
        Livewire::actingAs($this->admin)
            ->test(CategoryIndex::class)
            ->call('delete', $category->id)
            ->assertDispatched('swal');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);

        // Product without transactions can be deleted and dispatches swal
        Livewire::actingAs($this->admin)
            ->test(ProductIndex::class)
            ->call('delete', $product->id)
            ->assertDispatched('swal');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);

        // Now Category can be deleted and dispatches swal
        Livewire::actingAs($this->admin)
            ->test(CategoryIndex::class)
            ->call('delete', $category->id)
            ->assertDispatched('swal');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);

        // Unit can be deleted and dispatches swal
        Livewire::actingAs($this->admin)
            ->test(UnitIndex::class)
            ->call('delete', $unit->id)
            ->assertDispatched('swal');

        $this->assertDatabaseMissing('units', ['id' => $unit->id]);

        // Self user cannot be deleted
        Livewire::actingAs($this->admin)
            ->test(UserIndex::class)
            ->call('delete', $this->admin->id)
            ->assertDispatched('swal');

        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_user_index_ui_and_lifecycle_operations(): void
    {
        $staff = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@test.com',
            'password' => bcrypt('password123'),
            'role' => 'petugas',
            'is_active' => true,
        ]);

        // HTTP request test
        $this->actingAs($this->admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('Manajemen Pengguna')
            ->assertSee('Tambah Pengguna');

        // Livewire component test
        $component = Livewire::actingAs($this->admin)
            ->test(UserIndex::class)
            ->assertSee('Super Admin')
            ->assertSee('Budi Santoso')
            ->assertSee('Anda');

        // Search filtering
        $component->set('search', 'Budi')
            ->assertSee('Budi Santoso')
            ->assertDontSee('Super Admin');

        $component->set('search', '')
            ->assertSee('Super Admin')
            ->assertSee('Budi Santoso');

        // Modal create & closeModal
        $component->call('create')
            ->assertSet('showModal', true)
            ->assertSet('role', 'petugas')
            ->assertSet('is_active', true)
            ->assertSee('Simpan Pengguna')
            ->assertSee('Batal')
            ->call('closeModal')
            ->assertSet('showModal', false);

        // Validation on create
        $component->call('create')
            ->call('save')
            ->assertHasErrors(['name', 'email', 'password']);

        // Unique email validation
        $component->set('name', 'Duplikat User')
            ->set('email', 'superadmin@test.com')
            ->set('password', 'secret123')
            ->call('save')
            ->assertHasErrors(['email']);

        // Valid creation
        $component->set('name', 'Petugas Baru')
            ->set('email', 'petugas.baru@test.com')
            ->set('password', 'secret123')
            ->set('role', 'petugas')
            ->set('is_active', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false)
            ->assertDispatched('swal');

        $newUser = User::where('email', 'petugas.baru@test.com')->first();
        $this->assertNotNull($newUser);
        $this->assertSame('petugas', $newUser->role);

        // Edit user without changing password
        $component->call('edit', $newUser)
            ->assertSet('showModal', true)
            ->assertSet('editingUserId', $newUser->id)
            ->assertSet('name', 'Petugas Baru')
            ->assertSee('Simpan Perubahan')
            ->assertSee('Batal')
            ->set('name', 'Petugas Terupdate')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false)
            ->assertDispatched('swal');

        $this->assertDatabaseHas('users', [
            'id' => $newUser->id,
            'name' => 'Petugas Terupdate',
        ]);

        // Toggle active self protection
        $component->call('toggleActive', $this->admin)
            ->assertDispatched('swal');
        $this->assertTrue($this->admin->fresh()->is_active);

        // Toggle active other user
        $component->call('toggleActive', $newUser)
            ->assertDispatched('swal');
        $this->assertFalse($newUser->fresh()->is_active);

        // Delete self protection
        $component->call('delete', $this->admin->id)
            ->assertDispatched('swal');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);

        // Delete other user
        $component->call('delete', $newUser->id)
            ->assertDispatched('swal');
        $this->assertDatabaseMissing('users', ['id' => $newUser->id]);
    }
}
