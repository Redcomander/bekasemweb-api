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
        Schema::create('dokumen_nikahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')->constrained('pendaftaran_nikahs')->onDelete('cascade');
            $table->enum('jenis', [
                'ktp_pria',
                'ktp_wanita',
                'kk_pria',
                'kk_wanita',
                'akta_lahir_pria',
                'akta_lahir_wanita',
                'ijazah_pria',
                'ijazah_wanita',
                'surat_n1',
                'surat_n2',
                'surat_n4',
                'foto_pria',
                'foto_wanita',
                'surat_izin_orang_tua',
                'akta_cerai',
                'surat_kematian',
                'lainnya'
            ]);
            $table->string('file_path');
            $table->string('original_name');
            $table->enum('status', ['pending', 'valid', 'invalid'])->default('pending');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen_nikahs');
    }
};
