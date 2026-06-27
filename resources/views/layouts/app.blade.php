<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'POS System') - Kasir</title>

    {{-- Bootstrap 5 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    {{-- SweetAlert2 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    {{-- Custom CSS --}}
    <link rel="stylesheet" href="{{ asset('css/pos.css') }}">

    @stack('styles')
</head>
<body>

{{-- ─── Navbar ─────────────────────────────────────────────────────────────── --}}
<nav class="navbar navbar-expand-lg navbar-dark pos-navbar">
    <div class="container-fluid px-4">
        {{-- Brand --}}
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('pos.index') }}">
            <div class="brand-icon">
                <i class="bi bi-shop"></i>
            </div>
            <span class="brand-name">TOKO-BAROKAH</span>
        </a>

        {{-- Nav Links --}}
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('pos.index') }}"
               class="nav-btn {{ request()->routeIs('pos.*') ? 'active' : '' }}">
                <i class="bi bi-cart3 me-1"></i> Transaksi
            </a>
            <a href="{{ route('reports.index') }}"
               class="nav-btn {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line me-1"></i> Laporan
            </a>
        </div>
    </div>
</nav>

{{-- ─── Main Content ────────────────────────────────────────────────────────── --}}
<main>
    @yield('content')
</main>

{{-- ─── Scripts ─────────────────────────────────────────────────────────────── --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

@stack('scripts')
</body>
</html>
