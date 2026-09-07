<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with('order')
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Data pembayaran berhasil diambil',
            'data' => $payments,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'metode_pembayaran' => 'required|string|max:100',
            'jumlah_bayar' => 'required|numeric|min:0',
            'bukti_pembayaran' => 'nullable|string|max:255',
            'status' => 'nullable|in:pending,berhasil,gagal',
        ]);

        $order = Order::findOrFail($request->order_id);

        if ($request->jumlah_bayar != $order->total_harga) {
            return response()->json([
                'status' => false,
                'message' => 'Jumlah pembayaran tidak sesuai dengan total order',
            ], 422);
        }

        $payment = Payment::create([
            'order_id' => $request->order_id,
            'metode_pembayaran' => $request->metode_pembayaran,
            'jumlah_bayar' => $request->jumlah_bayar,
            'bukti_pembayaran' => $request->bukti_pembayaran,
            'status' => $request->status ?? 'pending',
            'dibayar_pada' => null,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Pembayaran berhasil dibuat',
            'data' => $payment->load('order'),
        ], 201);
    }

    public function show(string $id)
    {
        $payment = Payment::with('order')->find($id);

        if (!$payment) {
            return response()->json([
                'status' => false,
                'message' => 'Pembayaran tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Detail pembayaran berhasil diambil',
            'data' => $payment,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json([
                'status' => false,
                'message' => 'Pembayaran tidak ditemukan',
            ], 404);
        }

        $request->validate([
            'metode_pembayaran' => 'required|string|max:100',
            'jumlah_bayar' => 'required|numeric|min:0',
            'bukti_pembayaran' => 'nullable|string|max:255',
            'status' => 'required|in:pending,berhasil,gagal',
        ]);

        $dibayarPada = $payment->dibayar_pada;

        if ($request->status === 'berhasil') {
            $dibayarPada = now();
        }

        $payment->update([
            'metode_pembayaran' => $request->metode_pembayaran,
            'jumlah_bayar' => $request->jumlah_bayar,
            'bukti_pembayaran' => $request->bukti_pembayaran,
            'status' => $request->status,
            'dibayar_pada' => $dibayarPada,
        ]);

        if ($request->status === 'berhasil') {
            $payment->order()->update([
                'status' => 'dibayar',
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Pembayaran berhasil diperbarui',
            'data' => $payment->load('order'),
        ]);
    }

    public function destroy(string $id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json([
                'status' => false,
                'message' => 'Pembayaran tidak ditemukan',
            ], 404);
        }

        $payment->delete();

        return response()->json([
            'status' => true,
            'message' => 'Pembayaran berhasil dihapus',
        ]);
    }
}