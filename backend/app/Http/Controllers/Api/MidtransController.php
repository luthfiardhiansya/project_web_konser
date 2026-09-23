<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ETicketMail;
use App\Models\IssuedTicket;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Notification;
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
                'order_id' => $order->kode_pesanan.'-'.time(),
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
                'berhasil',
            ])) {
                $orderStatus = 'dibayar';
                $paymentStatus = 'berhasil';

            } elseif (in_array($statusInput, [
                'deny',
                'cancel',
                'expire',
                'gagal',
                'dibatalkan',
            ])) {
                $orderStatus = 'dibatalkan';
                $paymentStatus = 'gagal';
            }

            $order->update([
                'status' => $orderStatus,
            ]);

            /*
            |--------------------------------------------------------------------------
            | UPDATE / CREATE PAYMENT
            |--------------------------------------------------------------------------
            */

            $payment = $order->payment;

            if (! $payment) {
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
                    'issuedTickets',
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
                    'issuedTickets',
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

    /**
     * Handle Midtrans server-to-server notification (webhook).
     *
     * Midtrans mengirim notifikasi ke URL ini setiap kali status transaksi berubah.
     * Ini memastikan order terupdate meskipun user menutup browser.
     */
    public function handleNotification(Request $request): JsonResponse
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        try {
            $notification = new Notification;

            $transactionStatus = $notification->transaction_status;
            $fraudStatus = $notification->fraud_status;
            $orderId = $notification->order_id;

            // order_id di Midtrans = kode_pesanan + '-' + timestamp
            // Ambil kode_pesanan dengan menghapus suffix timestamp
            $kodePesanan = preg_replace('/-\d+$/', '', $orderId);

            $order = Order::with('orderDetails.ticket')
                ->where('kode_pesanan', $kodePesanan)
                ->first();

            if (! $order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order tidak ditemukan.',
                ], 404);
            }

            // Jika order sudah dibayar, skip agar tidak proses ulang
            if ($order->status === 'dibayar') {
                return response()->json([
                    'status' => true,
                    'message' => 'Order sudah diproses sebelumnya.',
                ]);
            }

            DB::beginTransaction();

            $orderStatus = 'pending';
            $paymentStatus = 'pending';

            if ($transactionStatus === 'capture') {
                $orderStatus = $fraudStatus === 'accept' ? 'dibayar' : 'pending';
                $paymentStatus = $fraudStatus === 'accept' ? 'berhasil' : 'pending';
            } elseif ($transactionStatus === 'settlement') {
                $orderStatus = 'dibayar';
                $paymentStatus = 'berhasil';
            } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
                $orderStatus = 'dibatalkan';
                $paymentStatus = 'gagal';
            }

            $order->update(['status' => $orderStatus]);

            $payment = $order->payment;

            if (! $payment) {
                $payment = Payment::create([
                    'order_id' => $order->id,
                    'metode_pembayaran' => $notification->payment_type ?? 'Midtrans',
                    'jumlah_bayar' => $order->total_harga,
                    'status' => $paymentStatus,
                    'dibayar_pada' => $paymentStatus === 'berhasil' ? now() : null,
                ]);
            } else {
                $payment->update([
                    'status' => $paymentStatus,
                    'metode_pembayaran' => $notification->payment_type ?? $payment->metode_pembayaran,
                    'dibayar_pada' => $paymentStatus === 'berhasil'
                        ? ($payment->dibayar_pada ?? now())
                        : $payment->dibayar_pada,
                ]);
            }

            // Generate e-ticket jika pembayaran berhasil
            if ($paymentStatus === 'berhasil') {
                foreach ($order->orderDetails as $detail) {
                    $existingCount = IssuedTicket::where('order_detail_id', $detail->id)->count();
                    $jumlahTiket = (int) $detail->jumlah;

                    for ($i = $existingCount; $i < $jumlahTiket; $i++) {
                        IssuedTicket::create([
                            'order_id' => $order->id,
                            'order_detail_id' => $detail->id,
                            'ticket_id' => $detail->ticket_id,
                            'qr_token' => (string) Str::uuid(),
                            'status' => 'valid',
                        ]);
                    }
                }

                $order->load(['user', 'orderDetails.ticket.event', 'payment', 'issuedTickets']);

                Mail::to($order->user->email)->send(new ETicketMail($order));
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Notifikasi berhasil diproses.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Gagal memproses notifikasi.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
