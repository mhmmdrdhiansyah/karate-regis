<?php

use App\Modules\AuthManagement\Controllers\RoleController;
use App\Modules\AuthManagement\Models\User;
use App\Modules\AuthManagement\Models\Role;
use Database\Factories\AuthManagement\RoleFactory;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;
use function Pest\Laravel\delete;

beforeEach(function () {
    Artisan::call('migrate:fresh');
    Artisan::call('db:seed', ['--class' => 'DatabaseSeeder']);
});

describe('RoleController', function () {
    describe('index', function () {
        it('redirects unauthenticated users to login page', function () {
            get(route('auth.roles.index'))
                ->assertRedirect(route('login'));
        });

        it('displays roles list for authenticated user', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            actingAs($user)
                ->get(route('auth.roles.index'))
                ->assertStatus(200)
                ->assertViewIs('role.index');
        });
    });

    describe('create', function () {
        it('redirects unauthenticated users to login page', function () {
            get(route('auth.roles.create'))
                ->assertRedirect(route('login'));
        });

        it('forbids access for users without create roles permission', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            actingAs($user)
                ->get(route('auth.roles.create'))
                ->assertStatus(403);
        });

        it('displays create form for users with create roles permission', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('create roles');
            actingAs($user)
                ->get(route('auth.roles.create'))
                ->assertStatus(200)
                ->assertViewIs('role.create')
                ->assertViewHas('permissions');
        });
    });

    describe('store', function () {
        it('redirects unauthenticated users to login page', function () {
            post(route('auth.roles.store'), [])
                ->assertRedirect(route('login'));
        });

        it('forbids access for users without create roles permission', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            actingAs($user)
                ->post(route('auth.roles.store'), [])
                ->assertStatus(403);
        });

        it('fails validation when required fields are missing', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('create roles');

            actingAs($user)
                ->post(route('auth.roles.store'), [])
                ->assertSessionHasErrors(['name', 'permissions']);
        });

        it('fails validation when name is empty', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('create roles');
            $permission = Permission::first();

            actingAs($user)
                ->post(route('auth.roles.store'), [
                    'name' => '',
                    'permissions' => [$permission->name],
                ])
                ->assertSessionHasErrors(['name']);
        });

        it('fails validation when name already exists', function () {
            Role::create(['name' => 'existing-role', 'guard_name' => 'web']);
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('create roles');
            $permission = Permission::first();

            actingAs($user)
                ->post(route('auth.roles.store'), [
                    'name' => 'existing-role',
                    'permissions' => [$permission->name],
                ])
                ->assertSessionHasErrors(['name']);
        });

        it('fails validation when permissions array is empty', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('create roles');

            actingAs($user)
                ->post(route('auth.roles.store'), [
                    'name' => 'new-role',
                    'permissions' => [],
                ])
                ->assertSessionHasErrors(['permissions']);
        });

        it('fails validation when permissions are not provided', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('create roles');

            actingAs($user)
                ->post(route('auth.roles.store'), [
                    'name' => 'new-role',
                ])
                ->assertSessionHasErrors(['permissions']);
        });

        it('fails validation when permission is invalid', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('create roles');

            actingAs($user)
                ->post(route('auth.roles.store'), [
                    'name' => 'new-role',
                    'permissions' => ['invalid-permission'],
                ])
                ->assertSessionHasErrors(['permissions']);
        });

        it('successfully creates a new role with valid data', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('create roles');
            $permission = Permission::first();

            actingAs($user)
                ->post(route('auth.roles.store'), [
                    'name' => 'new-role',
                    'permissions' => [$permission->name],
                ])
                ->assertRedirect(route('auth.roles.index'))
                ->assertSessionHas('success', 'Role baru berhasil dibuat');

            assertDatabaseHas('roles', [
                'name' => 'new-role',
                'guard_name' => 'web',
            ]);

            $role = Role::where('name', 'new-role')->first();
            expect($role->permissions->pluck('name')->toArray())->toContain($permission->name);
        });

        it('successfully creates a new role with multiple permissions', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('create roles');
            $permissions = Permission::take(2)->get();

            actingAs($user)
                ->post(route('auth.roles.store'), [
                    'name' => 'multi-permission-role',
                    'permissions' => $permissions->pluck('name')->toArray(),
                ])
                ->assertRedirect(route('auth.roles.index'))
                ->assertSessionHas('success', 'Role baru berhasil dibuat');

            $role = Role::where('name', 'multi-permission-role')->first();
            expect($role->permissions->count())->toBe(2);
        });
    });

    describe('edit', function () {
        it('redirects unauthenticated users to login page', function () {
            $role = Role::create(['name' => 'test-role', 'guard_name' => 'web']);
            get(route('auth.roles.edit', $role))
                ->assertRedirect(route('login'));
        });

        it('forbids access for users without edit roles permission', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $role = Role::create(['name' => 'test-role', 'guard_name' => 'web']);
            actingAs($user)
                ->get(route('auth.roles.edit', $role))
                ->assertStatus(403);
        });

        it('prevents editing super-admin role', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('edit roles');
            $superAdminRole = Role::create(['name' => 'super-admin', 'guard_name' => 'web']);

            actingAs($user)
                ->get(route('auth.roles.edit', $superAdminRole))
                ->assertRedirect()
                ->assertSessionHas('error', 'Role Super Admin tidak bisa diedit!');
        });

        it('displays edit form for regular roles', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('edit roles');
            $role = Role::create(['name' => 'test-role', 'guard_name' => 'web']);
            $permission = Permission::first();
            $role->givePermissionTo($permission->name);

            actingAs($user)
                ->get(route('auth.roles.edit', $role))
                ->assertStatus(200)
                ->assertViewIs('role.create')
                ->assertViewHas('role')
                ->assertViewHas('permissions')
                ->assertViewHas('rolePermissions');
        });
    });

    describe('update', function () {
        it('redirects unauthenticated users to login page', function () {
            $role = Role::create(['name' => 'test-role', 'guard_name' => 'web']);
            put(route('auth.roles.update', $role), [])
                ->assertRedirect(route('login'));
        });

        it('forbids access for users without edit roles permission', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $role = Role::create(['name' => 'test-role', 'guard_name' => 'web']);
            actingAs($user)
                ->put(route('auth.roles.update', $role), [])
                ->assertStatus(403);
        });

        it('prevents updating super-admin role', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('edit roles');
            $superAdminRole = Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
            $permission = Permission::first();

            actingAs($user)
                ->put(route('auth.roles.update', $superAdminRole), [
                    'name' => 'updated-super-admin',
                    'permissions' => [$permission->name],
                ])
                ->assertRedirect()
                ->assertSessionHas('error', 'Restricted!');
        });

        it('fails validation when required fields are missing', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('edit roles');
            $role = Role::create(['name' => 'test-role', 'guard_name' => 'web']);

            actingAs($user)
                ->put(route('auth.roles.update', $role), [])
                ->assertSessionHasErrors(['name', 'permissions']);
        });

        it('fails validation when name is empty', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('edit roles');
            $role = Role::create(['name' => 'test-role', 'guard_name' => 'web']);
            $permission = Permission::first();

            actingAs($user)
                ->put(route('auth.roles.update', $role), [
                    'name' => '',
                    'permissions' => [$permission->name],
                ])
                ->assertSessionHasErrors(['name']);
        });

        it('fails validation when name already exists for another role', function () {
            Role::create(['name' => 'existing-role', 'guard_name' => 'web']);
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('edit roles');
            $role = Role::create(['name' => 'test-role', 'guard_name' => 'web']);
            $permission = Permission::first();

            actingAs($user)
                ->put(route('auth.roles.update', $role), [
                    'name' => 'existing-role',
                    'permissions' => [$permission->name],
                ])
                ->assertSessionHasErrors(['name']);
        });

        it('fails validation when permissions array is empty', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('edit roles');
            $role = Role::create(['name' => 'test-role', 'guard_name' => 'web']);

            actingAs($user)
                ->put(route('auth.roles.update', $role), [
                    'name' => 'updated-role',
                    'permissions' => [],
                ])
                ->assertSessionHasErrors(['permissions']);
        });

        it('fails validation when permission is invalid', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('edit roles');
            $role = Role::create(['name' => 'test-role', 'guard_name' => 'web']);

            actingAs($user)
                ->put(route('auth.roles.update', $role), [
                    'name' => 'updated-role',
                    'permissions' => ['invalid-permission'],
                ])
                ->assertSessionHasErrors(['permissions']);
        });

        it('successfully updates role with valid data', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('edit roles');
            $role = Role::create(['name' => 'test-role', 'guard_name' => 'web']);
            $permission = Permission::first();

            actingAs($user)
                ->put(route('auth.roles.update', $role), [
                    'name' => 'updated-role',
                    'permissions' => [$permission->name],
                ])
                ->assertRedirect(route('auth.roles.index'))
                ->assertSessionHas('success', 'Role berhasil diperbarui');

            assertDatabaseHas('roles', [
                'id' => $role->id,
                'name' => 'updated-role',
            ]);

            $role->refresh();
            expect($role->permissions->pluck('name')->toArray())->toContain($permission->name);
        });

        it('successfully updates role with multiple permissions', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('edit roles');
            $role = Role::create(['name' => 'test-role', 'guard_name' => 'web']);
            $oldPermission = Permission::first();
            $role->givePermissionTo($oldPermission->name);
            $newPermissions = Permission::skip(1)->take(2)->get();

            actingAs($user)
                ->put(route('auth.roles.update', $role), [
                    'name' => 'updated-role',
                    'permissions' => $newPermissions->pluck('name')->toArray(),
                ])
                ->assertRedirect(route('auth.roles.index'))
                ->assertSessionHas('success', 'Role berhasil diperbarui');

            $role->refresh();
            expect($role->permissions->count())->toBe(2);
            expect($role->permissions->pluck('name')->toArray())->not->toContain($oldPermission->name);
        });
    });

    describe('destroy', function () {
        it('redirects unauthenticated users to login page', function () {
            $role = Role::create(['name' => 'test-role', 'guard_name' => 'web']);
            delete(route('auth.roles.destroy', $role))
                ->assertRedirect(route('login'));
        });

        it('forbids access for users without delete roles permission', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $role = Role::create(['name' => 'test-role', 'guard_name' => 'web']);
            actingAs($user)
                ->delete(route('auth.roles.destroy', $role))
                ->assertStatus(403);
        });

        it('prevents deleting super-admin role', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('delete roles');
            $superAdminRole = Role::create(['name' => 'super-admin', 'guard_name' => 'web']);

            actingAs($user)
                ->delete(route('auth.roles.destroy', $superAdminRole))
                ->assertRedirect()
                ->assertSessionHas('error', 'Role Super Admin tidak boleh dihapus!');

            assertDatabaseHas('roles', ['name' => 'super-admin']);
        });

        it('successfully deletes a regular role', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('delete roles');
            $role = Role::create(['name' => 'test-role', 'guard_name' => 'web']);

            actingAs($user)
                ->delete(route('auth.roles.destroy', $role))
                ->assertRedirect(route('auth.roles.index'))
                ->assertSessionHas('success', 'Role berhasil dihapus');

            assertDatabaseMissing('roles', ['id' => $role->id]);
        });
    });
});
