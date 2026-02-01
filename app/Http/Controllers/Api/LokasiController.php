<?php

namespace App\Http\Controllers\Api;

/**
 * @OA\Info(
 *     title="BEKASEMWEB API",
 *     version="1.0.0",
 *     description="API untuk Website KUA Sembawa"
 * )
 */
class LokasiController extends BaseController
{
    /**
     * Get KUA location information
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $lokasi = [
            'nama' => config('lokasi.nama'),
            'alamat' => config('lokasi.alamat'),
            'kelurahan' => config('lokasi.kelurahan'),
            'kecamatan' => config('lokasi.kecamatan'),
            'kabupaten' => config('lokasi.kabupaten'),
            'provinsi' => config('lokasi.provinsi'),
            'kode_pos' => config('lokasi.kode_pos'),
            'koordinat' => [
                'latitude' => config('lokasi.latitude'),
                'longitude' => config('lokasi.longitude'),
            ],
            'google_maps_embed' => config('lokasi.google_maps_embed'),
            'kontak' => [
                'telepon' => config('lokasi.telepon'),
                'email' => config('lokasi.email'),
                'whatsapp' => config('lokasi.whatsapp'),
            ],
            'jam_operasional' => config('lokasi.jam_operasional'),
            'website' => config('lokasi.website'),
        ];

        return $this->successResponse($lokasi, 'Informasi lokasi KUA');
    }
}
