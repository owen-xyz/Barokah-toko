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
        $products = Product::orderBy('id', 'asc')
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

        $products = Product::when($keyword, fn ($q) => $q->search($keyword))
            ->orderBy('nama_produk')
            ->paginate(12);

        return response()->json([
            'success' => true,
            'data'    => $products,
        ]);
    }
}
