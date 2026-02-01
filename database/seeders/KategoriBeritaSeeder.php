<?php

namespace Database\Seeders;

use App\Models\KategoriBerita;
use Illuminate\Database\Seeder;

class KategoriBeritaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategoris = [
            ['nama' => 'Pengumuman', 'deskripsi' => 'Pengumuman resmi dari KUA'],
            ['nama' => 'Kegiatan', 'deskripsi' => 'Kegiatan dan acara KUA'],
            ['nama' => 'Artikel', 'deskripsi' => 'Artikel dan tulisan'],
            ['nama' => 'Informasi', 'deskripsi' => 'Informasi umum'],
        ];

        foreach ($kategoris as $kategori) {
            KategoriBerita::firstOrCreate(
                ['nama' => $kategori['nama']],
                $kategori
            );
        }
    }
}
