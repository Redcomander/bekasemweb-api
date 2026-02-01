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
        Schema::create('antrians', function (Blueprint $table) {
            $table->id();
            $table->string('kode_antrian')->unique(); // A-001, B-002
            $table->date('tanggal');
            $table->enum('layanan', [
                'pendaftaran_nikah',
                'konsultasi',
                'legalisir',
                'bimwin',
                'lainnya'
            ]);
            $table->string('nama_pengunjung');
            $table->string('no_hp')->nullable();
            $table->text('keperluan')->nullable();
            $table->integer('nomor_urut');
            $table->enum('status', ['waiting', 'called', 'serving', 'completed', 'skipped'])->default('waiting');
            $table->timestamp('called_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('served_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            // Index for daily queue
            $table->index(['tanggal', 'layanan', 'nomor_urut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('antrians');
    }
};
