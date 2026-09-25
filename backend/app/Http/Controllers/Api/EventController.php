<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with(['category', 'tickets'])
            ->withSum('orderDetails as total_sold', 'jumlah')
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Data event berhasil diambil',
            'data' => $events,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'nama_event' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'tanggal' => 'required|date',
            'waktu' => 'required',

            'lokasi' => 'required|string|max:255',
            'alamat' => 'nullable|string|max:255',

            // Lokasi venue utama
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',

            // Lokasi khusus scan/check-in
            'scan_latitude' => 'nullable|numeric|between:-90,90',
            'scan_longitude' => 'nullable|numeric|between:-180,180',

            // Radius check-in dalam meter
            'radius_checkin' => 'nullable|integer|min:1',

            'poster' => 'nullable|string|max:255',
            'status' => 'nullable|in:aktif,selesai,dibatalkan',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Jika lokasi scan tidak dikirim
        |--------------------------------------------------------------------------
        | Otomatis gunakan lokasi venue sebagai lokasi scan.
        |
        | Jadi ketika admin baru membuat event:
        | scan_latitude  = latitude venue
        | scan_longitude = longitude venue
        |
        | Setelah itu admin masih bisa menggesernya melalui map.
        */

        $scanLatitude = $request->scan_latitude ?? $request->latitude;
        $scanLongitude = $request->scan_longitude ?? $request->longitude;

        $event = Event::create([
            'category_id' => $request->category_id,
            'nama_event' => $request->nama_event,
            'deskripsi' => $request->deskripsi,
            'tanggal' => $request->tanggal,
            'waktu' => $request->waktu,

            // Venue
            'lokasi' => $request->lokasi,
            'alamat' => $request->alamat,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,

            // Lokasi scan
            'scan_latitude' => $scanLatitude,
            'scan_longitude' => $scanLongitude,

            // Radius
            'radius_checkin' => $request->radius_checkin ?? 30,

            'poster' => $request->poster,
            'status' => $request->status ?? 'aktif',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Event berhasil ditambahkan',
            'data' => $event->load(['category', 'tickets']),
        ], 201);
    }

    public function show(string $id)
    {
        $event = Event::with(['category', 'tickets'])->find($id);

        if (!$event) {
            return response()->json([
                'status' => false,
                'message' => 'Event tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Detail event berhasil diambil',
            'data' => $event,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json([
                'status' => false,
                'message' => 'Event tidak ditemukan',
            ], 404);
        }

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'nama_event' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'tanggal' => 'required|date',
            'waktu' => 'required',

            'lokasi' => 'required|string|max:255',
            'alamat' => 'nullable|string|max:255',

            // Lokasi venue utama
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',

            // Lokasi khusus scan/check-in
            'scan_latitude' => 'nullable|numeric|between:-90,90',
            'scan_longitude' => 'nullable|numeric|between:-180,180',

            // Radius check-in
            'radius_checkin' => 'nullable|integer|min:1',

            'poster' => 'nullable|string|max:255',
            'status' => 'required|in:aktif,selesai,dibatalkan',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Pertahankan lokasi scan jika tidak dikirim
        |--------------------------------------------------------------------------
        | Kalau admin hanya mengubah nama event atau data lainnya,
        | lokasi scan yang sudah dipilih sebelumnya tidak boleh hilang.
        */

        $scanLatitude = $request->has('scan_latitude')
            ? $request->scan_latitude
            : $event->scan_latitude;

        $scanLongitude = $request->has('scan_longitude')
            ? $request->scan_longitude
            : $event->scan_longitude;

        /*
        |--------------------------------------------------------------------------
        | Fallback
        |--------------------------------------------------------------------------
        | Untuk event lama yang belum memiliki lokasi scan,
        | gunakan lokasi venue.
        */

        if ($scanLatitude === null && $request->latitude !== null) {
            $scanLatitude = $request->latitude;
        }

        if ($scanLongitude === null && $request->longitude !== null) {
            $scanLongitude = $request->longitude;
        }

        $event->update([
            'category_id' => $request->category_id,
            'nama_event' => $request->nama_event,
            'deskripsi' => $request->deskripsi,
            'tanggal' => $request->tanggal,
            'waktu' => $request->waktu,

            // Venue
            'lokasi' => $request->lokasi,
            'alamat' => $request->alamat,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,

            // Lokasi scan
            'scan_latitude' => $scanLatitude,
            'scan_longitude' => $scanLongitude,

            // Radius
            'radius_checkin' => $request->radius_checkin ?? $event->radius_checkin ?? 30,

            'poster' => $request->poster,
            'status' => $request->status,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Event berhasil diperbarui',
            'data' => $event->load(['category', 'tickets']),
        ]);
    }

    public function destroy(string $id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json([
                'status' => false,
                'message' => 'Event tidak ditemukan',
            ], 404);
        }

        $event->delete();

        return response()->json([
            'status' => true,
            'message' => 'Event berhasil dihapus',
        ]);
    }
}