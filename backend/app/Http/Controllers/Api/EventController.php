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
            'poster' => 'nullable|string|max:255',
            'status' => 'nullable|in:aktif,selesai,dibatalkan',
        ]);

        $event = Event::create([
            'category_id' => $request->category_id,
            'nama_event' => $request->nama_event,
            'deskripsi' => $request->deskripsi,
            'tanggal' => $request->tanggal,
            'waktu' => $request->waktu,
            'lokasi' => $request->lokasi,
            'alamat' => $request->alamat,
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
            'poster' => 'nullable|string|max:255',
            'status' => 'required|in:aktif,selesai,dibatalkan',
        ]);

        $event->update([
            'category_id' => $request->category_id,
            'nama_event' => $request->nama_event,
            'deskripsi' => $request->deskripsi,
            'tanggal' => $request->tanggal,
            'waktu' => $request->waktu,
            'lokasi' => $request->lokasi,
            'alamat' => $request->alamat,
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