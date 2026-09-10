<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestEmail;
use App\Models\Order;
use App\Mail\ETicketMail;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-email', function () {
    Mail::to('test@example.com')->send(new TestEmail());

    return 'Email berhasil dikirim!';
});

Route::get('/test-e-ticket/{id}', function ($id) {
    $order = Order::with([
        'user',
        'orderDetails.ticket.event',
        'payment',
        'issuedTickets'
    ])->findOrFail($id);

    Mail::to($order->user->email)->send(
        new ETicketMail($order)
    );

    return 'E-Ticket berhasil dikirim ke ' . $order->user->email;
});