<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'qty',
        'harga_jual',
        'harga_modal',
        'subtotal',
        'keuntungan',
        'created_at',
    ];

    protected $casts = [
        'harga_jual'  => 'decimal:2',
        'harga_modal' => 'decimal:2',
        'subtotal'    => 'decimal:2',
        'keuntungan'  => 'decimal:2',
        'qty'         => 'integer',
        'created_at'  => 'datetime',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
