<?php

use App\Modules\AuthManagement\Models\Role;
use App\Modules\AuthManagement\Models\Permission;
use App\Modules\AuthManagement\Models\User;
use Illuminate\Support\Facades\DB;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;
use function Pest\Laravel\delete;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    // Create permissions needed for role management
    Permission::firstOrCreate(['name' => 'create roles', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'edit roles', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'delete roles', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'view roles', 'guard_name' => 'web']);

    // Create additional permissions for testing role-permission assignment
    Permission::firstOrCreate(['name' => 'create users', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'edit users', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'delete users', 'guard_name' => 'web']);

    // Create super-admin role with all permissions
    $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
    $superAdminRole->givePermissionTo(Permission::all());

    // Create admin role with role management permissions
    $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $adminRole->givePermissionTo(['create roles', 'edit roles', 'delete roles', 'view roles']);

    // Create user role with limited permissions
    $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
    $userRole->givePermissionTo(['view roles']);
});

// ==================== HAPPY PATH TESTS ====================

test('authenticated user with super-admin role can view roles index page', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = actingAs($user)->get(route('auth.roles.index'));

    $response->assertStatus(200);
    $response->assertViewIs('role.index');
});

test('authenticated user with super-admin role can view create role page', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = actingAs($user)->get(route('auth.roles.create'));

    $response->assertStatus(200);
    $response->assertViewIs('role.create');
    $response->assertViewHas('permissions');
});

test('authenticated user with super-admin role can store a new role', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $permissions = Permission::take(2)->pluck('name')->toArray();

    $response = actingAs($user)->post(route('auth.roles.store'), [
        'name' => 'moderator',
        'permissions' => $permissions,
    ]);

    $response->assertRedirect(route('auth.roles.index'));
    $response->assertSessionHas('success', 'Role baru berhasil dibuat');

    assertDatabaseHas('roles', [
        'name' => 'moderator',
        'guard_name' => 'web',
    ]);

    $role = Role::where('name', 'moderator')->first();
    expect($role->permissions->pluck('name')->toArray())->toBe($permissions);
});

test('authenticated user with super-admin role can view edit role page', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $role = Role::create(['name' => 'moderator', 'guard_name' => 'web']);
    $role->givePermissionTo(['create users']);

    $response = actingAs($user)->get(route('auth.roles.edit', $role));

    $response->assertStatus(200);
    $response->assertViewIs('role.create');
    $response->assertViewHas('role');
    $response->assertViewHas('permissions');
    $response->assertViewHas('rolePermissions');
});

test('authenticated user with super-admin role can update a role', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $role = Role::create(['name' => 'moderator', 'guard_name' => 'web']);
    $role->givePermissionTo(['create users']);

    $newPermissions = Permission::take(2)->pluck('name')->toArray();

    $response = actingAs($user)->put(route('auth.roles.update', $role), [
        'name' => 'senior-moderator',
        'permissions' => $newPermissions,
    ]);

    $response->assertRedirect(route('auth.roles.index'));
    $response->assertSessionHas('success', 'Role berhasil diperbarui');

    assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => 'senior-moderator',
    ]);

    $role->refresh();
    expect($role->permissions->pluck('name')->toArray())->toBe($newPermissions);
});

test('authenticated user with super-admin role can delete a role', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $role = Role::create(['name' => 'moderator', 'guard_name' => 'web']);
    $role->givePermissionTo(['create users']);

    $response = actingAs($user)->delete(route('auth.roles.destroy', $role));

    $response->assertRedirect(route('auth.roles.index'));
    $response->assertSessionHas('success', 'Role berhasil dihapus');

    assertDatabaseMissing('roles', [
        'id' => $role->id,
        'name' => 'moderator',
    ]);
});

test('roles index page displays roles with pagination', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    Role::factory()->count(15)->create();

    $response = actingAs($user)->get(route('auth.roles.index'));

    $response->assertStatus(200);
    $response->assertViewHas('roles');
});

test('roles index page can search roles by name', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    Role::create(['name' => 'administrator', 'guard_name' => 'web']);
    Role::create(['name' => 'moderator', 'guard_name' => 'web']);
    Role::create(['name' => 'editor', 'guard_name' => 'web']);

    $response = actingAs($user)->get(route('auth.roles.index', ['search' => 'admin']));

    $response->assertStatus(200);
    $response->assertViewHas('roles');
});

// ==================== VALIDATION TESTS ====================

test('store role fails when name is missing', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $permissions = Permission::take(1)->pluck('name')->toArray();

    $response = actingAs($user)->post(route('auth.roles.store'), [
        'permissions' => $permissions,
    ]);

    $response->assertSessionHasErrors(['name']);
});

test('store role fails when permissions array is empty', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = actingAs($user)->post(route('auth.roles.store'), [
        'name' => 'moderator',
        'permissions' => [],
    ]);

    $response->assertSessionHasErrors(['permissions']);
});

test('store role fails when permissions are not provided', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = actingAs($user)->post(route('auth.roles.store'), [
        'name' => 'moderator',
    ]);

    $response->assertSessionHasErrors(['permissions']);
});

test('store role fails when name exceeds 255 characters', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $permissions = Permission::take(1)->pluck('name')->toArray();

    $response = actingAs($user)->post(route('auth.roles.store'), [
        'name' => str_repeat('a', 256),
        'permissions' => $permissions,
    ]);

    $response->assertSessionHasErrors(['name']);
});

test('store role fails when role name already exists', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    Role::create(['name' => 'moderator', 'guard_name' => 'web']);

    $permissions = Permission::take(1)->pluck('name')->toArray();

    $response = actingAs($user)->post(route('auth.roles.store'), [
        'name' => 'moderator',
        'permissions' => $permissions,
    ]);

    $response->assertSessionHasErrors(['name']);
});

test('store role fails when permission does not exist', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = actingAs($user)->post(route('auth.roles.store'), [
        'name' => 'moderator',
        'permissions' => ['non-existent-permission'],
    ]);

    $response->assertSessionHasErrors();
});

test('update role fails when name is missing', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $role = Role::create(['name' => 'moderator', 'guard_name' => 'web']);
    $permissions = Permission::take(1)->pluck('name')->toArray();

    $response = actingAs($user)->put(route('auth.roles.update', $role), [
        'permissions' => $permissions,
    ]);

    $response->assertSessionHasErrors(['name']);
});

test('update role fails when name already exists for another role', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    Role::create(['name' => 'moderator', 'guard_name' => 'web']);
    $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);

    $permissions = Permission::take(1)->pluck('name')->toArray();

    $response = actingAs($user)->put(route('auth.roles.update', $role), [
        'name' => 'moderator',
        'permissions' => $permissions,
    ]);

    $response->assertSessionHasErrors(['name']);
});

// ==================== AUTHORIZATION TESTS ====================

test('unauthenticated user cannot view roles index page', function () {
    $response = get(route('auth.roles.index'));

    $response->assertRedirect(route('login'));
});

test('unauthenticated user cannot view create role page', function () {
    $response = get(route('auth.roles.create'));

    $response->assertRedirect(route('login'));
});

test('unauthenticated user cannot store a role', function () {
    $permissions = Permission::take(1)->pluck('name')->toArray();

    $response = post(route('auth.roles.store'), [
        'name' => 'moderator',
        'permissions' => $permissions,
    ]);

    $response->assertRedirect(route('login'));
});

test('unauthenticated user cannot view edit role page', function () {
    $role = Role::create(['name' => 'moderator', 'guard_name' => 'web']);

    $response = get(route('auth.roles.edit', $role));

    $response->assertRedirect(route('login'));
});

test('unauthenticated user cannot update a role', function () {
    $role = Role::create(['name' => 'moderator', 'guard_name' => 'web']);
    $permissions = Permission::take(1)->pluck('name')->toArray();

    $response = put(route('auth.roles.update', $role), [
        'name' => 'senior-moderator',
        'permissions' => $permissions,
    ]);

    $response->assertRedirect(route('login'));
});

test('unauthenticated user cannot delete a role', function () {
    $role = Role::create(['name' => 'moderator', 'guard_name' => 'web']);

    $response = delete(route('auth.roles.destroy', $role));

    $response->assertRedirect(route('login'));
});

test('authenticated user without super-admin role cannot view create role page', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('user');

    $response = actingAs($user)->get(route('auth.roles.create'));

    $response->assertStatus(403);
});

test('authenticated user without super-admin role cannot store a role', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('user');

    $permissions = Permission::take(1)->pluck('name')->toArray();

    $response = actingAs($user)->post(route('auth.roles.store'), [
        'name' => 'moderator',
        'permissions' => $permissions,
    ]);

    $response->assertStatus(403);
});

test('authenticated user without super-admin role cannot view edit role page', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('user');

    $role = Role::create(['name' => 'moderator', 'guard_name' => 'web']);

    $response = actingAs($user)->get(route('auth.roles.edit', $role));

    $response->assertStatus(403);
});

test('authenticated user without super-admin role cannot update a role', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('user');

    $role = Role::create(['name' => 'moderator', 'guard_name' => 'web']);
    $permissions = Permission::take(1)->pluck('name')->toArray();

    $response = actingAs($user)->put(route('auth.roles.update', $role), [
        'name' => 'senior-moderator',
        'permissions' => $permissions,
    ]);

    $response->assertStatus(403);
});

test('authenticated user without super-admin role cannot delete a role', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('user');

    $role = Role::create(['name' => 'moderator', 'guard_name' => 'web']);

    $response = actingAs($user)->delete(route('auth.roles.destroy', $role));

    $response->assertStatus(403);
});

test('authenticated user without super-admin role cannot view roles index page', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('user');

    $response = actingAs($user)->get(route('auth.roles.index'));

    $response->assertStatus(403);
});

// ==================== SUPER-ADMIN PROTECTION TESTS ====================

test('super-admin role cannot be edited', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $superAdminRole = Role::where('name', 'super-admin')->first();

    $response = actingAs($user)->get(route('auth.roles.edit', $superAdminRole));

    $response->assertRedirect();
    $response->assertSessionHas('error', 'Role Super Admin tidak bisa diedit!');
});

test('super-admin role cannot be updated', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $superAdminRole = Role::where('name', 'super-admin')->first();
    $permissions = Permission::take(1)->pluck('name')->toArray();

    $response = actingAs($user)->put(route('auth.roles.update', $superAdminRole), [
        'name' => 'super-admin-updated',
        'permissions' => $permissions,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', 'Restricted!');

    // Verify the role name hasn't changed
    expect($superAdminRole->fresh()->name)->toBe('super-admin');
});

test('super-admin role cannot be deleted', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $superAdminRole = Role::where('name', 'super-admin')->first();

    $response = actingAs($user)->delete(route('auth.roles.destroy', $superAdminRole));

    $response->assertRedirect();
    $response->assertSessionHas('error', 'Role Super Admin tidak boleh dihapus!');

    // Verify the role still exists
    assertDatabaseHas('roles', [
        'name' => 'super-admin',
    ]);
});

// ==================== TRANSACTION TESTS ====================

test('role creation is rolled back on error', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $initialRoleCount = Role::count();

    // Try to create a role with invalid permission
    $response = actingAs($user)->post(route('auth.roles.store'), [
        'name' => 'test-role',
        'permissions' => ['invalid-permission'],
    ]);

    $response->assertSessionHasErrors();

    // Verify no role was created
    expect(Role::count())->toBe($initialRoleCount);
});

// ==================== PERMISSION SYNC TESTS ====================

test('syncing permissions replaces all existing permissions', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $role = Role::create(['name' => 'moderator', 'guard_name' => 'web']);
    $role->givePermissionTo(['create users', 'edit users']);

    $newPermissions = Permission::take(2)->pluck('name')->toArray();

    $response = actingAs($user)->put(route('auth.roles.update', $role), [
        'name' => 'moderator',
        'permissions' => $newPermissions,
    ]);

    $response->assertRedirect(route('auth.roles.index'));

    $role->refresh();
    expect($role->permissions->count())->toBe(2);
    expect($role->permissions->pluck('name')->toArray())->toBe($newPermissions);
});

test('role can have multiple permissions assigned', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $permissions = Permission::take(5)->pluck('name')->toArray();

    $response = actingAs($user)->post(route('auth.roles.store'), [
        'name' => 'multi-permission-role',
        'permissions' => $permissions,
    ]);

    $response->assertRedirect(route('auth.roles.index'));

    $role = Role::where('name', 'multi-permission-role')->first();
    expect($role->permissions->count())->toBe(5);
    // Sort both arrays for comparison since order might differ
    $rolePermissions = $role->permissions->pluck('name')->toArray();
    sort($rolePermissions);
    sort($permissions);
    expect($rolePermissions)->toBe($permissions);
});
