<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionDetail;

class ReportService
{
    /**
     * Ringkasan keseluruhan.
     */
    public function getSummary(): array
    {
        $row = Transaction::selectRaw('
            COALESCE(SUM(total_harga), 0)      AS total_penjualan,
            COALESCE(SUM(total_modal), 0)      AS total_modal,
            COALESCE(SUM(total_keuntungan), 0) AS total_keuntungan,
            COUNT(id)                           AS jumlah_transaksi
        ')->first();

        return [
            'total_penjualan'  => (float) $row->total_penjualan,
            'total_modal'      => (float) $row->total_modal,
            'total_keuntungan' => (float) $row->total_keuntungan,
            'jumlah_transaksi' => (int)   $row->jumlah_transaksi,
        ];
    }

    /**
     * Bar chart: penjualan harian 7 hari terakhir (Rp).
     */
    public function getDailySalesChart(): array
    {
        $rows = Transaction::selectRaw('DATE(created_at) AS tgl, SUM(total_harga) AS total')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('tgl')
            ->orderBy('tgl')
            ->pluck('total', 'tgl');

        $result = [];
        for ($i = 6; $i >= 0; $i--) {
            $date     = now()->subDays($i)->format('Y-m-d');
            $label    = now()->subDays($i)->translatedFormat('D d/m');
            $result[] = ['label' => $label, 'total' => (float) ($rows[$date] ?? 0)];
        }

        return $result;
    }

    /**
     * Doughnut chart: modal vs keuntungan (bagian dari total penjualan).
     * Hanya 2 slice agar tidak error saat salah satu nilai 0.
     */
    public function getDoughnutChart(): array
    {
        $s = $this->getSummary();

        return [
            'labels' => ['Modal', 'Keuntungan'],
            'values' => [$s['total_modal'], $s['total_keuntungan']],
            'colors' => ['#EA580C', '#16A34A'],
        ];
    }

    /**
     * Riwayat transaksi dengan pagination + filter tanggal.
     */
    public function getHistory(array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Transaction::with('details.product')
            ->when($filters['start'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['end']   ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->orderByDesc('created_at')
            ->paginate(15);
    }

    /**
     * Detail satu transaksi.
     */
    public function getDetail(int $id): Transaction
    {
        return Transaction::with('details.product')->findOrFail($id);
    }
}
