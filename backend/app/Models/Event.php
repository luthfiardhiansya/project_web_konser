<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'nama_event',
        'deskripsi',
        'tanggal',
        'waktu',
        'lokasi',
        'alamat',
        'latitude',
        'longitude',

        // Lokasi scan / check-in
        'scan_latitude',
        'scan_longitude',

        // Radius check-in dalam meter
        'radius_checkin',

        'poster',
        'status',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'scan_latitude' => 'float',
        'scan_longitude' => 'float',
        'radius_checkin' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function orderDetails()
    {
        return $this->hasManyThrough(OrderDetail::class, Ticket::class);
    }
}