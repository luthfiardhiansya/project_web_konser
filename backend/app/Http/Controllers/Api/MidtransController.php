<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
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

        $order = Order::findOrFail($request->order_id);

        $orderStatus = 'pending';
        $paymentStatus = 'pending';
        $statusInput = strtolower($request->status);

        if (in_array($statusInput, ['success', 'settlement', 'capture', 'dibayar', 'berhasil'])) {
            $orderStatus = 'dibayar';
            $paymentStatus = 'berhasil';
        } elseif (in_array($statusInput, ['deny', 'cancel', 'expire', 'gagal', 'dibatalkan'])) {
            $orderStatus = 'dibatalkan';
            $paymentStatus = 'gagal';
        }

        $order->update(['status' => $orderStatus]);

        $payment = $order->payment;
        if (!$payment) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'metode_pembayaran' => $request->metode_pembayaran ?? 'Midtrans Payment',
                'jumlah_bayar' => $order->total_harga,
                'status' => $paymentStatus,
                'dibayar_pada' => $paymentStatus === 'berhasil' ? now() : null,
            ]);
        } else {
            $payment->update([
                'status' => $paymentStatus,
                'dibayar_pada' => $paymentStatus === 'berhasil' ? now() : $payment->dibayar_pada,
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Status pembayaran berhasil diperbarui',
            'data' => $order->load(['user', 'orderDetails.ticket.event', 'payment']),
        ]);
    }
}