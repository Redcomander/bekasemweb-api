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
        Schema::create('pendaftaran_nikahs', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pendaftaran')->unique(); // PN-2026-0001
            
            // Data Calon Suami
            $table->string('nama_pria');
            $table->string('nik_pria', 16);
            $table->string('tempat_lahir_pria');
            $table->date('tanggal_lahir_pria');
            $table->text('alamat_pria');
            $table->string('no_hp_pria');
            $table->string('pekerjaan_pria')->nullable();
            $table->enum('status_pria', ['jejaka', 'duda'])->default('jejaka');
            
            // Data Calon Istri
            $table->string('nama_wanita');
            $table->string('nik_wanita', 16);
            $table->string('tempat_lahir_wanita');
            $table->date('tanggal_lahir_wanita');
            $table->text('alamat_wanita');
            $table->string('no_hp_wanita');
            $table->string('pekerjaan_wanita')->nullable();
            $table->enum('status_wanita', ['perawan', 'janda'])->default('perawan');
            
            // Data Wali
            $table->string('nama_wali');
            $table->string('hubungan_wali'); // ayah kandung, kakak, paman
            $table->string('no_hp_wali')->nullable();
            
            // Rencana Nikah
            $table->date('tanggal_nikah');
            $table->time('jam_nikah')->nullable();
            $table->string('lokasi_nikah'); // di KUA / di luar KUA
            $table->text('alamat_nikah')->nullable();
            $table->decimal('mahar', 15, 2)->nullable();
            $table->string('mahar_keterangan')->nullable(); // seperangkat alat sholat, dll
            
            // Status
            $table->enum('status', [
                'diajukan',      // Baru submit
                'verifikasi',    // Sedang diverifikasi admin
                'revisi',        // Perlu revisi dokumen
                'disetujui',     // Dokumen lengkap
                'dijadwalkan',   // Sudah ada jadwal
                'selesai',       // Akad selesai
                'dibatalkan'     // Dibatalkan
            ])->default('diajukan');
            
            $table->text('catatan_admin')->nullable();
            $table->foreignId('penghulu_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_nikahs');
    }
};
