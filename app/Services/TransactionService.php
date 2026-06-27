<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    /**
     * Simpan transaksi baru beserta detail dan update stok produk.
     * Menggunakan DB::transaction() untuk konsistensi data.
     *
     * @param  array $items  Array item keranjang dari request
     * @return Transaction
     *
     * @throws ValidationException
     * @throws \Throwable
     */
    public function store(array $items): Transaction
    {
        // Validasi stok sebelum transaksi dimulai
        $this->validateStock($items);

        return DB::transaction(function () use ($items) {
            $totalModal      = 0;
            $totalHarga      = 0;
            $totalKeuntungan = 0;

            // Hitung total dari semua item
            foreach ($items as $item) {
                $product  = Product::findOrFail($item['product_id']);
                $qty      = (int) $item['qty'];
                $hargaJual = (float) $item['harga_jual'];

                $subtotal    = $hargaJual * $qty;
                $modal       = $product->harga_modal * $qty;
                $keuntungan  = $subtotal - $modal;

                $totalModal      += $modal;
                $totalHarga      += $subtotal;
                $totalKeuntungan += $keuntungan;
            }

            // Buat header transaksi
            $transaction = Transaction::create([
                'kode_transaksi'   => Transaction::generateKode(),
                'total_modal'      => $totalModal,
                'total_harga'      => $totalHarga,
                'total_keuntungan' => $totalKeuntungan,
            ]);

            // Buat detail transaksi dan kurangi stok
            foreach ($items as $item) {
                $product   = Product::findOrFail($item['product_id']);
                $qty       = (int) $item['qty'];
                $hargaJual = (float) $item['harga_jual'];

                $subtotal   = $hargaJual * $qty;
                $modal      = $product->harga_modal * $qty;
                $keuntungan = $subtotal - $modal;

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id'     => $product->id,
                    'qty'            => $qty,
                    'harga_jual'     => $hargaJual,
                    'harga_modal'    => $product->harga_modal,
                    'subtotal'       => $subtotal,
                    'keuntungan'     => $keuntungan,
                    'created_at'     => now(),
                ]);

                // Kurangi stok produk
                $product->decrement('stok', $qty);
            }

            return $transaction;
        });
    }

    /**
     * Validasi stok sebelum menyimpan transaksi.
     *
     * @throws ValidationException
     */
    private function validateStock(array $items): void
    {
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);

            if (! $product) {
                throw ValidationException::withMessages([
                    'items' => ["Produk dengan ID {$item['product_id']} tidak ditemukan."],
                ]);
            }

            if ((int) $item['qty'] > $product->stok) {
                throw ValidationException::withMessages([
                    'items' => ["Stok {$product->nama_produk} tidak mencukupi. Stok tersedia: {$product->stok}"],
                ]);
            }

            if ((float) $item['harga_jual'] < 0) {
                throw ValidationException::withMessages([
                    'items' => ["Harga jual untuk {$product->nama_produk} tidak boleh negatif."],
                ]);
            }
        }
    }
}
