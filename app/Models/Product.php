<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_produk',
        'barcode',
        'nama_produk',
        'kategori',
        'harga_modal',
        'harga_ecer',
        'harga_dropship',
        'stok',
        'gambar',
    ];

    protected $casts = [
        'harga_modal'    => 'decimal:2',
        'harga_ecer'     => 'decimal:2',
        'harga_dropship' => 'decimal:2',
        'stok'           => 'integer',
    ];

    /**
     * Relasi ke detail transaksi.
     */
    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    /**
     * Scope pencarian produk berdasarkan nama, kode, atau kategori.
     */
    public function scopeSearch($query, string $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('nama_produk', 'like', "%{$keyword}%")
                ->orWhere('kode_produk', 'like', "%{$keyword}%")
                ->orWhere('kategori', 'like', "%{$keyword}%")
                ->orWhere('barcode', 'like', "%{$keyword}%");
        });
    }

    /**
     * Cari produk berdasarkan barcode persis (exact match).
     */
    public function scopeFindByBarcode($query, string $barcode)
    {
        return $query->where('barcode', $barcode);
    }

    /**
     * Ambil URL gambar produk.
     * Kolom `gambar` di DB hanya berisi nama file, misal: MG-001_1234567890.jpg
     * File fisik ada di: public/storage/products/MG-001_1234567890.jpg
     */
    public function getGambarUrlAttribute(): string
    {
        if ($this->gambar && \Storage::disk('public')->exists('products/'. $this->gambar)) {
            return asset('storage/products/'. $this->gambar);
        }

        return 'https://placehold.co/150x120/e8f4f8/2563eb?text=' . urlencode(substr($this->nama_produk, 0, 10));
    }
}
