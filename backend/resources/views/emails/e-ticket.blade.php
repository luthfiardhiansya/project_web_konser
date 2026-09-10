<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <title>E-Ticket InfoMusikBDG</title>
</head>

<body style="
    margin:0;
    padding:30px;
    background:#f5f5f5;
    font-family:Arial, Helvetica, sans-serif;
">

<div style="
    max-width:600px;
    margin:0 auto;
    background:#ffffff;
    padding:30px;
    border:1px solid #dddddd;
">

    {{-- HEADER --}}
    <div style="
        text-align:center;
        margin-bottom:30px;
    ">

        <h1 style="
            margin:0;
            font-size:28px;
        ">
            InfoMusikBDG
        </h1>

        <p style="
            margin-top:8px;
            color:#666666;
        ">
            Platform tiket event musik Bandung
        </p>

    </div>


    {{-- TITLE --}}

    <h2 style="
        margin-bottom:10px;
    ">
        E-Ticket Kamu 🎟️
    </h2>

    <p>
        Halo {{ $order->user->name ?? 'User' }},
    </p>

    <p>
        Pembayaran kamu berhasil.
        Berikut adalah detail pesanan tiket kamu.
    </p>


    <hr style="
        border:0;
        border-top:1px solid #dddddd;
        margin:25px 0;
    ">


    {{-- DETAIL PESANAN --}}

    <h3>
        Detail Pesanan
    </h3>

    <p>
        <strong>Kode Pesanan</strong><br>

        {{ $order->kode_pesanan }}
    </p>

    <p>
        <strong>Status Pesanan</strong><br>

        {{ ucfirst($order->status) }}
    </p>

    <p>
        <strong>Total Pembayaran</strong><br>

        Rp {{ number_format($order->total_harga, 0, ',', '.') }}
    </p>


    {{-- PAYMENT --}}

    @if($order->payment)

        <p>
            <strong>Metode Pembayaran</strong><br>

            {{ $order->payment->metode_pembayaran }}
        </p>

        <p>
            <strong>Status Pembayaran</strong><br>

            {{ ucfirst($order->payment->status) }}
        </p>

    @endif


    <hr style="
        border:0;
        border-top:1px solid #dddddd;
        margin:25px 0;
    ">


    {{-- DETAIL TIKET --}}

    <h3>
        Detail Tiket
    </h3>


    @foreach ($order->orderDetails as $detail)

        <div style="
            border:1px solid #dddddd;
            padding:18px;
            margin-bottom:15px;
        ">

            <h3 style="
                margin-top:0;
            ">
                {{ $detail->ticket->event->nama_event ?? 'Event' }}
            </h3>


            <p>
                <strong>Jenis Tiket:</strong><br>

                {{ $detail->ticket->nama_tiket ?? '-' }}
            </p>


            <p>
                <strong>Jumlah:</strong><br>

                {{ $detail->jumlah }}
            </p>


            <p>
                <strong>Harga:</strong><br>

                Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}
            </p>


            <p>
                <strong>Tanggal:</strong><br>

                {{ $detail->ticket->event->tanggal ?? '-' }}
            </p>


            <p>
                <strong>Waktu:</strong><br>

                {{ $detail->ticket->event->waktu ?? '-' }}
            </p>


            <p style="
                margin-bottom:0;
            ">
                <strong>Lokasi:</strong><br>

                {{ $detail->ticket->event->lokasi ?? '-' }}
            </p>

        </div>

    @endforeach


    <hr style="
        border:0;
        border-top:1px solid #dddddd;
        margin:25px 0;
    ">


    {{-- QR CODE --}}

    <h3 style="
        text-align:center;
    ">
        QR E-Ticket
    </h3>


    <p style="
        text-align:center;
        color:#555555;
    ">
        Tunjukkan QR Code berikut saat masuk ke event.
    </p>


    @foreach ($order->issuedTickets as $ticket)

        <div style="
            text-align:center;
            border:1px solid #dddddd;
            padding:20px;
            margin:20px 0;
        ">

            <p>
                <strong>
                    Tiket #{{ $loop->iteration }}
                </strong>
            </p>


            {{-- QR CODE --}}

            <img
                src="{{ $qrCodes[$ticket->id] }}"
                width="250"
                height="250"
                alt="QR E-Ticket"
                style="
                    display:block;
                    margin:15px auto;
                "
            >


            <p style="
                font-size:11px;
                color:#777777;
                word-break:break-all;
            ">
                {{ $ticket->qr_token }}
            </p>


            <p style="
                font-size:12px;
                color:#555555;
            ">
                Status tiket:
                <strong>
                    {{ ucfirst($ticket->status) }}
                </strong>
            </p>

        </div>

    @endforeach


    <hr style="
        border:0;
        border-top:1px solid #dddddd;
        margin:25px 0;
    ">


    {{-- FOOTER --}}

    <p style="
        color:#555555;
        font-size:14px;
    ">
        Simpan email ini sebagai bukti pembelian tiket kamu.
    </p>


    <p style="
        margin-bottom:0;
    ">
        <strong>
            InfoMusikBDG
        </strong>
        <br>

        Platform tiket event musik Bandung
    </p>

</div>

</body>
</html>