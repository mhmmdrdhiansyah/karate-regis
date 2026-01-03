<?php

use App\Modules\AuthManagement\Controllers\PermissionController;
use App\Modules\AuthManagement\Models\User;
use App\Modules\AuthManagement\Models\Permission;
use Database\Factories\AuthManagement\PermissionFactory;
use Illuminate\Support\Facades\Artisan;
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

describe('PermissionController', function () {
    describe('index', function () {
        it('redirects unauthenticated users to login page', function () {
            get(route('auth.permissions.index'))
                ->assertRedirect(route('login'));
        });

        it('displays permissions list for authenticated user', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            actingAs($user)
                ->get(route('auth.permissions.index'))
                ->assertStatus(200)
                ->assertViewIs('permission.index');
        });
    });

    describe('create', function () {
        it('redirects unauthenticated users to login page', function () {
            get(route('auth.permissions.create'))
                ->assertRedirect(route('login'));
        });

        it('forbids access for users without create permissions permission', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            actingAs($user)
                ->get(route('auth.permissions.create'))
                ->assertStatus(403);
        });

        it('displays create form for users with create permissions permission', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('create permissions');
            actingAs($user)
                ->get(route('auth.permissions.create'))
                ->assertStatus(200)
                ->assertViewIs('permission.create');
        });
    });

    describe('store', function () {
        it('redirects unauthenticated users to login page', function () {
            post(route('auth.permissions.store'), [])
                ->assertRedirect(route('login'));
        });

        it('forbids access for users without create permissions permission', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            actingAs($user)
                ->post(route('auth.permissions.store'), [])
                ->assertStatus(403);
        });

        it('fails validation when name is missing', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('create permissions');

            actingAs($user)
                ->post(route('auth.permissions.store'), [])
                ->assertSessionHasErrors(['name']);
        });

        it('fails validation when name is empty', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('create permissions');

            actingAs($user)
                ->post(route('auth.permissions.store'), [
                    'name' => '',
                ])
                ->assertSessionHasErrors(['name']);
        });

        it('fails validation when name already exists', function () {
            Permission::create(['name' => 'existing-permission', 'guard_name' => 'web']);
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('create permissions');

            actingAs($user)
                ->post(route('auth.permissions.store'), [
                    'name' => 'existing-permission',
                ])
                ->assertSessionHasErrors(['name']);
        });

        it('fails validation when name exceeds max length', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('create permissions');

            actingAs($user)
                ->post(route('auth.permissions.store'), [
                    'name' => str_repeat('a', 256),
                ])
                ->assertSessionHasErrors(['name']);
        });

        it('successfully creates a new permission with valid data', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('create permissions');

            actingAs($user)
                ->post(route('auth.permissions.store'), [
                    'name' => 'new-permission',
                ])
                ->assertRedirect(route('auth.permissions.index'))
                ->assertSessionHas('success', 'Permission baru berhasil dibuat');

            assertDatabaseHas('permissions', [
                'name' => 'new-permission',
                'guard_name' => 'web',
            ]);
        });

        it('successfully creates a permission with dot notation', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('create permissions');

            actingAs($user)
                ->post(route('auth.permissions.store'), [
                    'name' => 'users.create',
                ])
                ->assertRedirect(route('auth.permissions.index'))
                ->assertSessionHas('success', 'Permission baru berhasil dibuat');

            assertDatabaseHas('permissions', [
                'name' => 'users.create',
                'guard_name' => 'web',
            ]);
        });
    });

    describe('edit', function () {
        it('redirects unauthenticated users to login page', function () {
            $permission = Permission::create(['name' => 'test-permission', 'guard_name' => 'web']);
            get(route('auth.permissions.edit', $permission))
                ->assertRedirect(route('login'));
        });

        it('forbids access for users without edit permissions permission', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $permission = Permission::create(['name' => 'test-permission', 'guard_name' => 'web']);
            actingAs($user)
                ->get(route('auth.permissions.edit', $permission))
                ->assertStatus(403);
        });

        it('displays edit form for users with edit permissions permission', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('edit permissions');
            $permission = Permission::create(['name' => 'test-permission', 'guard_name' => 'web']);

            actingAs($user)
                ->get(route('auth.permissions.edit', $permission))
                ->assertStatus(200)
                ->assertViewIs('permission.create')
                ->assertViewHas('permission');
        });
    });

    describe('update', function () {
        it('redirects unauthenticated users to login page', function () {
            $permission = Permission::create(['name' => 'test-permission', 'guard_name' => 'web']);
            put(route('auth.permissions.update', $permission), [])
                ->assertRedirect(route('login'));
        });

        it('forbids access for users without edit permissions permission', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $permission = Permission::create(['name' => 'test-permission', 'guard_name' => 'web']);
            actingAs($user)
                ->put(route('auth.permissions.update', $permission), [])
                ->assertStatus(403);
        });

        it('fails validation when name is missing', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('edit permissions');
            $permission = Permission::create(['name' => 'test-permission', 'guard_name' => 'web']);

            actingAs($user)
                ->put(route('auth.permissions.update', $permission), [])
                ->assertSessionHasErrors(['name']);
        });

        it('fails validation when name is empty', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('edit permissions');
            $permission = Permission::create(['name' => 'test-permission', 'guard_name' => 'web']);

            actingAs($user)
                ->put(route('auth.permissions.update', $permission), [
                    'name' => '',
                ])
                ->assertSessionHasErrors(['name']);
        });

        it('fails validation when name already exists for another permission', function () {
            Permission::create(['name' => 'existing-permission', 'guard_name' => 'web']);
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('edit permissions');
            $permission = Permission::create(['name' => 'test-permission', 'guard_name' => 'web']);

            actingAs($user)
                ->put(route('auth.permissions.update', $permission), [
                    'name' => 'existing-permission',
                ])
                ->assertSessionHasErrors(['name']);
        });

        it('fails validation when name exceeds max length', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('edit permissions');
            $permission = Permission::create(['name' => 'test-permission', 'guard_name' => 'web']);

            actingAs($user)
                ->put(route('auth.permissions.update', $permission), [
                    'name' => str_repeat('a', 256),
                ])
                ->assertSessionHasErrors(['name']);
        });

        it('successfully updates permission with valid data', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('edit permissions');
            $permission = Permission::create(['name' => 'test-permission', 'guard_name' => 'web']);

            actingAs($user)
                ->put(route('auth.permissions.update', $permission), [
                    'name' => 'updated-permission',
                ])
                ->assertRedirect(route('auth.permissions.index'))
                ->assertSessionHas('success', 'Permission berhasil diperbarui');

            assertDatabaseHas('permissions', [
                'id' => $permission->id,
                'name' => 'updated-permission',
            ]);
        });

        it('allows updating permission to same name', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('edit permissions');
            $permission = Permission::create(['name' => 'test-permission', 'guard_name' => 'web']);

            actingAs($user)
                ->put(route('auth.permissions.update', $permission), [
                    'name' => 'test-permission',
                ])
                ->assertRedirect(route('auth.permissions.index'))
                ->assertSessionHas('success', 'Permission berhasil diperbarui');

            assertDatabaseHas('permissions', [
                'id' => $permission->id,
                'name' => 'test-permission',
            ]);
        });
    });

    describe('destroy', function () {
        it('redirects unauthenticated users to login page', function () {
            $permission = Permission::create(['name' => 'test-permission', 'guard_name' => 'web']);
            delete(route('auth.permissions.destroy', $permission))
                ->assertRedirect(route('login'));
        });

        it('forbids access for users without delete permissions permission', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $permission = Permission::create(['name' => 'test-permission', 'guard_name' => 'web']);
            actingAs($user)
                ->delete(route('auth.permissions.destroy', $permission))
                ->assertStatus(403);
        });

        it('successfully deletes a permission', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('delete permissions');
            $permission = Permission::create(['name' => 'test-permission', 'guard_name' => 'web']);

            actingAs($user)
                ->delete(route('auth.permissions.destroy', $permission))
                ->assertRedirect(route('auth.permissions.index'))
                ->assertSessionHas('success', 'Permission berhasil dihapus');

            assertDatabaseMissing('permissions', ['id' => $permission->id]);
        });
    });
});
