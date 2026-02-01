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
        Schema::create('majelis_taklims', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('penceramah');
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai')->nullable();
            $table->string('tempat')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('youtube_url')->nullable();
            $table->enum('status', ['upcoming', 'live', 'arsip'])->default('upcoming');
            $table->string('poster')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('majelis_taklims');
    }
};
