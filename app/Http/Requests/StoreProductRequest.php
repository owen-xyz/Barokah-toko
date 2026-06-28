<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode_produk'  => ['required', 'string', 'max:50', 'unique:products,kode_produk'],
            'nama_produk'  => ['required', 'string', 'max:255'],
            'kategori'     => ['required', 'string', 'max:100'],
            'harga_modal'  => ['required', 'numeric', 'min:0'],
            'harga_ecer'   => ['required', 'numeric', 'min:0'],
            'harga_dropship' => ['required', 'numeric', 'min:0'],
            'stok'         => ['required', 'integer', 'min:0'],
            'gambar'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'kode_produk.required'   => 'Kode produk wajib diisi.',
            'kode_produk.unique'     => 'Kode produk sudah digunakan.',
            'nama_produk.required'   => 'Nama produk wajib diisi.',
            'kategori.required'      => 'Kategori wajib diisi.',
            'harga_modal.required'   => 'Harga modal wajib diisi.',
            'harga_ecer.required'    => 'Harga ecer wajib diisi.',
            'harga_dropship.required'=> 'Harga dropship wajib diisi.',
            'stok.required'          => 'Stok wajib diisi.',
            'gambar.image'           => 'File harus berupa gambar.',
            'gambar.max'             => 'Ukuran gambar maksimal 2MB.',
        ];
    }
}
