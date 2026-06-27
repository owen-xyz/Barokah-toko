<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.product_id'     => ['required', 'integer', 'exists:products,id'],
            'items.*.qty'            => ['required', 'integer', 'min:1'],
            'items.*.harga_jual'     => ['required', 'numeric', 'min:0'],
            'items.*.jenis_harga'    => ['required', 'string', 'in:ecer,dropship,manual'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'              => 'Keranjang tidak boleh kosong.',
            'items.min'                   => 'Minimal harus ada 1 produk di keranjang.',
            'items.*.product_id.required' => 'ID produk wajib diisi.',
            'items.*.product_id.exists'   => 'Produk tidak ditemukan.',
            'items.*.qty.required'        => 'Qty wajib diisi.',
            'items.*.qty.min'             => 'Qty minimal 1.',
            'items.*.harga_jual.required' => 'Harga jual wajib diisi.',
            'items.*.harga_jual.min'      => 'Harga jual tidak boleh kurang dari 0.',
            'items.*.jenis_harga.in'      => 'Jenis harga tidak valid.',
        ];
    }
}
