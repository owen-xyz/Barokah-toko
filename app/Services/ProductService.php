<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    /**
     * Simpan produk baru beserta gambarnya.
     */
    public function store(array $data, ?UploadedFile $image = null): Product
    {
        if ($image) {
            $data['gambar'] = $this->uploadImage($image, $data['kode_produk']);
        }

        return Product::create($data);
    }

    /**
     * Update data produk. Jika ada gambar baru, hapus yang lama dulu.
     */
    public function update(Product $product, array $data, ?UploadedFile $image = null): Product
    {
        if ($image) {
            // Hapus gambar lama dari storage
            $this->deleteImage($product->gambar);

            // Upload gambar baru
            $data['gambar'] = $this->uploadImage($image, $data['kode_produk']);
        }

        $product->update($data);

        return $product->fresh();
    }

    /**
     * Hapus produk beserta gambarnya.
     */
    public function destroy(Product $product): void
    {
        $this->deleteImage($product->gambar);
        $product->delete();
    }

    /**
     * Upload gambar ke storage/app/public/products/
     * Kembalikan path relatif untuk disimpan ke DB: storage/products/filename.jpg
     */
    private function uploadImage(UploadedFile $file, string $kode): string
    {
        $url = 'storage/products/';
        $filename = $kode . '_' . time() . '.' . $file->extension();

        // Simpan fisik ke public/storage/products/
        $file->storeAs('products', $filename, 'public');

        // Yang disimpan di DB hanya nama file saja
        return $url . $filename;
    }

    /**
     * Hapus file gambar dari storage jika ada.
     */
    private function deleteImage(?string $gambar): void
    {
        // dd($gambar);
        if (!$gambar) return;




        $file = Str::after($gambar, 'storage/');

        if (Storage::disk('public')->exists($file)) {
            Storage::disk('public')->delete($file);
        } else {
            dd('gagal menghapus file' . ':' . $file);
        }
    }
}
