<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MidtransTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_finish_payment_successfully(): void
    {
        $user = User::factory()->create();

        $event = Event::create([
            'nama_event' => 'Test Concert',
            'deskripsi' => 'Deskripsi test',
            'tanggal' => '2026-10-10',
            'waktu' => '19:00',
            'lokasi' => 'Bandung',
            'alamat' => 'Jl. Test',
            'status' => 'aktif',
        ]);

        $ticket = Ticket::create([
            'event_id' => $event->id,
            'nama_tiket' => 'VIP Test',
            'harga' => 50000,
            'stok' => 10,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'kode_pesanan' => 'ORD-TEST1234',
            'total_harga' => 50000,
            'status' => 'pending',
        ]);

        $order->orderDetails()->create([
            'ticket_id' => $ticket->id,
            'jumlah' => 1,
            'harga_satuan' => 50000,
            'subtotal' => 50000,
        ]);

        $response = $this->postJson('/api/payments/finish', [
            'order_id' => $order->id,
            'status' => 'berhasil',
            'metode_pembayaran' => 'gopay',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'dibayar',
        ]);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => 'berhasil',
            'metode_pembayaran' => 'gopay',
        ]);

        $this->assertDatabaseHas('issued_tickets', [
            'order_id' => $order->id,
            'ticket_id' => $ticket->id,
            'status' => 'valid',
        ]);
    }

    public function test_finish_payment_with_failure_status(): void
    {
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'kode_pesanan' => 'ORD-TEST5678',
            'total_harga' => 50000,
            'status' => 'pending',
        ]);

        $response = $this->postJson('/api/payments/finish', [
            'order_id' => $order->id,
            'status' => 'gagal',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'dibatalkan',
        ]);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => 'gagal',
        ]);
    }
}
