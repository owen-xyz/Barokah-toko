<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // Minyak Goreng
            ['kode_produk' => 'MG-001', 'nama_produk' => 'Minyak Goreng Filma Pouch 1lt', 'kategori' => 'Minyak Goreng', 'harga_modal' => 14000, 'harga_ecer' => 17000, 'harga_dropship' => 15500, 'stok' => 120],
            ['kode_produk' => 'MG-002', 'nama_produk' => 'Minyak Goreng Fortuna 2lt', 'kategori' => 'Minyak Goreng', 'harga_modal' => 31000, 'harga_ecer' => 36000, 'harga_dropship' => 33500, 'stok' => 416],
            ['kode_produk' => 'MG-003', 'nama_produk' => 'Minyak Goreng Sania 2lt', 'kategori' => 'Minyak Goreng', 'harga_modal' => 28000, 'harga_ecer' => 33000, 'harga_dropship' => 30500, 'stok' => 85],
            ['kode_produk' => 'MG-004', 'nama_produk' => 'Minyak Goreng Bimoli 2lt', 'kategori' => 'Minyak Goreng', 'harga_modal' => 29000, 'harga_ecer' => 34000, 'harga_dropship' => 31500, 'stok' => 60],
            ['kode_produk' => 'MG-005', 'nama_produk' => 'Minyak Goreng Tropical 2lt', 'kategori' => 'Minyak Goreng', 'harga_modal' => 27000, 'harga_ecer' => 32000, 'harga_dropship' => 29500, 'stok' => 75],

            // Sabun & Deterjen
            ['kode_produk' => 'SD-001', 'nama_produk' => 'So Klin Bio Matic 1600ml', 'kategori' => 'Sabun & Deterjen', 'harga_modal' => 36000, 'harga_ecer' => 41300, 'harga_dropship' => 38500, 'stok' => 200],
            ['kode_produk' => 'SD-002', 'nama_produk' => 'So Klin Liquid 1600ml', 'kategori' => 'Sabun & Deterjen', 'harga_modal' => 28000, 'harga_ecer' => 32000, 'harga_dropship' => 30000, 'stok' => 150],
            ['kode_produk' => 'SD-003', 'nama_produk' => 'Super Sol Karbol Wangi Pine 800ml', 'kategori' => 'Sabun & Deterjen', 'harga_modal' => 16000, 'harga_ecer' => 18400, 'harga_dropship' => 17000, 'stok' => 95],
            ['kode_produk' => 'SD-004', 'nama_produk' => 'Rinso Anti Noda 900gr', 'kategori' => 'Sabun & Deterjen', 'harga_modal' => 22000, 'harga_ecer' => 26000, 'harga_dropship' => 24000, 'stok' => 110],
            ['kode_produk' => 'SD-005', 'nama_produk' => 'Surf Deterjen Bubuk 900gr', 'kategori' => 'Sabun & Deterjen', 'harga_modal' => 18000, 'harga_ecer' => 21000, 'harga_dropship' => 19500, 'stok' => 80],

            // Susu & Minuman
            ['kode_produk' => 'SM-001', 'nama_produk' => 'Susu Ultra Milk Rasa Cokelat 1000ml', 'kategori' => 'Susu & Minuman', 'harga_modal' => 14500, 'harga_ecer' => 17500, 'harga_dropship' => 16000, 'stok' => 89],
            ['kode_produk' => 'SM-002', 'nama_produk' => 'Susu Ultra Milk Full Cream 1000ml', 'kategori' => 'Susu & Minuman', 'harga_modal' => 14500, 'harga_ecer' => 17500, 'harga_dropship' => 16000, 'stok' => 95],
            ['kode_produk' => 'SM-003', 'nama_produk' => 'Indomilk Susu Kental Manis 385gr', 'kategori' => 'Susu & Minuman', 'harga_modal' => 9500, 'harga_ecer' => 12000, 'harga_dropship' => 10500, 'stok' => 120],
            ['kode_produk' => 'SM-004', 'nama_produk' => 'Teh Botol Sosro 450ml', 'kategori' => 'Susu & Minuman', 'harga_modal' => 4000, 'harga_ecer' => 5500, 'harga_dropship' => 4700, 'stok' => 200],
            ['kode_produk' => 'SM-005', 'nama_produk' => 'Aqua Air Mineral 600ml', 'kategori' => 'Susu & Minuman', 'harga_modal' => 2500, 'harga_ecer' => 3500, 'harga_dropship' => 3000, 'stok' => 300],

            // Tissue & Kebersihan
            ['kode_produk' => 'TK-001', 'nama_produk' => 'Tissue Jolly 250 Sheet 2 Ply', 'kategori' => 'Tissue & Kebersihan', 'harga_modal' => 5500, 'harga_ecer' => 6800, 'harga_dropship' => 6100, 'stok' => 29],
            ['kode_produk' => 'TK-002', 'nama_produk' => 'Tissue Basah Mitu Baby 4 Sheets', 'kategori' => 'Tissue & Kebersihan', 'harga_modal' => 700, 'harga_ecer' => 950, 'harga_dropship' => 820, 'stok' => 500],
            ['kode_produk' => 'TK-003', 'nama_produk' => 'Tissue Basah Dettol 10 Sheets', 'kategori' => 'Tissue & Kebersihan', 'harga_modal' => 7500, 'harga_ecer' => 9300, 'harga_dropship' => 8400, 'stok' => 75],
            ['kode_produk' => 'TK-004', 'nama_produk' => 'Tissue Passeo 60 Sheets 3 Ply', 'kategori' => 'Tissue & Kebersihan', 'harga_modal' => 7200, 'harga_ecer' => 8900, 'harga_dropship' => 8000, 'stok' => 90],
            ['kode_produk' => 'TK-005', 'nama_produk' => 'Softex Pembalut Wanita Regular 12pcs', 'kategori' => 'Tissue & Kebersihan', 'harga_modal' => 8500, 'harga_ecer' => 10500, 'harga_dropship' => 9500, 'stok' => 60],

            // Obat & Kesehatan
            ['kode_produk' => 'OK-001', 'nama_produk' => 'Tolak Angin Cair 15ml', 'kategori' => 'Obat & Kesehatan', 'harga_modal' => 2800, 'harga_ecer' => 3500, 'harga_dropship' => 3100, 'stok' => 150],
            ['kode_produk' => 'OK-002', 'nama_produk' => 'Antangin JRG Sachet 15ml', 'kategori' => 'Obat & Kesehatan', 'harga_modal' => 2200, 'harga_ecer' => 3000, 'harga_dropship' => 2600, 'stok' => 130],
            ['kode_produk' => 'OK-003', 'nama_produk' => 'Panadol Tablet 500mg 10pcs', 'kategori' => 'Obat & Kesehatan', 'harga_modal' => 7000, 'harga_ecer' => 9000, 'harga_dropship' => 8000, 'stok' => 80],
            ['kode_produk' => 'OK-004', 'nama_produk' => 'Betadine Antiseptic 30ml', 'kategori' => 'Obat & Kesehatan', 'harga_modal' => 12000, 'harga_ecer' => 15000, 'harga_dropship' => 13500, 'stok' => 45],
            ['kode_produk' => 'OK-005', 'nama_produk' => 'Minyak Kayu Putih Cap Lang 60ml', 'kategori' => 'Obat & Kesehatan', 'harga_modal' => 14000, 'harga_ecer' => 17000, 'harga_dropship' => 15500, 'stok' => 55],

            // Snack & Makanan
            ['kode_produk' => 'SN-001', 'nama_produk' => 'Indomie Goreng Spesial', 'kategori' => 'Snack & Makanan', 'harga_modal' => 2800, 'harga_ecer' => 3500, 'harga_dropship' => 3100, 'stok' => 250],
            ['kode_produk' => 'SN-002', 'nama_produk' => 'Chitato Rasa Sapi Panggang 75gr', 'kategori' => 'Snack & Makanan', 'harga_modal' => 7000, 'harga_ecer' => 9000, 'harga_dropship' => 8000, 'stok' => 100],
            ['kode_produk' => 'SN-003', 'nama_produk' => 'Biskuat Energy Cokelat 100gr', 'kategori' => 'Snack & Makanan', 'harga_modal' => 5000, 'harga_ecer' => 7000, 'harga_dropship' => 6000, 'stok' => 120],
            ['kode_produk' => 'SN-004', 'nama_produk' => 'Good Time Choco Chip Cookies 72gr', 'kategori' => 'Snack & Makanan', 'harga_modal' => 9500, 'harga_ecer' => 12000, 'harga_dropship' => 10800, 'stok' => 85],
            ['kode_produk' => 'SN-005', 'nama_produk' => 'Gula Pasir Gulaku 1kg', 'kategori' => 'Snack & Makanan', 'harga_modal' => 14000, 'harga_ecer' => 17000, 'harga_dropship' => 15500, 'stok' => 200],
        ];

        foreach ($products as $product) {
            Product::create(array_merge($product, ['gambar' => null]));
        }
    }
}
