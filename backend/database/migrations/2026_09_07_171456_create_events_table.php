<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                ->constrained('categories')
                ->onDelete('cascade');
            $table->string('nama_event');
            $table->text('deskripsi');
            $table->date('tanggal');
            $table->string('waktu');
            $table->string('lokasi');
            $table->text('alamat')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Koordinat lokasi scan/check-in
            $table->decimal('scan_latitude', 10, 7)->nullable();
            $table->decimal('scan_longitude', 10, 7)->nullable();

            // Radius default check-in dalam meter
            $table->unsignedInteger('radius_checkin')->default(30);

            $table->text('poster')->nullable();

            $table->enum('status', [
                'aktif',
                'selesai',
                'dibatalkan'
            ])->default('aktif');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};