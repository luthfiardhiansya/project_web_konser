<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with([
            'user',
            'orderDetails.ticket.event',
            'payment'
        ]);

        $userId = $request->user_id ?? $request->user()?->id;

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $orders = $query->latest()->get();

        return response()->json([
            'status' => true,
            'message' => 'Data order berhasil diambil',
            'data' => $orders,
        ]);
    }

    public function store(Request $request)
    {
        $userId = $request->user_id ?? $request->user()?->id;

        if (!$request->has('items') && $request->has('ticket_id')) {
            $request->merge([
                'items' => [
                    [
                        'ticket_id' => $request->ticket_id,
                        'jumlah' => (int) ($request->jumlah ?? 1),
                    ]
                ]
            ]);
        }

        if ($userId) {
            $request->merge(['user_id' => $userId]);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.ticket_id' => 'required|exists:tickets,id',
            'items.*.jumlah' => 'required|integer|min:1',
        ]);

        try {
            $order = DB::transaction(function () use ($request) {

                $totalHarga = 0;
                $details = [];

                foreach ($request->items as $item) {

                    $ticket = Ticket::lockForUpdate()
                        ->findOrFail($item['ticket_id']);

                    $jumlah = $item['jumlah'];

                    if ($ticket->stok < $jumlah) {
                        throw new \Exception(
                            "Stok tiket {$ticket->nama_tiket} tidak mencukupi"
                        );
                    }

                    $hargaSatuan = $ticket->harga;
                    $subtotal = $hargaSatuan * $jumlah;

                    $totalHarga += $subtotal;

                    $details[] = [
                        'ticket_id' => $ticket->id,
                        'jumlah' => $jumlah,
                        'harga_satuan' => $hargaSatuan,
                        'subtotal' => $subtotal,
                    ];

                    $ticket->decrement('stok', $jumlah);
                }

                $order = Order::create([
                    'user_id' => $request->user_id,
                    'kode_pesanan' => 'ORD-' . strtoupper(Str::random(8)),
                    'total_harga' => $totalHarga,
                    'status' => 'pending',
                ]);

                foreach ($details as $detail) {
                    $order->orderDetails()->create($detail);
                }

                return $order;
            });

            return response()->json([
                'status' => true,
                'message' => 'Order berhasil dibuat',
                'data' => $order->load([
                    'user',
                    'orderDetails.ticket.event',
                    'payment'
                ]),
            ], 201);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function show(string $id)
    {
        $order = Order::with([
            'user',
            'orderDetails.ticket.event',
            'payment'
        ])->find($id);

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Detail order berhasil diambil',
            'data' => $order,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order tidak ditemukan',
            ], 404);
        }

        $request->validate([
            'status' => 'required|in:pending,dibayar,dibatalkan,selesai',
        ]);

        $order->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Status order berhasil diperbarui',
            'data' => $order,
        ]);
    }

    public function destroy(string $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order tidak ditemukan',
            ], 404);
        }

        $order->delete();

        return response()->json([
            'status' => true,
            'message' => 'Order berhasil dihapus',
        ]);
    }

    /**
     * Batalkan pesanan oleh user sendiri + kembalikan stok tiket.
     * POST /api/orders/{id}/cancel
     */
    public function cancel(string $id)
    {
        $order = Order::with('orderDetails.ticket')->find($id);

        if (!$order) {
            return response()->json([
                'status'  => false,
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'status'  => false,
                'message' => 'Hanya pesanan berstatus pending yang bisa dibatalkan.',
            ], 422);
        }

        DB::transaction(function () use ($order) {
            // Kembalikan stok tiket
            foreach ($order->orderDetails as $detail) {
                if ($detail->ticket) {
                    $detail->ticket->increment('stok', $detail->jumlah);
                }
            }

            $order->update(['status' => 'dibatalkan']);
        });

        return response()->json([
            'status'  => true,
            'message' => 'Pesanan berhasil dibatalkan dan stok tiket telah dikembalikan.',
            'data'    => $order->fresh(),
        ]);
    }

    /**
     * Auto-cancel pesanan pending yang sudah lebih dari 30 menit.
     * POST /api/orders/auto-cancel  (bisa dipanggil dari cron / frontend)
     */
    public function autoCancelExpired()
    {
        $expiredOrders = Order::with('orderDetails.ticket')
            ->where('status', 'pending')
            ->where('created_at', '<', now()->subMinutes(30))
            ->get();

        $cancelledCount = 0;

        foreach ($expiredOrders as $order) {
            DB::transaction(function () use ($order) {
                foreach ($order->orderDetails as $detail) {
                    if ($detail->ticket) {
                        $detail->ticket->increment('stok', $detail->jumlah);
                    }
                }
                $order->update(['status' => 'dibatalkan']);
            });
            $cancelledCount++;
        }

        return response()->json([
            'status'  => true,
            'message' => "{$cancelledCount} pesanan expired telah dibatalkan.",
            'count'   => $cancelledCount,
        ]);
    }
}
