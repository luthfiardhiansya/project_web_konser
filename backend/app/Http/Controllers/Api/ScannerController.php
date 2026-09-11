<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IssuedTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScannerController extends Controller
{
    public function scan(Request $request)
    {
        $request->validate([
            'qr_token' => 'required|string',
        ]);

        $user = $request->user();

        // Hanya role scanner yang boleh melakukan scan
        if ($user->role !== 'scanner') {
            return response()->json([
                'status' => false,
                'message' => 'Anda tidak memiliki akses sebagai scanner.',
            ], 403);
        }

        DB::beginTransaction();

        try {

            // Lock tiket supaya tidak bisa dipakai dua kali
            $issuedTicket = IssuedTicket::with([
                'ticket.event',
                'order.user',
                'orderDetail'
            ])
                ->where('qr_token', $request->qr_token)
                ->lockForUpdate()
                ->first();

            // QR tidak ditemukan
            if (!$issuedTicket) {
                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'message' => 'QR Ticket tidak ditemukan.',
                ], 404);
            }

            // Tiket sudah digunakan
            if ($issuedTicket->status === 'used') {
                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'message' => 'Tiket ini sudah digunakan.',
                    'data' => [
                        'ticket_id' => $issuedTicket->id,
                        'status' => $issuedTicket->status,
                        'scanned_at' => $issuedTicket->scanned_at,
                    ],
                ], 409);
            }

            // Pastikan order memang sudah dibayar
            if ($issuedTicket->order->status !== 'dibayar') {
                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'message' => 'Tiket belum memiliki pembayaran yang valid.',
                ], 422);
            }

            // Tandai tiket sudah digunakan
            $issuedTicket->update([
                'status' => 'used',
                'scanned_at' => now(),
                'scanned_by' => $user->id,
            ]);

            DB::commit();

            // Ambil data terbaru
            $issuedTicket->load([
                'ticket.event',
                'order.user',
                'orderDetail',
                'scanner'
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Tiket valid. Berhasil digunakan.',
                'data' => [
                    'ticket_id' => $issuedTicket->id,
                    'status' => $issuedTicket->status,
                    'scanned_at' => $issuedTicket->scanned_at,
                    'scanned_by' => $issuedTicket->scanner?->name,

                    'event' => $issuedTicket->ticket->event->nama_event ?? null,
                    'jenis_tiket' => $issuedTicket->ticket->nama_tiket ?? null,
                    'kode_pesanan' => $issuedTicket->order->kode_pesanan ?? null,
                    'nama_pemesan' => $issuedTicket->order->user->name ?? null,
                    'email_pemesan' => $issuedTicket->order->user->email ?? null,
                ],
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Gagal memproses QR Ticket.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}