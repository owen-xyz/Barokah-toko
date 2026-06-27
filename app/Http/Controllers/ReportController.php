<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService
    ) {}

    /**
     * Tampilkan halaman laporan.
     */
    public function index(): View
    {
        return view('reports.index');
    }

    /**
     * Ambil data laporan (summary + chart) via AJAX.
     */
    public function getData(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'summary'        => $this->reportService->getSummary(),
                'daily_sales'    => $this->reportService->getDailySalesChart(),
                'doughnut_chart' => $this->reportService->getDoughnutChart(),
            ],
        ]);
    }
}
