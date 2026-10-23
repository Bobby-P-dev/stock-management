<?php

namespace Tests\Feature;

use App\Livewire\Opnames\StockOpnameCreate;
use App\Livewire\Scanner\RemoteScanner;
use App\Livewire\Transactions\StockInCreate;
use App\Livewire\Transactions\StockOutCreate;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Services\ScannerPairingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RemoteScannerPairingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Supplier $supplier;

    private Product $product1;

    private ScannerPairingService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Bpk. Ahmad Admin',
            'email' => 'ahmad@stock.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Konsumsi & Sembako',
            'is_active' => true,
        ]);

        $unit = Unit::create([
            'name' => 'Bungkus',
            'symbol' => 'BKS',
            'is_active' => true,
        ]);

        $this->supplier = Supplier::create([
            'code' => 'SUP-REMOTE-01',
            'name' => 'Distributor Utama',
            'is_active' => true,
        ]);

        $this->product1 = Product::create([
            'sku' => 'PRD-MIE-01',
            'barcode' => '8991234567890',
            'name' => 'Mie Goreng Spesial',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'minimum_stock' => 10,
            'current_stock' => 25,
            'is_active' => true,
        ]);

        $this->service = app(ScannerPairingService::class);
    }

    public function test_pairing_service_lifecycle(): void
    {
        // 1. Create session
        $session = $this->service->createSession('IN', $this->admin);
        $token = $session['token'];
        $this->assertNotEmpty($token);
        $this->assertEquals('IN', $session['type']);
        $this->assertEquals('waiting', $session['status']);

        // 2. Connect device
        $connected = $this->service->connectDevice($token, 'Samsung Galaxy S24');
        $this->assertTrue($connected);
        $sessionUpdated = $this->service->getSession($token);
        $this->assertEquals('connected', $sessionUpdated['status']);
        $this->assertEquals('Samsung Galaxy S24', $sessionUpdated['device_name']);

        // 3. Push scanned barcodes
        $res = $this->service->pushScannedBarcode($token, '8991234567890');
        $this->assertTrue($res['success']);
        $this->assertTrue($res['product_found']);
        $this->assertEquals('Mie Goreng Spesial', $res['product_name']);

        // 4. Poll scanned barcodes for laptop
        $polled = $this->service->pollScannedBarcodes($token);
        $this->assertCount(1, $polled);
        $this->assertEquals('8991234567890', $polled[0]['code']);

        // Subsequent poll is empty (queue drained)
        $this->assertEmpty($this->service->pollScannedBarcodes($token));

        // 5. Close session
        $this->service->closeSession($token);
        $this->assertNull($this->service->getSession($token));
    }

    public function test_remote_scanner_component_mounting_and_processing(): void
    {
        // Invalid token returns invalid state
        Livewire::test(RemoteScanner::class, ['token' => 'BSC-INVALID'])
            ->assertSet('isValid', false)
            ->assertSee('Sesi Scanner Tidak Ditemukan');

        // Valid session connects automatically
        $session = $this->service->createSession('IN', $this->admin);
        $token = $session['token'];

        $component = Livewire::test(RemoteScanner::class, ['token' => $token])
            ->assertSet('isValid', true)
            ->assertSee('Terhubung ke Laptop')
            ->assertSee('Bpk. Ahmad Admin');

        // Process a barcode
        $component->call('processBarcode', '8991234567890')
            ->assertSet('scanCount', 1)
            ->assertDispatched('swal');

        // Verify barcode is in queue
        $queue = $this->service->pollScannedBarcodes($token);
        $this->assertCount(1, $queue);
        $this->assertEquals('8991234567890', $queue[0]['code']);
    }

    public function test_end_to_end_remote_pairing_into_stock_in(): void
    {
        // 1. Laptop opens pairing on Stock IN
        $laptop = Livewire::actingAs($this->admin)->test(StockInCreate::class);
        $laptop->call('openPairing');

        $token = $laptop->get('pairingToken');
        $this->assertNotEmpty($token);
        $this->assertTrue($laptop->get('showPairingModal'));

        // 2. Phone connects and scans barcode
        $phone = Livewire::test(RemoteScanner::class, ['token' => $token]);
        $phone->call('processBarcode', '8991234567890');

        // 3. Laptop polls remote scanner
        $laptop->call('checkRemoteScanner');

        // 4. Verify item is in laptop form with quantity = 1
        $this->assertTrue($laptop->get('pairingConnected'));
        $this->assertEquals($this->product1->id, $laptop->get('items.0.product_id'));
        $this->assertEquals(1, $laptop->get('items.0.quantity'));

        // 5. Phone scans same barcode again (Mode 2 increment over remote!)
        $phone->call('processBarcode', '8991234567890');
        $laptop->call('checkRemoteScanner');

        // Quantity on laptop is now 2!
        $this->assertEquals(2, $laptop->get('items.0.quantity'));
    }

    public function test_end_to_end_remote_pairing_into_stock_out(): void
    {
        // 1. Laptop opens pairing on Stock OUT
        $laptop = Livewire::actingAs($this->admin)->test(StockOutCreate::class);
        $laptop->call('openPairing');

        $token = $laptop->get('pairingToken');
        $this->assertNotEmpty($token);

        // 2. Phone scans barcode
        $phone = Livewire::test(RemoteScanner::class, ['token' => $token]);
        $phone->call('processBarcode', '8991234567890');

        // 3. Laptop polls
        $laptop->call('checkRemoteScanner');

        // 4. Item added with quantity = 1
        $this->assertEquals($this->product1->id, $laptop->get('items.0.product_id'));
        $this->assertEquals(1, $laptop->get('items.0.quantity'));
    }

    public function test_end_to_end_remote_pairing_into_stock_opname(): void
    {
        // 1. Laptop opens pairing on Stock Opname
        $laptop = Livewire::actingAs($this->admin)->test(StockOpnameCreate::class);
        $laptop->call('openPairing');

        $token = $laptop->get('pairingToken');
        $this->assertNotEmpty($token);

        // 2. Phone scans barcode of product1 (current stock is 25)
        $phone = Livewire::test(RemoteScanner::class, ['token' => $token]);
        $phone->call('processBarcode', '8991234567890');

        // 3. Laptop polls
        $laptop->call('checkRemoteScanner');

        // 4. Item added with physical_stock = 1, system_stock = 25, diff = -24
        $this->assertEquals($this->product1->id, $laptop->get('items.0.product_id'));
        $this->assertEquals(25, $laptop->get('items.0.system_stock'));
        $this->assertEquals(1, $laptop->get('items.0.physical_stock'));
        $this->assertEquals(-24, $laptop->get('items.0.difference'));

        // 5. Phone scans second time -> physical count increments to 2
        $phone->call('processBarcode', '8991234567890');
        $laptop->call('checkRemoteScanner');

        $this->assertEquals(2, $laptop->get('items.0.physical_stock'));
        $this->assertEquals(-23, $laptop->get('items.0.difference'));
    }

    public function test_persistent_user_session_across_components_and_save_and_new(): void
    {
        // 1. Laptop opens pairing on Stock IN
        $stockIn = Livewire::actingAs($this->admin)->test(StockInCreate::class);
        $stockIn->call('openPairing');
        $token = $stockIn->get('pairingToken');
        $this->assertNotEmpty($token);

        // Phone connects
        $phone = Livewire::test(RemoteScanner::class, ['token' => $token]);
        $stockIn->call('checkRemoteScanner');
        $this->assertTrue($stockIn->get('pairingConnected'));

        // 2. Scan barcode & save with saveAndCreateNew
        $phone->call('processBarcode', '8991234567890');
        $stockIn->call('checkRemoteScanner');
        $stockIn->set('supplier_id', $this->supplier->id);
        $stockIn->set('transaction_date', now()->toDateString());
        $stockIn->call('saveAndCreateNew');

        // Form was reset but pairing token is STILL attached
        $this->assertEquals($token, $stockIn->get('pairingToken'));
        $this->assertTrue($stockIn->get('pairingConnected'));
        $this->assertCount(1, $stockIn->get('items'));
        $this->assertEmpty($stockIn->get('items.0.product_id'));

        // Verify notification reached session for phone
        $session = $this->service->getSession($token);
        $this->assertNotNull($session['last_transaction']);
        $this->assertStringStartsWith('SIN-', $session['last_transaction']['transaction_no']);

        // 3. User navigates to Stock OUT -> automatically re-attaches to the same phone session!
        $stockOut = Livewire::actingAs($this->admin)->test(StockOutCreate::class);
        $this->assertEquals($token, $stockOut->get('pairingToken'));
        $this->assertTrue($stockOut->get('pairingConnected'));
    }
}
