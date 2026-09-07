<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'metode_pembayaran',
        'jumlah_bayar',
        'bukti_pembayaran',
        'status',
        'dibayar_pada',
    ];

    protected $casts = [
        'jumlah_bayar' => 'decimal:2',
        'dibayar_pada' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}