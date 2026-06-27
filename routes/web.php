<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root ke halaman transaksi
Route::get('/', fn() => redirect()->route('pos.index'));

// ─── Halaman Transaksi (POS) ─────────────────────────────────────────────────
Route::prefix('pos')->name('pos.')->group(function () {
    Route::get('/', [TransactionController::class, 'index'])->name('index');
});

// ─── Halaman Laporan ─────────────────────────────────────────────────────────
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/data', [ReportController::class, 'getData'])->name('data');
});

// ─── API Internal (AJAX endpoints) ───────────────────────────────────────────
Route::prefix('api')->name('api.')->group(function () {
    // Products
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');

    // Transactions
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
});
