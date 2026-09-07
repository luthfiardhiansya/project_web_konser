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
                ->cascadeOnDelete();

            $table->string('nama_event');
            $table->text('deskripsi');
            $table->date('tanggal');
            $table->time('waktu');
            $table->string('lokasi');
            $table->string('alamat')->nullable();
            $table->string('poster')->nullable();

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