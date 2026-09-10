<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

class ETicketMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public array $qrCodes = [];

    public function __construct(Order $order)
    {
        $this->order = $order;

        foreach ($order->issuedTickets as $ticket) {

            $builder = new Builder(
                writer: new PngWriter(),
                data: $ticket->qr_token,
                size: 250,
                margin: 10,
            );

            $result = $builder->build();

            $this->qrCodes[$ticket->id] = $result->getDataUri();
        }
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'E-Ticket InfoMusikBDG - ' . $this->order->kode_pesanan,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.e-ticket',
            with: [
                'order' => $this->order,
                'qrCodes' => $this->qrCodes,
            ],
        );
    }
}