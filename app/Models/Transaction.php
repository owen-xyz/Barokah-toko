<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_transaksi',
        'total_modal',
        'total_harga',
        'total_keuntungan',
    ];

    protected $casts = [
        'total_modal'      => 'decimal:2',
        'total_harga'      => 'decimal:2',
        'total_keuntungan' => 'decimal:2',
    ];

    /**
     * Relasi ke detail transaksi.
     */
    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    /**
     * Generate kode transaksi unik.
     * Format: TRX-YYYYMMDD-XXXXX
     */
    public static function generateKode(): string
    {
        $prefix = 'TRX-' . date('Ymd') . '-';
        $last   = static::where('kode_transaksi', 'like', $prefix . '%')
                         ->orderBy('id', 'desc')
                         ->first();

        $number = $last
            ? (int) substr($last->kode_transaksi, -5) + 1
            : 1;

        return $prefix . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
