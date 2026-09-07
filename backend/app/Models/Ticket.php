<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'nama_tiket',
        'harga',
        'stok',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }
}