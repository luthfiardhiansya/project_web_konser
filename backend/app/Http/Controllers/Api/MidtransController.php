<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\IssuedTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ETicketMail;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransController extends Controller
{
    public function createSnapToken(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::with('user')->findOrFail($request->order_id);

        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $params = [
            'transaction_details' => [
                'order_id' => $order->kode_pesanan . '-' . time(),
                'gross_amount' => (int) $order->total_harga,
            ],
            'customer_details' => [
                'first_name' => $order->user->name ?? 'Customer',
                'email' => $order->user->email ?? 'customer@example.com',
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);

            return response()->json([
                'success' => true,
                'message' => 'Snap token berhasil dibuat.',
                'snap_token' => $snapToken,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat Snap Token.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function finishPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'status' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $order = Order::with('orderDetails.ticket')
                ->findOrFail($request->order_id);

            $orderStatus = 'pending';
            $paymentStatus = 'pending';

            $statusInput = strtolower($request->status);

            if (in_array($statusInput, [
                'success',
                'settlement',
                'capture',
                'dibayar',
                'berhasil'
            ])) {
                $orderStatus = 'dibayar';
                $paymentStatus = 'berhasil';

            } elseif (in_array($statusInput, [
                'deny',
                'cancel',
                'expire',
                'gagal',
                'dibatalkan'
            ])) {
                $orderStatus = 'dibatalkan';
                $paymentStatus = 'gagal';
            }

            $order->update([
                'status' => $orderStatus
            ]);

            /*
            |--------------------------------------------------------------------------
            | UPDATE / CREATE PAYMENT
            |--------------------------------------------------------------------------
            */

            $payment = $order->payment;

            if (!$payment) {
                $payment = Payment::create([
                    'order_id' => $order->id,
                    'metode_pembayaran' => $request->metode_pembayaran
                        ?? 'Midtrans Payment',
                    'jumlah_bayar' => $order->total_harga,
                    'status' => $paymentStatus,
                    'dibayar_pada' => $paymentStatus === 'berhasil'
                        ? now()
                        : null,
                ]);
            } else {
                $payment->update([
                    'status' => $paymentStatus,
                    'dibayar_pada' => $paymentStatus === 'berhasil'
                        ? ($payment->dibayar_pada ?? now())
                        : $payment->dibayar_pada,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | BUAT E-TICKET / QR
            |--------------------------------------------------------------------------
            */

            if ($paymentStatus === 'berhasil') {

                foreach ($order->orderDetails as $detail) {

                    /*
                    |--------------------------------------------------------------------------
                    | Cegah QR dibuat dua kali
                    |--------------------------------------------------------------------------
                    */

                    $existingCount = IssuedTicket::where(
                        'order_detail_id',
                        $detail->id
                    )->count();

                    $jumlahSudahDibuat = $existingCount;
                    $jumlahTiket = (int) $detail->jumlah;

                    /*
                    |--------------------------------------------------------------------------
                    | Buat QR sesuai jumlah tiket
                    |--------------------------------------------------------------------------
                    */

                    for (
                        $i = $jumlahSudahDibuat;
                        $i < $jumlahTiket;
                        $i++
                    ) {

                        IssuedTicket::create([
                            'order_id' => $order->id,
                            'order_detail_id' => $detail->id,
                            'ticket_id' => $detail->ticket_id,
                            'qr_token' => (string) Str::uuid(),
                            'status' => 'valid',
                        ]);
                    }
                }
            }

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | KIRIM EMAIL E-TICKET
            |--------------------------------------------------------------------------
            */

            if ($paymentStatus === 'berhasil') {

                $order->load([
                    'user',
                    'orderDetails.ticket.event',
                    'payment',
                    'issuedTickets'
                ]);

                Mail::to($order->user->email)->send(
                    new ETicketMail($order)
                );
            }


            /*
            |--------------------------------------------------------------------------
            | RESPONSE
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'status' => true,
                'message' => 'Status pembayaran berhasil diperbarui.',
                'data' => $order->load([
                    'user',
                    'orderDetails.ticket.event',
                    'payment',
                    'issuedTickets'
                ]),
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Gagal memproses pembayaran.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}