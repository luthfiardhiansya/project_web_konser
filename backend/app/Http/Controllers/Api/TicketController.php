<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::with('event')
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Data tiket berhasil diambil',
            'data' => $tickets,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'nama_tiket' => 'required|string|max:100',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
        ]);

        $ticket = Ticket::create([
            'event_id' => $request->event_id,
            'nama_tiket' => $request->nama_tiket,
            'harga' => $request->harga,
            'stok' => $request->stok,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Tiket berhasil ditambahkan',
            'data' => $ticket->load('event'),
        ], 201);
    }

    public function show(string $id)
    {
        $ticket = Ticket::with('event')->find($id);

        if (!$ticket) {
            return response()->json([
                'status' => false,
                'message' => 'Tiket tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Detail tiket berhasil diambil',
            'data' => $ticket,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json([
                'status' => false,
                'message' => 'Tiket tidak ditemukan',
            ], 404);
        }

        $request->validate([
            'event_id' => 'required|exists:events,id',
            'nama_tiket' => 'required|string|max:100',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
        ]);

        $ticket->update([
            'event_id' => $request->event_id,
            'nama_tiket' => $request->nama_tiket,
            'harga' => $request->harga,
            'stok' => $request->stok,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Tiket berhasil diperbarui',
            'data' => $ticket->load('event'),
        ]);
    }

    public function destroy(string $id)
    {
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json([
                'status' => false,
                'message' => 'Tiket tidak ditemukan',
            ], 404);
        }

        $ticket->delete();

        return response()->json([
            'status' => true,
            'message' => 'Tiket berhasil dihapus',
        ]);
    }
}