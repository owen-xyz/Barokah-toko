@extends('layouts.app')
@section('title', 'Laporan')

@section('content')
<div class="report-wrapper">

    {{-- Header --}}
    <div class="report-header">
        <div>
            <h1 class="report-title"><i class="bi bi-bar-chart-line-fill me-2"></i>Dashboard Laporan</h1>
            <p class="report-subtitle">Ringkasan performa bisnis Anda</p>
        </div>
        <button id="refreshBtn" class="btn-refresh">
            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
        </button>
    </div>

    {{-- Summary Cards --}}
    <div class="summary-cards">
        <div class="summary-card summary-card-blue">
            <div class="summary-card-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="summary-card-body">
                <p class="summary-card-label">Total Penjualan</p>
                <p id="cardPenjualan" class="summary-card-value">—</p>
            </div>
        </div>
        <div class="summary-card summary-card-orange">
            <div class="summary-card-icon"><i class="bi bi-box-seam-fill"></i></div>
            <div class="summary-card-body">
                <p class="summary-card-label">Total Modal</p>
                <p id="cardModal" class="summary-card-value">—</p>
            </div>
        </div>
        <div class="summary-card summary-card-green">
            <div class="summary-card-icon"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="summary-card-body">
                <p class="summary-card-label">Total Keuntungan</p>
                <p id="cardKeuntungan" class="summary-card-value">—</p>
            </div>
        </div>
        <div class="summary-card summary-card-purple">
            <div class="summary-card-icon"><i class="bi bi-receipt-cutoff"></i></div>
            <div class="summary-card-body">
                <p class="summary-card-label">Jumlah Transaksi</p>
                <p id="cardTransaksi" class="summary-card-value">—</p>
            </div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="charts-row">
        <div class="chart-card chart-card-wide">
            <div class="chart-card-header">
                <h3 class="chart-card-title">
                    <i class="bi bi-bar-chart-fill me-2 text-primary"></i>Penjualan 7 Hari Terakhir
                </h3>
                <span class="chart-unit">dalam Rupiah</span>
            </div>
            <div class="chart-container">
                <canvas id="barChart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <div class="chart-card-header">
                <h3 class="chart-card-title">
                    <i class="bi bi-pie-chart-fill me-2 text-success"></i>Komposisi Keuangan
                </h3>
            </div>
            <div class="chart-container chart-container-donut">
                <canvas id="doughnutChart"></canvas>
            </div>
            <div id="doughnutLegend" class="donut-legend"></div>
        </div>
    </div>

    {{-- Riwayat Transaksi --}}
    <div class="history-card">
        <div class="history-header">
            <h3 class="history-title">
                <i class="bi bi-clock-history me-2 text-primary"></i>Riwayat Transaksi
            </h3>
            <div class="history-filters">
                <input type="date" id="filterStart" class="filter-date" title="Dari tanggal">
                <span class="text-muted small">—</span>
                <input type="date" id="filterEnd" class="filter-date" title="Sampai tanggal">
                <button id="filterBtn" class="btn-filter-date">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
                <button id="resetFilterBtn" class="btn-reset-date">
                    <i class="bi bi-x-circle me-1"></i>Reset
                </button>
            </div>
        </div>

        <div id="historyTableWrap" class="history-table-wrap">
            <div class="history-loading" id="historyLoading">
                <div class="spinner-border text-primary" role="status"></div>
                <p>Memuat riwayat...</p>
            </div>
            <table class="history-table" id="historyTable" style="display:none">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Transaksi</th>
                        <th>Tanggal</th>
                        <th class="text-end">Item</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Keuntungan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="historyBody"></tbody>
            </table>
            <div class="history-empty d-none" id="historyEmpty">
                <i class="bi bi-inbox"></i>
                <p>Belum ada transaksi</p>
            </div>
        </div>

        <div id="historyPagination" class="history-pagination"></div>
    </div>

</div>

{{-- ═══ MODAL DETAIL TRANSAKSI ═══════════════════════════════════════════════ --}}
<div class="modal-overlay" id="detailOverlay" onclick="closeDetailModal()"></div>
<div class="detail-modal" id="detailModal">
    <div class="detail-modal-header">
        <div>
            <h3 class="detail-modal-title" id="detailKode">Detail Transaksi</h3>
            <p class="detail-modal-sub" id="detailDate"></p>
        </div>
        <button class="modal-close-btn" onclick="closeDetailModal()"><i class="bi bi-x-lg"></i></button>
    </div>

    <div class="detail-modal-body">
        <div id="detailLoading" class="detail-loading">
            <div class="spinner-border text-primary" role="status"></div>
        </div>

        <div id="detailContent" style="display:none">
            <table class="detail-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Harga</th>
                        <th class="text-end">Subtotal</th>
                        <th class="text-end">Untung</th>
                    </tr>
                </thead>
                <tbody id="detailBody"></tbody>
            </table>

            <div class="detail-summary">
                <div class="detail-sum-row">
                    <span>Total Modal</span>
                    <span id="sumModal">—</span>
                </div>
                <div class="detail-sum-row">
                    <span>Total Penjualan</span>
                    <span id="sumTotal" class="fw-bold text-primary">—</span>
                </div>
                <div class="detail-sum-row">
                    <span>Total Keuntungan</span>
                    <span id="sumProfit" class="fw-bold text-success">—</span>
                </div>
            </div>
        </div>
    </div>

    <div class="detail-modal-footer">
        <button class="btn-detail-action btn-print-receipt" id="btnPrintReceipt" onclick="printReceipt()">
            <i class="bi bi-printer me-1"></i> Cetak Struk
        </button>
        <button class="btn-detail-action btn-share-wa" onclick="shareWA()">
            <i class="bi bi-whatsapp me-1"></i> Kirim WA
        </button>
        <button class="btn-detail-action btn-export-excel" onclick="exportExcel()">
            <i class="bi bi-file-earmark-excel me-1"></i> Excel
        </button>
        <button class="btn-detail-action btn-export-pdf" onclick="exportPDF()">
            <i class="bi bi-file-earmark-pdf me-1"></i> PDF
        </button>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/reports.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/reports.js') }}"></script>
@endpush