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
        Schema::create('galeries', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->enum('tipe', ['foto', 'video'])->default('foto');
            $table->string('file_path');
            $table->string('thumbnail')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('kategori')->nullable(); // kegiatan, pernikahan, kajian
            $table->date('tanggal_kegiatan')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('galeries');
    }
};
