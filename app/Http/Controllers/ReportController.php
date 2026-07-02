<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reportService) {}

    public function index(): View
    {
        return view('reports.index');
    }

    /** Data summary + chart */
    public function getData(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'summary'     => $this->reportService->getSummary(),
                'daily_sales' => $this->reportService->getDailySalesChart(),
                'doughnut'    => $this->reportService->getDoughnutChart(),
            ],
        ]);
    }

    /** Riwayat transaksi (AJAX paginated) */
    public function history(Request $request): JsonResponse
    {
        $paginator = $this->reportService->getHistory([
            'start' => $request->input('start'),
            'end'   => $request->input('end'),
        ]);

        $items = $paginator->map(fn($t) => [
            'id'               => $t->id,
            'kode_transaksi'   => $t->kode_transaksi,
            'total_harga'      => (float) $t->total_harga,
            'total_modal'      => (float) $t->total_modal,
            'total_keuntungan' => (float) $t->total_keuntungan,
            'jumlah_item'      => $t->details->count(),
            'created_at'       => $t->created_at->format('d/m/Y H:i'),
        ]);

        return response()->json([
            'success'      => true,
            'data'         => $items,
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'total'        => $paginator->total(),
        ]);
    }

    /** Detail satu transaksi (AJAX) */
    public function detail(int $id): JsonResponse
    {
        $t = $this->reportService->getDetail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id'               => $t->id,
                'kode_transaksi'   => $t->kode_transaksi,
                'created_at'       => $t->created_at->format('d/m/Y H:i:s'),
                'total_harga'      => (float) $t->total_harga,
                'total_modal'      => (float) $t->total_modal,
                'total_keuntungan' => (float) $t->total_keuntungan,
                'items' => $t->details->map(fn($d) => [
                    'nama_produk'  => $d->product->nama_produk ?? 'Produk Dihapus',
                    'kode_produk'  => $d->product->kode_produk ?? '-',
                    'qty'          => $d->qty,
                    'harga_jual'   => (float) $d->harga_jual,
                    'harga_modal'  => (float) $d->harga_modal,
                    'subtotal'     => (float) $d->subtotal,
                    'keuntungan'   => (float) $d->keuntungan,
                ]),
            ],
        ]);
    }
}
