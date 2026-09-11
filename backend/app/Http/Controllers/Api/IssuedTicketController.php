<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IssuedTicket;
use Illuminate\Http\Request;

class IssuedTicketController extends Controller
{
    /**
     * Ambil semua E-Ticket milik user yang sedang login
     */
    public function myTickets(Request $request)
    {
        $user = $request->user();

        $tickets = IssuedTicket::with([
            'ticket.event',
            'order',
            'orderDetail'
        ])
        ->whereHas('order', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->latest()
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'E-Ticket berhasil diambil.',
            'data' => $tickets,
        ]);
    }

    /**
     * [ADMIN] Ambil semua tiket yang sudah ter-scan (status = used)
     */
    public function scannedTickets(Request $request)
    {
        $query = IssuedTicket::with([
            'ticket.event',
            'order.user',
            'orderDetail',
            'scanner',
        ])
        ->where('status', 'used');

        // Filter tanggal scan dari
        if ($request->filled('date_from')) {
            $query->whereDate('scanned_at', '>=', $request->date_from);
        }

        // Filter tanggal scan sampai
        if ($request->filled('date_to')) {
            $query->whereDate('scanned_at', '<=', $request->date_to);
        }

        $tickets = $query->orderBy('scanned_at', 'desc')->get();

        return response()->json([
            'status'  => true,
            'message' => 'Riwayat scan berhasil diambil.',
            'data'    => $tickets,
        ]);
    }
}