<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Admin
        $superAdmin = User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@kua-sembawa.go.id',
                'password' => Hash::make('password'),
            ]
        );
        $superAdmin->roles()->sync([Role::where('name', 'super_admin')->first()->id]);

        // Admin KUA
        $adminKua = User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin KUA Sembawa',
                'email' => 'admin@kua-sembawa.go.id',
                'password' => Hash::make('password'),
            ]
        );
        $adminKua->roles()->sync([Role::where('name', 'admin_kua')->first()->id]);

        // Penghulu
        $penghulu = User::updateOrCreate(
            ['username' => 'penghulu'],
            [
                'name' => 'Penghulu',
                'email' => 'penghulu@kua-sembawa.go.id',
                'password' => Hash::make('password'),
            ]
        );
        $penghulu->roles()->sync([Role::where('name', 'penghulu')->first()->id]);

        // Penyuluh
        $penyuluh = User::updateOrCreate(
            ['username' => 'penyuluh'],
            [
                'name' => 'Penyuluh Agama',
                'email' => 'penyuluh@kua-sembawa.go.id',
                'password' => Hash::make('password'),
            ]
        );
        $penyuluh->roles()->sync([Role::where('name', 'penyuluh')->first()->id]);

        $this->command->info('Default users seeded successfully!');
        $this->command->table(
            ['Username', 'Email', 'Role', 'Password'],
            [
                ['superadmin', 'superadmin@kua-sembawa.go.id', 'Super Admin', 'password'],
                ['admin', 'admin@kua-sembawa.go.id', 'Admin KUA', 'password'],
                ['penghulu', 'penghulu@kua-sembawa.go.id', 'Penghulu', 'password'],
                ['penyuluh', 'penyuluh@kua-sembawa.go.id', 'Penyuluh', 'password'],
            ]
        );
    }
}
