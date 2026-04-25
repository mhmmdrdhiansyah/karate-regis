<?php

namespace Database\Seeders\Auth;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Reset Cache Permission (Wajib agar aplikasi sadar ada perubahan)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // =================================================================
        // 2. DAFTAR PERMISSION (Tambah permission baru di sini aman)
        // =================================================================
        $permissions = [
            'view dashboard',
            'view users',
            'create users',
            'edit users',
            'delete users',
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'view permissions',
            'create permissions',
            'edit permissions',
            'delete permissions',
            'manage settings',
            // Event & Registration Permissions
            'view events',
            'manage participants',
            'verify payments',
            'verify documents',
            'manage own participants',
            'manage registrations',
        ];

        foreach ($permissions as $permission) {
            // firstOrCreate: Cek apakah nama permission sudah ada?
            // Jika SUDAH ada -> Lewati (Data aman)
            // Jika BELUM ada -> Buat baru
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // =================================================================
        // 3. SETUP ROLES
        // =================================================================

        // --- Role: Super Admin ---
        $superAdminRole = Role::firstOrCreate([
            'name' => 'super-admin',
            'guard_name' => 'web'
        ]);
        // syncPermissions: Memastikan Super Admin SELALU punya SEMUA akses (termasuk yg baru ditambah)
        $superAdminRole->syncPermissions(Permission::all());

        // --- Role: Panitia (Event Organizer) ---
        $panitiaRole = Role::firstOrCreate([
            'name' => 'panitia',
            'guard_name' => 'web'
        ]);
        // Panitia dapat mengelola event dan registrasi
        $panitiaRole->givePermissionTo([
            'view dashboard',
            'view users',
            'view events',
            'manage participants',
            'verify payments',
            'verify documents',
            'manage registrations',
        ]);

        // --- Role: Kontingen (Contingent/Team Representative) ---
        $kontingenRole = Role::firstOrCreate([
            'name' => 'kontingen',
            'guard_name' => 'web'
        ]);
        // Kontingen hanya bisa view event dan manage peserta sendiri
        $kontingenRole->givePermissionTo([
            'view dashboard',
            'view events',
            'manage own participants',
        ]);

        // =================================================================
        // 4. SETUP USER DEFAULT (Aman dijalankan berulang)
        // =================================================================

        // --- User: Super Admin ---
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'], // 1. Cek: Apakah email ini ada?
            [                              // 2. Jika TIDAK ada, buat dengan data ini:
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        // Pastikan role-nya nempel (meskipun usernya sudah lama ada)
        $admin->assignRole($superAdminRole);


        // --- User: Panitia ---
        $panitia = User::firstOrCreate(
            ['email' => 'panitia@admin.com'],
            [
                'name' => 'Panitia Event',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $panitia->assignRole($panitiaRole);

        // --- User: Kontingen ---
        $kontingen = User::firstOrCreate(
            ['email' => 'kontingen@test.com'],
            [
                'name' => 'Kontingen Test',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $kontingen->assignRole($kontingenRole);

        $this->command->info('Seeder selesai! Data lama aman, data baru ditambahkan.');
    }
}
