@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
<div class="report-wrapper">

    {{-- ─── Page Header ──────────────────────────────────────────────────────── --}}
    <div class="report-header">
        <div>
            <h1 class="report-title"><i class="bi bi-bar-chart-line-fill me-2"></i>Dashboard Laporan</h1>
            <p class="report-subtitle">Ringkasan performa bisnis Anda</p>
        </div>
        <button id="refreshBtn" class="btn-refresh">
            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
        </button>
    </div>

    {{-- ─── Summary Cards ────────────────────────────────────────────────────── --}}
    <div class="summary-cards">

        <div class="summary-card summary-card-blue">
            <div class="summary-card-icon">
                <i class="bi bi-currency-dollar"></i>
            </div>
            <div class="summary-card-body">
                <p class="summary-card-label">Total Penjualan</p>
                <p id="cardPenjualan" class="summary-card-value">
                    <span class="loading-dots">...</span>
                </p>
            </div>
        </div>

        <div class="summary-card summary-card-orange">
            <div class="summary-card-icon">
                <i class="bi bi-box-seam-fill"></i>
            </div>
            <div class="summary-card-body">
                <p class="summary-card-label">Total Modal</p>
                <p id="cardModal" class="summary-card-value">
                    <span class="loading-dots">...</span>
                </p>
            </div>
        </div>

        <div class="summary-card summary-card-green">
            <div class="summary-card-icon">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
            <div class="summary-card-body">
                <p class="summary-card-label">Total Keuntungan</p>
                <p id="cardKeuntungan" class="summary-card-value">
                    <span class="loading-dots">...</span>
                </p>
            </div>
        </div>

        <div class="summary-card summary-card-purple">
            <div class="summary-card-icon">
                <i class="bi bi-receipt-cutoff"></i>
            </div>
            <div class="summary-card-body">
                <p class="summary-card-label">Jumlah Transaksi</p>
                <p id="cardTransaksi" class="summary-card-value">
                    <span class="loading-dots">...</span>
                </p>
            </div>
        </div>

    </div>

    {{-- ─── Charts ───────────────────────────────────────────────────────────── --}}
    <div class="charts-row">

        {{-- Bar Chart: Produk terjual per hari --}}
        <div class="chart-card chart-card-wide">
            <div class="chart-card-header">
                <h3 class="chart-card-title">
                    <i class="bi bi-bar-chart-fill me-2 text-primary"></i>
                    Produk Terjual (7 Hari Terakhir)
                </h3>
                <span class="chart-unit">satuan (pcs)</span>
            </div>
            <div class="chart-container">
                <canvas id="barChart"></canvas>
            </div>
        </div>

        {{-- Doughnut Chart: Modal vs Penjualan vs Keuntungan --}}
        <div class="chart-card">
            <div class="chart-card-header">
                <h3 class="chart-card-title">
                    <i class="bi bi-pie-chart-fill me-2 text-success"></i>
                    Komposisi Keuangan
                </h3>
            </div>
            <div class="chart-container chart-container-donut">
                <canvas id="doughnutChart"></canvas>
            </div>
            <div id="doughnutLegend" class="donut-legend">
                {{-- Diisi JavaScript --}}
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('js/reports.js') }}"></script>
@endpush
