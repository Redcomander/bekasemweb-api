<?php

namespace Database\Seeders;

use App\Models\Masjid;
use App\Models\Imam;
use App\Models\Khotib;
use Illuminate\Database\Seeder;

class MasjidSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample Masjids
        $masjids = [
            [
                'nama' => 'Masjid Agung Sembawa',
                'alamat' => 'Jl. Raya Sembawa No. 1',
                'kelurahan' => 'Sembawa',
                'kecamatan' => 'Sembawa',
                'tipe' => 'masjid',
                'tahun_berdiri' => 1985,
                'kapasitas' => 500,
                'keterangan' => 'Masjid utama Kecamatan Sembawa',
            ],
            [
                'nama' => 'Masjid Nurul Iman',
                'alamat' => 'Jl. Merdeka No. 10',
                'kelurahan' => 'Sembawa',
                'kecamatan' => 'Sembawa',
                'tipe' => 'masjid',
                'tahun_berdiri' => 1995,
                'kapasitas' => 300,
            ],
            [
                'nama' => 'Musholla Al-Ikhlas',
                'alamat' => 'Jl. Pasar Sembawa',
                'kelurahan' => 'Sembawa',
                'kecamatan' => 'Sembawa',
                'tipe' => 'musholla',
                'kapasitas' => 50,
            ],
        ];

        foreach ($masjids as $data) {
            Masjid::updateOrCreate(['nama' => $data['nama']], $data);
        }

        // Sample Imams
        $imams = [
            ['nama' => 'H. Ahmad Fauzi', 'no_hp' => '08123456781', 'alamat' => 'Sembawa'],
            ['nama' => 'Ustadz Muhammad Ali', 'no_hp' => '08123456782', 'alamat' => 'Sembawa'],
            ['nama' => 'H. Abdullah', 'no_hp' => '08123456783', 'alamat' => 'Sembawa'],
        ];

        foreach ($imams as $data) {
            Imam::updateOrCreate(['nama' => $data['nama']], $data);
        }

        // Sample Khotibs
        $khotibs = [
            ['nama' => 'Dr. H. Zainuddin', 'no_hp' => '08123456791', 'alamat' => 'Sembawa'],
            ['nama' => 'Ustadz Hasan Basri', 'no_hp' => '08123456792', 'alamat' => 'Sembawa'],
        ];

        foreach ($khotibs as $data) {
            Khotib::updateOrCreate(['nama' => $data['nama']], $data);
        }

        // Assign imam & khotib to masjid
        $masjidAgung = Masjid::where('nama', 'Masjid Agung Sembawa')->first();
        $imam1 = Imam::where('nama', 'H. Ahmad Fauzi')->first();
        $khotib1 = Khotib::where('nama', 'Dr. H. Zainuddin')->first();
        
        if ($masjidAgung && $imam1) {
            $masjidAgung->imams()->syncWithoutDetaching([$imam1->id => ['is_primary' => true]]);
        }
        if ($masjidAgung && $khotib1) {
            $masjidAgung->khotibs()->syncWithoutDetaching([$khotib1->id]);
        }

        $this->command->info('Masjid, Imam, and Khotib seeded successfully!');
    }
}
