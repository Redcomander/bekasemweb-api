<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ==========================================
        // CREATE ROLES
        // ==========================================
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Admin',
                'description' => 'Akses penuh ke seluruh sistem',
            ],
            [
                'name' => 'admin_kua',
                'display_name' => 'Admin KUA',
                'description' => 'Kelola konten dan layanan KUA',
            ],
            [
                'name' => 'penghulu',
                'display_name' => 'Penghulu',
                'description' => 'Kelola jadwal dan data nikah',
            ],
            [
                'name' => 'penyuluh',
                'display_name' => 'Penyuluh',
                'description' => 'Kelola konten BIMWIN dan penyuluhan',
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(['name' => $roleData['name']], $roleData);
        }

        // ==========================================
        // CREATE PERMISSIONS
        // ==========================================
        $permissions = [
            // User Management
            ['name' => 'manage_users', 'display_name' => 'Kelola Pengguna', 'group' => 'users'],
            ['name' => 'manage_roles', 'display_name' => 'Kelola Role', 'group' => 'users'],
            
            // Content Management
            ['name' => 'manage_berita', 'display_name' => 'Kelola Berita', 'group' => 'content'],
            ['name' => 'manage_galeri', 'display_name' => 'Kelola Galeri', 'group' => 'content'],
            ['name' => 'manage_majelis', 'display_name' => 'Kelola Majelis Taklim', 'group' => 'content'],
            
            // Masjid Management
            ['name' => 'manage_masjid', 'display_name' => 'Kelola Masjid', 'group' => 'masjid'],
            ['name' => 'manage_imam', 'display_name' => 'Kelola Imam', 'group' => 'masjid'],
            ['name' => 'manage_khotib', 'display_name' => 'Kelola Khotib', 'group' => 'masjid'],
            
            // Layanan Nikah
            ['name' => 'manage_pendaftaran', 'display_name' => 'Kelola Pendaftaran Nikah', 'group' => 'nikah'],
            ['name' => 'verify_dokumen', 'display_name' => 'Verifikasi Dokumen', 'group' => 'nikah'],
            ['name' => 'manage_jadwal', 'display_name' => 'Kelola Jadwal Nikah', 'group' => 'nikah'],
            
            // Antrian
            ['name' => 'manage_antrian', 'display_name' => 'Kelola Antrian', 'group' => 'layanan'],
            
            // Dashboard
            ['name' => 'view_dashboard', 'display_name' => 'Lihat Dashboard', 'group' => 'dashboard'],
            ['name' => 'view_reports', 'display_name' => 'Lihat Laporan', 'group' => 'dashboard'],
        ];

        foreach ($permissions as $permData) {
            Permission::updateOrCreate(['name' => $permData['name']], $permData);
        }

        // ==========================================
        // ASSIGN PERMISSIONS TO ROLES
        // ==========================================
        
        // Super Admin - semua permission
        $superAdmin = Role::where('name', 'super_admin')->first();
        $superAdmin->permissions()->sync(Permission::pluck('id'));

        // Admin KUA
        $adminKua = Role::where('name', 'admin_kua')->first();
        $adminKuaPermissions = Permission::whereIn('name', [
            'manage_berita', 'manage_galeri', 'manage_majelis',
            'manage_masjid', 'manage_imam', 'manage_khotib',
            'manage_pendaftaran', 'verify_dokumen', 'manage_jadwal',
            'manage_antrian', 'view_dashboard', 'view_reports',
        ])->pluck('id');
        $adminKua->permissions()->sync($adminKuaPermissions);

        // Penghulu
        $penghulu = Role::where('name', 'penghulu')->first();
        $penghuluPermissions = Permission::whereIn('name', [
            'manage_jadwal', 'view_dashboard',
        ])->pluck('id');
        $penghulu->permissions()->sync($penghuluPermissions);

        // Penyuluh
        $penyuluh = Role::where('name', 'penyuluh')->first();
        $penyuluhPermissions = Permission::whereIn('name', [
            'manage_berita', 'manage_majelis', 'view_dashboard',
        ])->pluck('id');
        $penyuluh->permissions()->sync($penyuluhPermissions);

        $this->command->info('Roles and Permissions seeded successfully!');
    }
}
