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
            // 'view laporan', // <-- Contoh: Jika nanti ada permission baru, tinggal tulis disini
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

        // --- Role: Staff ---
        $staffRole = Role::firstOrCreate([
            'name' => 'staff',
            'guard_name' => 'web'
        ]);
        // Berikan akses standar jika belum punya
        // givePermissionTo: Menambah akses tanpa menghapus akses lain yang mungkin sudah dikasih manual
        $staffRole->givePermissionTo([
            'view dashboard',
            'view users',
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


        // --- User: Staff Biasa ---
        $staff = User::firstOrCreate(
            ['email' => 'staff@admin.com'], // 1. Cek email
            [                              // 2. Data baru jika email belum ada
                'name' => 'Staff Biasa',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $staff->assignRole($staffRole);

        $this->command->info('Seeder selesai! Data lama aman, data baru ditambahkan.');
    }
}
