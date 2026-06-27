<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Ambil ringkasan laporan keseluruhan.
     */
    public function getSummary(): array
    {
        $summary = Transaction::selectRaw('
            SUM(total_harga)      as total_penjualan,
            SUM(total_modal)      as total_modal,
            SUM(total_keuntungan) as total_keuntungan,
            COUNT(id)             as jumlah_transaksi
        ')->first();

        return [
            'total_penjualan'   => (float) ($summary->total_penjualan ?? 0),
            'total_modal'       => (float) ($summary->total_modal ?? 0),
            'total_keuntungan'  => (float) ($summary->total_keuntungan ?? 0),
            'jumlah_transaksi'  => (int)   ($summary->jumlah_transaksi ?? 0),
        ];
    }

    /**
     * Data bar chart: jumlah produk terjual per hari (7 hari terakhir).
     */
    public function getDailySalesChart(): array
    {
        $data = TransactionDetail::selectRaw('
                DATE(created_at) as tanggal,
                SUM(qty)         as total_qty
            ')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        // Isi hari yang tidak ada transaksi dengan 0
        $result = [];
        for ($i = 6; $i >= 0; $i--) {
            $date   = now()->subDays($i)->format('Y-m-d');
            $label  = now()->subDays($i)->translatedFormat('D, d M');
            $found  = $data->firstWhere('tanggal', $date);

            $result[] = [
                'label' => $label,
                'qty'   => $found ? (int) $found->total_qty : 0,
            ];
        }

        return $result;
    }

    /**
     * Data doughnut chart: persentase modal, penjualan, keuntungan.
     */
    public function getDoughnutChart(): array
    {
        $summary = $this->getSummary();

        return [
            'modal'      => $summary['total_modal'],
            'penjualan'  => $summary['total_penjualan'],
            'keuntungan' => $summary['total_keuntungan'],
        ];
    }
}
