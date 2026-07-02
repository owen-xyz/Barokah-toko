@extends('layouts.app')

@section('title', 'Transaksi')

@section('content')
<div class="pos-wrapper">

    {{-- ═══════════════════════════════════════════════════════════════════════
         KOLOM KIRI — Produk
    ══════════════════════════════════════════════════════════════════════════ --}}
    <div class="pos-left">

        {{-- Search Bar --}}
        <div class="search-wrapper">
            <div class="search-box">
                <i class="bi bi-search search-icon"></i>
                <input
                    type="text"
                    id="searchInput"
                    class="form-control search-input"
                    placeholder="Cari produk, kode, atau scan barcode..."
                    autocomplete="off"
                >
                <div id="searchSpinner" class="search-spinner d-none">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                </div>
                <div class="scanner-indicator" id="scannerIndicator" title="Scanner siap — arahkan ke search box">
                    <i class="bi bi-upc-scan"></i>
                </div>
            </div>
            <p class="scanner-hint">
                <i class="bi bi-info-circle me-1"></i>
                Scan barcode langsung di kolom pencarian — produk otomatis masuk keranjang
            </p>
        </div>

        {{-- Kategori badge aktif --}}
        <div class="d-flex align-items-center justify-content-between mb-2 px-1">
            <span id="productCount" class="product-count-label">Memuat produk...</span>
        </div>

        {{-- Grid Produk --}}
        <div id="productGrid" class="product-grid">
            {{-- Diisi oleh JavaScript --}}
            @include('pos._product_skeleton')
        </div>

        {{-- Pagination --}}
        <div id="paginationWrapper" class="pagination-wrapper">
            {{-- Diisi oleh JavaScript --}}
        </div>

    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════
         KOLOM KANAN — Keranjang
    ══════════════════════════════════════════════════════════════════════════ --}}
    <div class="pos-right">

        {{-- Header Keranjang --}}
        <div class="cart-header">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-cart3 cart-header-icon"></i>
                <span class="cart-header-title">Keranjang Transaksi</span>
            </div>
            <button id="clearCartBtn" class="btn-clear-cart" title="Kosongkan keranjang">
                <i class="bi bi-trash3"></i>
            </button>
        </div>

        {{-- Daftar Item Keranjang --}}
        <div id="cartItems" class="cart-items">
            {{-- Empty state --}}
            <div id="cartEmpty" class="cart-empty">
                <div class="cart-empty-icon">
                    <i class="bi bi-cart-x"></i>
                </div>
                <p class="cart-empty-text">Keranjang masih kosong</p>
                <p class="cart-empty-sub">Klik produk di sebelah kiri untuk menambahkan</p>
            </div>
        </div>

        {{-- Divider --}}
        <div class="cart-divider"></div>

        {{-- Ringkasan Transaksi --}}
        <div class="cart-summary">
            <div class="summary-row">
                <span class="summary-label">
                    <i class="bi bi-receipt me-1"></i>Subtotal
                </span>
                <span id="summarySubtotal" class="summary-value">Rp 0</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">
                    <i class="bi bi-box-seam me-1"></i>Total Modal
                </span>
                <span id="summaryModal" class="summary-value text-secondary">Rp 0</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">
                    <i class="bi bi-graph-up-arrow me-1"></i>Keuntungan
                </span>
                <span id="summaryProfit" class="summary-value text-success">Rp 0</span>
            </div>
            <div class="summary-divider"></div>
            <div class="summary-row summary-total-row">
                <span class="summary-total-label">Total Pembayaran</span>
                <span id="summaryTotal" class="summary-total-value">Rp 0</span>
            </div>
        </div>

        {{-- Tombol Simpan Transaksi --}}
        <button id="saveTransactionBtn" class="btn-save-transaction" disabled>
            <i class="bi bi-check2-circle me-2"></i>
            <span id="saveTransactionText">Simpan Transaksi</span>
        </button>

    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/pos.js') }}"></script>
@endpush
