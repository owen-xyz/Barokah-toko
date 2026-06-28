<?php

use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// Redirect root ke halaman transaksi
Route::get('/', fn() => redirect()->route('pos.index'));

// ─── Halaman Transaksi (POS) ──────────────────────────────────────────────────
Route::prefix('pos')->name('pos.')->group(function () {
    Route::get('/', [TransactionController::class, 'index'])->name('index');
});

// ─── Halaman Laporan ──────────────────────────────────────────────────────────
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/data', [ReportController::class, 'getData'])->name('data');
});

// ─── Admin Produk ─────────────────────────────────────────────────────────────
Route::prefix('admin/products')->name('admin.products.')->group(function () {
    Route::get('/',          [AdminProductController::class, 'index'])->name('index');
    Route::post('/',         [AdminProductController::class, 'store'])->name('store');
    Route::get('/{product}', [AdminProductController::class, 'show'])->name('show');
    Route::put('/{product}', [AdminProductController::class, 'update'])->name('update');
    Route::delete('/{product}', [AdminProductController::class, 'destroy'])->name('destroy');
});

// ─── API Internal (AJAX endpoints untuk POS) ──────────────────────────────────
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/products',        [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
    Route::post('/transactions',   [TransactionController::class, 'store'])->name('transactions.store');
});
