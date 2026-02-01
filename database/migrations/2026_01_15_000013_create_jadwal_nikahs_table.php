<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jadwal_nikahs', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai')->nullable();
            $table->foreignId('penghulu_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('pendaftaran_id')->nullable()->constrained('pendaftaran_nikahs')->nullOnDelete();
            $table->string('lokasi');
            $table->text('catatan')->nullable();
            $table->enum('status', ['available', 'booked', 'completed', 'cancelled'])->default('available');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_nikahs');
    }
};
