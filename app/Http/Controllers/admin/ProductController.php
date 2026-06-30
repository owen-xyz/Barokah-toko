<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService
    ) {}

    /**
     * Halaman daftar produk admin.
     */
    public function index(Request $request): View
    {
        $keyword  = $request->input('search', '');
        $kategori = $request->input('kategori', '');

        $products = Product::when($keyword,  fn ($q) => $q->search($keyword))
            ->when($kategori, fn ($q) => $q->where('kategori', $kategori))
            ->orderBy('nama_produk')
            ->paginate(12)
            ->withQueryString();

        $kategoris = Product::select('kategori')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori');

        return view('admin.products.index', compact('products', 'kategoris', 'keyword', 'kategori'));
    }

    /**
     * Simpan produk baru (AJAX).
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            $product = $this->productService->store(
                $request->except('gambar'),
                $request->file('gambar')
            );

            return response()->json([
                'success' => true,
                'message' => "Produk {$product->nama_produk} berhasil ditambahkan.",
                'data'    => $this->formatProduct($product),
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan produk: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ambil data produk untuk form edit (AJAX).
     */
    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->formatProduct($product),
        ]);
    }

    /**
     * Update produk (AJAX).
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        try {
            $updated = $this->productService->update(
                $product,
                $request->except('gambar'),
                $request->file('gambar')
            );

            return response()->json([
                'success' => true,
                'message' => "Produk {$updated->nama_produk} berhasil diperbarui.",
                'data'    => $this->formatProduct($updated),
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui produk: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Hapus produk (AJAX).
     */
    public function destroy(Product $product): JsonResponse
    {
        try {
            $nama = $product->nama_produk;
            $this->productService->destroy($product);

            return response()->json([
                'success' => true,
                'message' => "Produk {$nama} berhasil dihapus.",
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus produk: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Format data produk untuk respons JSON.
     */
    private function formatProduct(Product $product): array
    {
        return [
            'id'             => $product->id,
            'kode_produk'    => $product->kode_produk,
            'barcode'        => $product->barcode,
            'nama_produk'    => $product->nama_produk,
            'kategori'       => $product->kategori,
            'harga_modal'    => (float) $product->harga_modal,
            'harga_ecer'     => (float) $product->harga_ecer,
            'harga_dropship' => (float) $product->harga_dropship,
            'stok'           => $product->stok,
            'gambar'         => $product->gambar
        ];
    }
}
