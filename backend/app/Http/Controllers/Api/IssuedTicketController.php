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
}