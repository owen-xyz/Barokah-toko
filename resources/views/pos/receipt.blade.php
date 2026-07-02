<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk - {{ $transaction->kode_transaksi }}</title>
    <style>
        @page {
            
            size: 80mm portrait;
            margin: 0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-weight: 700;
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            width: 80mm;
            margin: 0 auto;
            padding: 0;
            background: #fff;
        }

        .center { text-align: center; }
        .right  { text-align: right; }
        .bold   { font-weight: 700; }

        .store-name {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .store-sub {
            font-size: 10px;
            margin-top: 2px;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin-bottom: 2px;
        }

        .item-row {
            margin-bottom: 6px;
        }

        .item-name {
            font-size: 12px;
            font-weight: 700;
        }

        .item-detail {
            display: flex;
            justify-content: space-between;
            font-weight: 700;
            font-size: 11px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 3px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            font-weight: 700;
            margin-top: 4px;
        }

        .footer {
            margin-top: 12px;
            font-size: 10px;
        }

        .footer p { margin-bottom: 2px; }

        /* Tombol print — disembunyikan saat dicetak */
        .print-actions {
            position: fixed;
            top: 16px;
            right: 16px;
            display: flex;
            gap: 8px;
        }

        .btn-print, .btn-close {
            font-family: Arial, sans-serif;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-print { background: #2563EB; color: #fff; }
        .btn-close { background: #E5E7EB; color: #374151; }

        @media print {
            .print-actions { display: none; }
            body { width: 100%; padding: 0 4mm; }
        }
    </style>
</head>
<body>

    {{-- Tombol aksi (tidak ikut tercetak) --}}
    <div class="print-actions">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak</button>
        <button class="btn-close" onclick="window.close()">✕ Tutup</button>
    </div>

    {{-- Header Toko --}}
    <div class="center">
        <div class="store-name">TOKO BAROKAH</div>
        <div class="store-sub">Jl. Kampung cibobos Rw/Rw : 002/005</div>
        <div class="store-sub">Telp: 0858-3456-7890</div>
    </div>

    <div class="divider"></div>

    {{-- Info Transaksi --}}
    <div class="info-row">
        <span>No. Struk</span>
        <span class="bold">{{ $transaction->kode_transaksi }}</span>
    </div>
    <div class="info-row">
        <span>Tanggal</span>
        <span>{{ $transaction->created_at->translatedFormat('d M Y, H:i') }}</span>
    </div>

    <div class="divider"></div>

    {{-- Daftar Item --}}
    @foreach ($transaction->details as $detail)
    <div class="item-row">
        <div class="item-name">{{ $detail->product->nama_produk ?? 'Produk Dihapus' }}</div>
        <div class="item-detail">
            <span>{{ $detail->qty }} x Rp {{ number_format($detail->harga_jual, 0, ',', '.') }}</span>
            <span>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</span>
        </div>
    </div>
    @endforeach

    <div class="divider"></div>

    {{-- Ringkasan --}}
    <div class="summary-row">
        <span>Subtotal</span>
        <span>Rp {{ number_format($transaction->total_harga, 0, ',', '.') }}</span>
    </div>

    <div class="divider"></div>

    <div class="total-row">
        <span>TOTAL</span>
        <span>Rp {{ number_format($transaction->total_harga, 0, ',', '.') }}</span>
    </div>

    <div class="divider"></div>

    {{-- Footer --}}
    <div class="center footer">
        <p>Terima kasih atas kunjungan Anda</p>
        <p>Barang yang sudah dibeli tidak dapat dikembalikan</p>
        <p>=== {{ now()->format('Y') }} KasirPOS ===</p>
    </div>

    <script>
        // Auto-trigger print dialog setelah halaman dimuat (opsional, bisa di-comment)
        // window.onload = () => window.print();
    </script>

</body>
</html>
