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
            'view kontingen',
            'create kontingen',
            'edit kontingen',
            'delete kontingen',
            'edit own kontingen',
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
        // Panitia dapat mengelola event dan registrasi serta kontingen
        $panitiaRole->syncPermissions([
            'view dashboard',
            'view users',
            'view kontingen',
            'create kontingen',
            'edit kontingen',
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
        $kontingenRole->syncPermissions([
            'view dashboard',
            'edit own kontingen',
            'view events',
            'manage own participants',
        ]);

        // =================================================================
        // 4. SETUP USER DEFAULT (Aman dijalankan berulang)
        // =================================================================

        // --- User: Super Admin ---
        $admin = User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole($superAdminRole);

        // --- User: Panitia ---
        $panitia = User::updateOrCreate(
            ['email' => 'panitia@admin.com'],
            [
                'name' => 'Panitia',
                'username' => 'panitia',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $panitia->assignRole($panitiaRole);

        // --- User: Kontingen ---
        $kontingen = User::updateOrCreate(
            ['email' => 'kontingen@admin.com'],
            [
                'name' => 'Kontingen',
                'username' => 'kontingen',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $kontingen->assignRole($kontingenRole);

        $this->command->info('Seeder selesai! Data lama aman, data baru ditambahkan.');
    }
}
