<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->string('metode_pembayaran');
            $table->decimal('jumlah_bayar', 12, 2);

            $table->string('bukti_pembayaran')->nullable();

            $table->enum('status', [
                'pending',
                'berhasil',
                'gagal'
            ])->default('pending');

            $table->timestamp('dibayar_pada')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};