<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issued_tickets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignId('order_detail_id')
                ->constrained('order_details')
                ->cascadeOnDelete();

            $table->foreignId('ticket_id')
                ->constrained('tickets')
                ->cascadeOnDelete();

            // Token unik untuk QR setiap tiket
            $table->string('qr_token')->unique();

            // valid = belum digunakan
            // used = sudah digunakan
            $table->enum('status', ['valid', 'used'])
                ->default('valid');

            // Waktu tiket digunakan
            $table->timestamp('scanned_at')->nullable();

            // User dengan role scanner yang melakukan scan
            $table->foreignId('scanned_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issued_tickets');
    }
};