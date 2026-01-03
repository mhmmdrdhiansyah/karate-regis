<?php

use App\Modules\AuthManagement\Controllers\UserController;
use App\Modules\AuthManagement\Models\User;
use Database\Factories\AuthManagement\UserFactory;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\from;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;
use function Pest\Laravel\delete;

beforeEach(function () {
    Artisan::call('migrate:fresh');
    Artisan::call('db:seed', ['--class' => 'DatabaseSeeder']);
});

describe('UserController', function () {
    describe('index', function () {
        it('redirects unauthenticated users to login page', function () {
            get(route('auth.users.index'))
                ->assertRedirect(route('login'));
        });

        it('displays users list for authenticated user', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            actingAs($user)
                ->get(route('auth.users.index'))
                ->assertStatus(200)
                ->assertViewIs('user.index');
        });
    });

    describe('create', function () {
        it('redirects unauthenticated users to login page', function () {
            get(route('auth.users.create'))
                ->assertRedirect(route('login'));
        });

        it('forbids access for users without create users permission', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            actingAs($user)
                ->get(route('auth.users.create'))
                ->assertStatus(403);
        });

        it('displays create form for users with create users permission', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('create users');
            actingAs($user)
                ->get(route('auth.users.create'))
                ->assertStatus(200)
                ->assertViewIs('user.create');
        });
    });

    describe('store', function () {
        it('redirects unauthenticated users to login page', function () {
            post(route('auth.users.store'), [])
                ->assertRedirect(route('login'));
        });

        it('forbids access for users without create users permission', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            actingAs($user)
                ->post(route('auth.users.store'), [])
                ->assertStatus(403);
        });

        it('fails validation when required fields are missing', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('create users');
            $role = Role::first();

            actingAs($user)
                ->post(route('auth.users.store'), [
                    'role' => $role->name,
                ])
                ->assertSessionHasErrors(['name', 'email', 'password']);
        });

        it('fails validation when email is invalid', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('create users');
            $role = Role::first();

            actingAs($user)
                ->post(route('auth.users.store'), [
                    'name' => 'Test User',
                    'email' => 'invalid-email',
                    'password' => 'password123',
                    'password_confirmation' => 'password123',
                    'role' => $role->name,
                ])
                ->assertSessionHasErrors(['email']);
        });

        it('fails validation when password confirmation does not match', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('create users');
            $role = Role::first();

            actingAs($user)
                ->post(route('auth.users.store'), [
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'password' => 'password123',
                    'password_confirmation' => 'different123',
                    'role' => $role->name,
                ])
                ->assertSessionHasErrors(['password']);
        });

        it('fails validation when email already exists', function () {
            $existingUser = User::create([
                'name' => fake()->name(),
                'email' => 'existing@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('create users');
            $role = Role::first();

            actingAs($user)
                ->post(route('auth.users.store'), [
                    'name' => 'Test User',
                    'email' => 'existing@example.com',
                    'password' => 'password123',
                    'password_confirmation' => 'password123',
                    'role' => $role->name,
                ])
                ->assertSessionHasErrors(['email']);
        });

        it('fails validation when role is invalid', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('create users');

            actingAs($user)
                ->post(route('auth.users.store'), [
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'password' => 'password123',
                    'password_confirmation' => 'password123',
                    'role' => 'invalid-role',
                ])
                ->assertSessionHasErrors(['role']);
        });

        it('successfully creates a new user with valid data', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('create users');
            $role = Role::first();

            actingAs($user)
                ->post(route('auth.users.store'), [
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'password' => 'password123',
                    'password_confirmation' => 'password123',
                    'role' => $role->name,
                ])
                ->assertRedirect(route('auth.users.index'))
                ->assertSessionHas('success', 'User berhasil ditambahkan');

            assertDatabaseHas('users', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

            $newUser = User::where('email', 'test@example.com')->first();
            expect($newUser->roles->pluck('name')->toArray())->toContain($role->name);
        });
    });

    describe('edit', function () {
        it('redirects unauthenticated users to login page', function () {
            $user = User::factory()->create();
            get(route('auth.users.edit', $user))
                ->assertRedirect(route('login'));
        });

        it('forbids access for users without edit users permission', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $targetUser = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            actingAs($user)
                ->get(route('auth.users.edit', $targetUser))
                ->assertStatus(403);
        });

        it('displays edit form for users with edit users permission', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('edit users');
            $targetUser = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $targetUser->assignRole('admin');

            actingAs($user)
                ->get(route('auth.users.edit', $targetUser))
                ->assertStatus(200)
                ->assertViewIs('user.create')
                ->assertViewHas('user')
                ->assertViewHas('roles')
                ->assertViewHas('userRole');
        });
    });

    describe('update', function () {
        it('redirects unauthenticated users to login page', function () {
            $user = User::factory()->create();
            put(route('auth.users.update', $user), [])
                ->assertRedirect(route('login'));
        });

        it('forbids access for users without edit users permission', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $targetUser = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            actingAs($user)
                ->put(route('auth.users.update', $targetUser), [])
                ->assertStatus(403);
        });

        it('fails validation when required fields are missing', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('edit users');
            $targetUser = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $role = Role::first();

            actingAs($user)
                ->put(route('auth.users.update', $targetUser), [
                    'role' => $role->name,
                ])
                ->assertSessionHasErrors(['name', 'email']);
        });

        it('fails validation when email is invalid', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('edit users');
            $targetUser = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $role = Role::first();

            actingAs($user)
                ->put(route('auth.users.update', $targetUser), [
                    'name' => 'Updated Name',
                    'email' => 'invalid-email',
                    'role' => $role->name,
                ])
                ->assertSessionHasErrors(['email']);
        });

        it('fails validation when email already exists for another user', function () {
            $existingUser = User::create([
                'name' => fake()->name(),
                'email' => 'existing@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('edit users');
            $targetUser = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $role = Role::first();

            actingAs($user)
                ->put(route('auth.users.update', $targetUser), [
                    'name' => 'Updated Name',
                    'email' => 'existing@example.com',
                    'role' => $role->name,
                ])
                ->assertSessionHasErrors(['email']);
        });

        it('successfully updates user with valid data', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('edit users');
            $targetUser = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $targetUser->assignRole('admin');
            $role = Role::first();

            actingAs($user)
                ->put(route('auth.users.update', $targetUser), [
                    'name' => 'Updated Name',
                    'email' => 'updated@example.com',
                    'role' => $role->name,
                ])
                ->assertRedirect(route('auth.users.index'))
                ->assertSessionHas('success', 'User berhasil diperbarui');

            assertDatabaseHas('users', [
                'id' => $targetUser->id,
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
            ]);

            $targetUser->refresh();
            expect($targetUser->roles->pluck('name')->toArray())->toContain($role->name);
        });
    });

    describe('destroy', function () {
        it('redirects unauthenticated users to login page', function () {
            $user = User::factory()->create();
            delete(route('auth.users.destroy', $user))
                ->assertRedirect(route('login'));
        });

        it('forbids access for users without delete users permission', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $targetUser = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            actingAs($user)
                ->delete(route('auth.users.destroy', $targetUser))
                ->assertStatus(403);
        });

        it('prevents users from deleting themselves', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('delete users');

            actingAs($user)
                ->delete(route('auth.users.destroy', $user))
                ->assertRedirect()
                ->assertSessionHas('error', 'Anda tidak bisa menghapus akun sendiri!');

            assertDatabaseHas('users', ['id' => $user->id]);
        });

        it('successfully deletes another user', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $user->givePermissionTo('delete users');
            $targetUser = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);

            actingAs($user)
                ->delete(route('auth.users.destroy', $targetUser))
                ->assertRedirect(route('auth.users.index'))
                ->assertSessionHas('success', 'User berhasil dihapus');

            assertDatabaseMissing('users', ['id' => $targetUser->id]);
        });
    });

    describe('show', function () {
        it('redirects unauthenticated users to login page', function () {
            $user = User::factory()->create();
            get(route('auth.users.show', $user))
                ->assertRedirect(route('login'));
        });

        it('displays user details for authenticated user', function () {
            $user = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $targetUser = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            actingAs($user)
                ->get(route('auth.users.show', $targetUser))
                ->assertStatus(200)
                ->assertViewIs('user.show');
        });
    });
});
