<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Proteksi: jangan jalankan ulang jika user admin sudah terdaftar
        if (User::where('email', 'admin@stock.com')->exists()) {
            $this->command?->info('Data awal sudah ada di database. Melewati proses seeding.');

            return;
        }

        // 1. Akun Pengguna
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@stock.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $petugas = User::create([
            'name' => 'Petugas Gudang',
            'email' => 'petugas@stock.com',
            'password' => Hash::make('password'),
            'role' => 'petugas',
            'is_active' => true,
        ]);

        // 2. Kategori Barang
        $catJaringan = Category::create([
            'name' => 'Jaringan & Server',
            'description' => 'Perangkat dan media transmisi jaringan komputer',
            'is_active' => true,
        ]);

        $catAksesoris = Category::create([
            'name' => 'Aksesoris & Komponen',
            'description' => 'Perangkat peripheral dan suku cadang komputer',
            'is_active' => true,
        ]);

        $catAtk = Category::create([
            'name' => 'Alat Tulis Kantor',
            'description' => 'Perlengkapan operasional kantor dan administrasi',
            'is_active' => true,
        ]);

        // 3. Satuan Barang
        $unitPcs = Unit::create(['name' => 'Pieces', 'symbol' => 'PCS', 'is_active' => true]);
        $unitBox = Unit::create(['name' => 'Box', 'symbol' => 'BOX', 'is_active' => true]);
        $unitUnit = Unit::create(['name' => 'Unit', 'symbol' => 'UNIT', 'is_active' => true]);
        $unitRoll = Unit::create(['name' => 'Roll', 'symbol' => 'ROLL', 'is_active' => true]);

        // 4. Supplier
        $sup1 = Supplier::create([
            'code' => 'SUP-001',
            'name' => 'PT Sinar Abadi Perkasa',
            'phone' => '081234567890',
            'email' => 'sinar@abadiperkasa.co.id',
            'address' => 'Kawasan Industri Pulogadung Blok B No. 12, Jakarta Timur',
            'is_active' => true,
        ]);

        $sup2 = Supplier::create([
            'code' => 'SUP-002',
            'name' => 'CV Maju Teknologi Solusindo',
            'phone' => '085678912345',
            'email' => 'sales@majutekno.com',
            'address' => 'Jl. Boulevard Barat Raya Blok LC-7, Kelapa Gading, Jakarta Utara',
            'is_active' => true,
        ]);

        $sup3 = Supplier::create([
            'code' => 'SUP-003',
            'name' => 'Distributor Stationery Jaya',
            'phone' => '081987654321',
            'email' => 'order@stationeryjaya.com',
            'address' => 'Jl. Pangeran Jayakarta No. 88, Jakarta Pusat',
            'is_active' => true,
        ]);

        // 5. Master Barang (current_stock awal di-set 0, kemudian diisi lewat mutasi agar tercatat di kartu stok)
        $prodKabel = Product::create([
            'sku' => 'PRD-LAN-CAT6',
            'name' => 'Kabel UTP Cat6 305M Belden Original',
            'category_id' => $catJaringan->id,
            'unit_id' => $unitRoll->id,
            'minimum_stock' => 3,
            'current_stock' => 0,
            'is_active' => true,
        ]);

        $prodRj45 = Product::create([
            'sku' => 'PRD-CON-RJ45',
            'name' => 'Konektor RJ45 Cat6 Gold Plated (Isi 50 pcs)',
            'category_id' => $catJaringan->id,
            'unit_id' => $unitBox->id,
            'minimum_stock' => 5,
            'current_stock' => 0,
            'is_active' => true,
        ]);

        $prodMouse = Product::create([
            'sku' => 'PRD-MOU-W01',
            'name' => 'Mouse Wireless Silent Click Logitech B175',
            'category_id' => $catAksesoris->id,
            'unit_id' => $unitUnit->id,
            'minimum_stock' => 5,
            'current_stock' => 0,
            'is_active' => true,
        ]);

        $prodKeyboard = Product::create([
            'sku' => 'PRD-KBD-M02',
            'name' => 'Keyboard Mechanical TKL Red Switch',
            'category_id' => $catAksesoris->id,
            'unit_id' => $unitUnit->id,
            'minimum_stock' => 4,
            'current_stock' => 0,
            'is_active' => true,
        ]);

        $prodHvs = Product::create([
            'sku' => 'PRD-PAP-A480',
            'name' => 'Kertas HVS PaperOne A4 80gsm (1 Rim / 500 Lembar)',
            'category_id' => $catAtk->id,
            'unit_id' => $unitBox->id,
            'minimum_stock' => 8,
            'current_stock' => 0,
            'is_active' => true,
        ]);

        $prodPen = Product::create([
            'sku' => 'PRD-PEN-GEL',
            'name' => 'Pulpen Gel Zebra Sarasa Clip 0.5mm Hitam (Pack 12 pcs)',
            'category_id' => $catAtk->id,
            'unit_id' => $unitBox->id,
            'minimum_stock' => 3,
            'current_stock' => 0,
            'is_active' => true,
        ]);

        // 6. Jalankan Transaksi Awal lewat StockService agar Kartu Stok Terisi Sempurna
        $stockService = app(StockService::class);

        // Transaksi Masuk #1 (10 hari lalu)
        $stockService->recordStockIn(
            [
                'transaction_date' => Carbon::today()->subDays(10)->toDateString(),
                'supplier_id' => $sup1->id,
                'note' => 'Pengadaan stok batch 1 perangkat jaringan',
            ],
            [
                ['product_id' => $prodKabel->id, 'quantity' => 12, 'unit_price' => 1250000],
                ['product_id' => $prodRj45->id, 'quantity' => 30, 'unit_price' => 85000],
            ],
            $admin->id
        );

        // Transaksi Masuk #2 (7 hari lalu)
        $stockService->recordStockIn(
            [
                'transaction_date' => Carbon::today()->subDays(7)->toDateString(),
                'supplier_id' => $sup2->id,
                'note' => 'Pengadaan peripheral komputer kantor',
            ],
            [
                ['product_id' => $prodMouse->id, 'quantity' => 20, 'unit_price' => 135000],
                ['product_id' => $prodKeyboard->id, 'quantity' => 10, 'unit_price' => 350000],
            ],
            $petugas->id
        );

        // Transaksi Masuk #3 (5 hari lalu)
        $stockService->recordStockIn(
            [
                'transaction_date' => Carbon::today()->subDays(5)->toDateString(),
                'supplier_id' => $sup3->id,
                'note' => 'Pasokan kebutuhan ATK',
            ],
            [
                ['product_id' => $prodHvs->id, 'quantity' => 10, 'unit_price' => 45000],
            ],
            $petugas->id
        );

        // Transaksi Keluar #1 (3 hari lalu)
        $stockService->recordStockOut(
            [
                'transaction_date' => Carbon::today()->subDays(3)->toDateString(),
                'recipient' => 'Divisi Infrastruktur Jaringan',
                'note' => 'Pemasangan kabel LAN lantai 2 & 3',
            ],
            [
                ['product_id' => $prodKabel->id, 'quantity' => 4],
                ['product_id' => $prodRj45->id, 'quantity' => 10],
            ],
            $petugas->id
        );

        // Transaksi Keluar #2 (kemarin)
        $stockService->recordStockOut(
            [
                'transaction_date' => Carbon::yesterday()->toDateString(),
                'recipient' => 'Departemen Operasional & HRD',
                'note' => 'Penggantian mouse staf dan kebutuhan cetak formulir',
            ],
            [
                ['product_id' => $prodMouse->id, 'quantity' => 5],
                ['product_id' => $prodHvs->id, 'quantity' => 8], // Menyisakan 2 box (di bawah minimum 8 -> Status Menipis!)
            ],
            $petugas->id
        );
    }
}
