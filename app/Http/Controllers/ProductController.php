<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Daftar produk dengan pagination (untuk halaman POS).
     */
    public function index(Request $request): JsonResponse
    {
        $products = Product::orderBy('nama_produk')
            ->paginate(12);

        return response()->json([
            'success' => true,
            'data'    => $products,
        ]);
    }

    /**
     * Pencarian produk realtime (nama, kode, kategori).
     */
    public function search(Request $request): JsonResponse
    {
        $keyword = $request->input('q', '');

        $products = Product::when($keyword, fn($q) => $q->search($keyword))
            ->orderBy('nama_produk')
            ->paginate(12);

        return response()->json([
            'success' => true,
            'data'    => $products,
        ]);
    }

    /**
     * Cari produk berdasarkan barcode (exact match) — untuk hasil scan.
     */
    public function scanBarcode(Request $request): JsonResponse
    {
        $barcode = trim($request->input('barcode', ''));

        if (empty($barcode)) {
            return response()->json([
                'success' => false,
                'message' => 'Barcode tidak boleh kosong.',
            ], 422);
        }

        $product = Product::findByBarcode($barcode)->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => "Produk dengan barcode \"{$barcode}\" tidak ditemukan.",
            ], 404);
        }

        if ($product->stok <= 0) {
            return response()->json([
                'success' => false,
                'message' => "Stok {$product->nama_produk} habis.",
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data'    => $product,
        ]);
    }
}
