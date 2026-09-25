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
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $user = $request->user();

        // =========================================================
        // HANYA ROLE SCANNER
        // =========================================================

        if ($user->role !== 'scanner') {
            return response()->json([
                'status' => false,
                'message' => 'Anda tidak memiliki akses sebagai scanner.',
            ], 403);
        }

        DB::beginTransaction();

        try {

            // =====================================================
            // CARI TIKET + LOCK
            // =====================================================

            $issuedTicket = IssuedTicket::with([
                'ticket.event',
                'order.user',
                'orderDetail'
            ])
                ->where('qr_token', $request->qr_token)
                ->lockForUpdate()
                ->first();

            if (!$issuedTicket) {
                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'message' => 'QR Ticket tidak ditemukan.',
                ], 404);
            }

            // =====================================================
            // CEK SUDAH DIGUNAKAN
            // =====================================================

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

            // =====================================================
            // CEK PEMBAYARAN
            // =====================================================

            if (!$issuedTicket->order) {
                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'message' => 'Data pesanan tiket tidak ditemukan.',
                ], 422);
            }

            if ($issuedTicket->order->status !== 'dibayar') {
                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'message' => 'Tiket belum memiliki pembayaran yang valid.',
                ], 422);
            }

            // =====================================================
            // CEK EVENT
            // =====================================================

            $event = $issuedTicket->ticket?->event;

            if (!$event) {
                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'message' => 'Event tiket tidak ditemukan.',
                ], 422);
            }

            // =====================================================
            // CEK KOORDINAT SCAN EVENT
            // =====================================================

            if (
                $event->scan_latitude === null ||
                $event->scan_longitude === null
            ) {
                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'message' => 'Lokasi scan event belum dikonfigurasi oleh admin.',
                ], 422);
            }

            // =====================================================
            // RADIUS CHECK-IN
            // =====================================================

            $radius = $event->radius_checkin
                ? (float) $event->radius_checkin
                : 30;

            // =====================================================
            // HITUNG JARAK GPS
            // =====================================================

            $scannerLatitude = (float) $request->latitude;
            $scannerLongitude = (float) $request->longitude;

            $scanLatitude = (float) $event->scan_latitude;
            $scanLongitude = (float) $event->scan_longitude;

            $distance = $this->calculateDistance(
                $scannerLatitude,
                $scannerLongitude,
                $scanLatitude,
                $scanLongitude
            );

            // =====================================================
            // DI LUAR RADIUS
            // =====================================================

            if ($distance > $radius) {

                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'message' => 'lokasi di luar jangkauan titik sacn',
                    'data' => [
                        'distance' => round($distance, 2),
                        'radius' => $radius,
                        'unit' => 'meter',
                    ],
                ], 403);
            }

            // =====================================================
            // SEMUA VALID
            // =====================================================

            $issuedTicket->update([
                'status' => 'used',
                'scanned_at' => now(),
                'scanned_by' => $user->id,
            ]);

            DB::commit();

            // =====================================================
            // LOAD DATA TERBARU
            // =====================================================

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

                    'event' =>
                        $issuedTicket->ticket->event->nama_event
                        ?? null,

                    'jenis_tiket' =>
                        $issuedTicket->ticket->nama_tiket
                        ?? null,

                    'kode_pesanan' =>
                        $issuedTicket->order->kode_pesanan
                        ?? null,

                    'nama_pemesan' =>
                        $issuedTicket->order->user->name
                        ?? null,

                    'email_pemesan' =>
                        $issuedTicket->order->user->email
                        ?? null,

                    'distance' =>
                        round($distance, 2),

                    'radius' =>
                        $radius,
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

    /**
     * Menghitung jarak dua koordinat GPS
     * menggunakan rumus Haversine.
     *
     * Hasil dalam meter.
     */
    private function calculateDistance(
        float $latitude1,
        float $longitude1,
        float $latitude2,
        float $longitude2
    ): float {

        $earthRadius = 6371000;

        $latFrom = deg2rad($latitude1);
        $lonFrom = deg2rad($longitude1);

        $latTo = deg2rad($latitude2);
        $lonTo = deg2rad($longitude2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a =
            sin($latDelta / 2) ** 2 +
            cos($latFrom) *
            cos($latTo) *
            sin($lonDelta / 2) ** 2;

        $c = 2 * atan2(
            sqrt($a),
            sqrt(1 - $a)
        );

        return $earthRadius * $c;
    }
}
