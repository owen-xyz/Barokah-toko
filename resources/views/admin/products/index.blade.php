@extends('layouts.app')

@section('title', 'Manajemen Produk')

@section('content')
<div class="admin-wrapper">

    {{-- Page Header --}}
    <div class="admin-header">
        <div>
            <h1 class="admin-title">
                <i class="bi bi-box-seam-fill me-2"></i>Manajemen Produk
            </h1>
            <p class="admin-subtitle">Kelola seluruh produk toko Anda</p>
        </div>
        <button class="btn-add-product" onclick="openAddModal()">
            <i class="bi bi-plus-lg me-1"></i> Tambah Produk
        </button>
    </div>

    {{-- Filter & Search --}}
    <div class="admin-filter-bar">
        <form method="GET" action="{{ route('admin.products.index') }}" id="filterForm">
            <div class="filter-inner">
                <div class="filter-search-box">
                    <i class="bi bi-search filter-search-icon"></i>
                    <input type="text" name="search" class="filter-search-input" 
                           placeholder="Cari nama, kode produk..." value="{{ $keyword }}" autocomplete="off">
                </div>
                <select name="kategori" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="">Semua Kategori</option>
                    @foreach ($kategoris as $kat)
                        <option value="{{ $kat }}" {{ $kategori === $kat ? 'selected' : '' }}>{{ $kat }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-filter">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                @if ($keyword || $kategori)
                    <a href="{{ route('admin.products.index') }}" class="btn-reset">
                        <i class="bi bi-x-circle me-1"></i> Reset
                    </a>
                @endif
            </div>
        </form>
        <span class="product-total-badge">{{ $products->total() }} produk</span>
    </div>

    {{-- Product Grid --}}
    @if ($products->isEmpty())
        <div class="admin-empty">
            <i class="bi bi-box-open"></i>
            <p>Belum ada produk</p>
            <small>Klik "Tambah Produk" untuk menambahkan produk pertama</small>
        </div>
    @else
        <div class="admin-product-grid">
            @foreach ($products as $product)
            <div class="admin-product-card" id="card-{{ $product->id }}">
                @if ($product->stok <= 0)
                    <span class="stock-badge stock-badge-empty">Habis</span>
                @elseif ($product->stok <= 10)
                    <span class="stock-badge stock-badge-low">Stok Menipis</span>
                @endif

                <div class="admin-card-img-wrap">
                    <img src="{{ asset($product->gambar) }}" alt="{{ $product->nama_produk }}"
                         class="admin-card-img"
                         onerror="this.src='https://placehold.co/150x120/e8f4f8/2563eb?text=IMG'">
                </div>

                <div class="admin-card-body">
                    <span class="admin-card-kode">{{ $product->kode_produk }}</span>
                    <p class="admin-card-nama">{{ $product->nama_produk }}</p>
                    <span class="admin-card-kategori">{{ $product->kategori }}</span>

                    <div class="admin-card-prices">
                        <div class="price-row">
                            <span class="price-label">Ecer</span>
                            <span class="price-value price-ecer">Rp {{ number_format($product->harga_ecer, 0, ',', '.') }}</span>
                        </div>
                        <div class="price-row">
                            <span class="price-label">Dropship</span>
                            <span class="price-value">Rp {{ number_format($product->harga_dropship, 0, ',', '.') }}</span>
                        </div>
                        <div class="price-row">
                            <span class="price-label">Modal</span>
                            <span class="price-value text-secondary">Rp {{ number_format($product->harga_modal, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="admin-card-stock">
                        <i class="bi bi-archive me-1"></i>
                        Stok: <strong>{{ $product->stok }}</strong>
                    </div>
                </div>

                <div class="admin-card-actions">
                    <button class="btn-action btn-edit" onclick="openEditModal({{ $product->id }})" title="Edit">
                        <i class="bi bi-pencil-fill"></i> Edit
                    </button>
                    <button class="btn-action btn-delete"
                            onclick="deleteProduct({{ $product->id }}, '{{ addslashes($product->nama_produk) }}')"
                            title="Hapus">
                        <i class="bi bi-trash3-fill"></i>
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if ($products->hasPages())
        <div class="admin-pagination">
            @if ($products->onFirstPage())
                <span class="page-btn" style="opacity:.4;cursor:not-allowed"><i class="bi bi-chevron-left"></i></span>
            @else
                <a href="{{ $products->previousPageUrl() }}" class="page-btn"><i class="bi bi-chevron-left"></i></a>
            @endif

            @foreach ($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                <a href="{{ $url }}" class="page-btn {{ $page === $products->currentPage() ? 'active' : '' }}">{{ $page }}</a>
            @endforeach

            @if ($products->hasMorePages())
                <a href="{{ $products->nextPageUrl() }}" class="page-btn"><i class="bi bi-chevron-right"></i></a>
            @else
                <span class="page-btn" style="opacity:.4;cursor:not-allowed"><i class="bi bi-chevron-right"></i></span>
            @endif
        </div>
        @endif
    @endif

</div>

{{-- Overlay --}}
<div class="modal-overlay" id="modalOverlay" onclick="closeModal()"></div>

{{-- Modal Tambah / Edit --}}
<div class="product-modal" id="productModal">
    <div class="modal-header-custom">
        <h3 class="modal-title-custom" id="modalTitle">
            <i class="bi bi-plus-circle me-2" id="modalIcon"></i>
            <span id="modalTitleText">Tambah Produk</span>
        </h3>
        <button class="modal-close-btn" onclick="closeModal()"><i class="bi bi-x-lg"></i></button>
    </div>

    <div class="modal-body-custom">
        <form id="productForm" enctype="multipart/form-data" novalidate>
            <input type="hidden" id="productId" value="">

            <div class="form-grid">

                <div class="form-group">
                    <label class="form-label-custom">Kode Produk <span class="required">*</span></label>
                    <input type="text" name="kode_produk" id="kode_produk" class="form-input"
                           placeholder="Contoh: MG-001" maxlength="50">
                    <span class="form-error" id="err_kode_produk"></span>
                </div>

                <div class="form-group form-group-wide">
                    <label class="form-label-custom">Nama Produk <span class="required">*</span></label>
                    <input type="text" name="nama_produk" id="nama_produk" class="form-input"
                           placeholder="Nama lengkap produk" maxlength="255">
                    <span class="form-error" id="err_nama_produk"></span>
                </div>

                 <div class="form-group">
                    <label class="form-label-custom">Barcode <span class=""></span></label>
                    <input type="text" name="barcode" id="barcode" class="form-input"
                           placeholder="gunakan scaner" maxlength="50">
                    <span class="form-error" id="err_barcode"></span>
                </div>

                <div class="form-group">
                    <label class="form-label-custom">Kategori <span class="required">*</span></label>
                    <input type="text" name="kategori" id="kategori" class="form-input"
                           placeholder="Contoh: Minyak Goreng" list="kategoriList" maxlength="100">
                    <datalist id="kategoriList">
                        @foreach ($kategoris as $kat)
                            <option value="{{ $kat }}">
                        @endforeach
                    </datalist>
                    <span class="form-error" id="err_kategori"></span>
                </div>

                <div class="form-group">
                    <label class="form-label-custom">Stok <span class="required">*</span></label>
                    <input type="number" name="stok" id="stok" class="form-input" placeholder="0" min="0">
                    <span class="form-error" id="err_stok"></span>
                </div>

                <div class="form-group">
                    <label class="form-label-custom">Harga Modal <span class="required">*</span></label>
                    <div class="input-prefix-wrap">
                        <span class="input-prefix">Rp</span>
                        <input type="number" name="harga_modal" id="harga_modal"
                               class="form-input form-input-prefix" placeholder="0" min="0">
                    </div>
                    <span class="form-error" id="err_harga_modal"></span>
                </div>

                <div class="form-group">
                    <label class="form-label-custom">Harga Ecer <span class="required">*</span></label>
                    <div class="input-prefix-wrap">
                        <span class="input-prefix">Rp</span>
                        <input type="number" name="harga_ecer" id="harga_ecer"
                               class="form-input form-input-prefix" placeholder="0" min="0">
                    </div>
                    <span class="form-error" id="err_harga_ecer"></span>
                </div>

                <div class="form-group">
                    <label class="form-label-custom">Harga Dropship <span class="required">*</span></label>
                    <div class="input-prefix-wrap">
                        <span class="input-prefix">Rp</span>
                        <input type="number" name="harga_dropship" id="harga_dropship"
                               class="form-input form-input-prefix" placeholder="0" min="0">
                    </div>
                    <span class="form-error" id="err_harga_dropship"></span>
                </div>

                <div class="form-group form-group-wide">
                    <label class="form-label-custom">Gambar Produk</label>
                    <div class="img-preview-wrap">
                        <img id="imgPreview"
                             src="https://placehold.co/200x140/e8f4f8/2563eb?text=Preview"
                             alt="Preview" class="img-preview">
                        <button type="button" class="img-remove-btn d-none" id="imgRemoveBtn" onclick="removeImage()">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </div>
                    <div class="drop-zone" id="dropZone" onclick="document.getElementById('gambar').click()">
                        <i class="bi bi-cloud-arrow-up drop-zone-icon"></i>
                        <p class="drop-zone-text">Klik atau drag & drop gambar di sini</p>
                        <p class="drop-zone-sub">JPG, PNG, WEBP — Maks. 2MB</p>
                        <input type="file" name="gambar" id="gambar"
                               accept="image/jpg,image/jpeg,image/png,image/webp"
                               class="d-none" onchange="previewImage(this)">
                    </div>
                    <span class="form-error" id="err_gambar"></span>
                </div>

            </div>
        </form>
    </div>

    <div class="modal-footer-custom">
        <button type="button" class="btn-modal-cancel" onclick="closeModal()">
            <i class="bi bi-x me-1"></i> Batal
        </button>
        <button type="button" class="btn-modal-save" id="btnSave" onclick="submitProduct()">
            <i class="bi bi-check2-circle me-1"></i>
            <span id="btnSaveText">Simpan Produk</span>
        </button>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-products.css') }}">
@endpush

@push('scripts')
<script>
    const ROUTES = {
        store:   '{{ route('admin.products.store') }}',
        show:    '{{ url('admin/products') }}/',
        update:  '{{ url('admin/products') }}/',
        destroy: '{{ url('admin/products') }}/',
    };
    const CSRF = '{{ csrf_token() }}';
</script>
<script src="{{ asset('js/admin-products.js') }}"></script>
@endpush
