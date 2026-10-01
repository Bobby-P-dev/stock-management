<?php

use App\Http\Controllers\ExportController;
use App\Livewire\Auth\Login;
use App\Livewire\Categories\CategoryIndex;
use App\Livewire\Dashboard;
use App\Livewire\Opnames\StockOpnameCreate;
use App\Livewire\Opnames\StockOpnameIndex;
use App\Livewire\Opnames\StockOpnameShow;
use App\Livewire\Products\ProductIndex;
use App\Livewire\Reports\ReportIndex;
use App\Livewire\StockCard;
use App\Livewire\Suppliers\SupplierIndex;
use App\Livewire\Transactions\StockInCreate;
use App\Livewire\Transactions\StockOutCreate;
use App\Livewire\Transactions\TransactionIndex;
use App\Livewire\Transactions\TransactionShow;
use App\Livewire\Units\UnitIndex;
use App\Livewire\Users\UserIndex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/login', Login::class)->name('login');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    Route::get('/products', ProductIndex::class)->name('products.index');
    Route::get('/transactions', TransactionIndex::class)->name('transactions.index');
    Route::get('/transactions/in', StockInCreate::class)->name('transactions.in');
    Route::get('/transactions/out', StockOutCreate::class)->name('transactions.out');
    Route::get('/transactions/{transaction}', TransactionShow::class)->name('transactions.show');

    Route::get('/stock-card/{productId?}', StockCard::class)->name('stock-card');
    Route::get('/opnames', StockOpnameIndex::class)->name('opnames.index');
    Route::get('/opnames/create', StockOpnameCreate::class)->name('opnames.create');
    Route::get('/opnames/{opname}', StockOpnameShow::class)->name('opnames.show');
    Route::get('/reports', ReportIndex::class)->name('reports.index');

    Route::get('/export/stock', [ExportController::class, 'exportStock'])->name('export.stock');
    Route::get('/export/stock-in', [ExportController::class, 'exportStockIn'])->name('export.stock-in');
    Route::get('/export/stock-out', [ExportController::class, 'exportStockOut'])->name('export.stock-out');
    Route::get('/export/stock-card/{productId}', [ExportController::class, 'exportStockCard'])->name('export.stock-card');
    Route::get('/export/opname/{opnameId}', [ExportController::class, 'exportOpname'])->name('export.opname');

    Route::middleware('admin')->group(function () {
        Route::get('/categories', CategoryIndex::class)->name('categories.index');
        Route::get('/units', UnitIndex::class)->name('units.index');
        Route::get('/suppliers', SupplierIndex::class)->name('suppliers.index');
        Route::get('/users', UserIndex::class)->name('users.index');
    });
});
