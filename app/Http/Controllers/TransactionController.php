<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(
        private readonly TransactionService $transactionService
    ) {}

    /**
     * Tampilkan halaman utama POS / transaksi.
     */
    public function index(): View
    {
        return view('pos.index');
    }

    /**
     * Simpan transaksi baru.
     */
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        try {
            $transaction = $this->transactionService->store($request->validated()['items']);

            return response()->json([
                'success' => true,
                'message' => "Transaksi berhasil disimpan! Kode: {$transaction->kode_transaksi}",
                'data'    => [
                    'id'               => $transaction->id,
                    'kode_transaksi'   => $transaction->kode_transaksi,
                    'total_harga'      => $transaction->total_harga,
                    'total_keuntungan' => $transaction->total_keuntungan,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()['items'][0] ?? 'Validasi gagal.',
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tampilkan halaman struk untuk dicetak.
     */
    public function printReceipt(int $id): View
    {
        $transaction = \App\Models\Transaction::with('details.product')->findOrFail($id);

        return view('pos.receipt', compact('transaction'));
    }
}
