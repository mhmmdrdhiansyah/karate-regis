<?php

use App\Models\User;
use App\Modules\AuthManagement\Models\Permission;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\from;
use function Pest\Laravel\get;
use function Pest\Laravel\actingAs;

uses()->group('auth-management', 'permission');

beforeEach(function () {
    // Create all necessary permissions for the middleware
    $permissions = [
        'create permissions',
        'edit permissions',
        'delete permissions',
    ];

    foreach ($permissions as $permissionName) {
        \Spatie\Permission\Models\Permission::firstOrCreate([
            'name' => $permissionName,
            'guard_name' => 'web'
        ]);
    }

    // Create super-admin role (required by route middleware)
    $superAdminRole = \Spatie\Permission\Models\Role::firstOrCreate([
        'name' => 'super-admin',
        'guard_name' => 'web'
    ]);

    // Give super-admin role all permissions
    foreach ($permissions as $permissionName) {
        $superAdminRole->givePermissionTo($permissionName);
    }
});

/*
|--------------------------------------------------------------------------
 INDEX TESTS
|--------------------------------------------------------------------------
*/

test('super-admin can view permissions index page', function () {
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    Permission::factory()->create(['name' => 'test permission']);

    $response = actingAs($user)->get(route('auth.permissions.index'));

    $response->assertStatus(200);
    $response->assertViewIs('permission.index');
    $response->assertViewHas('permissions');
});

test('permissions index can search by name', function () {
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    Permission::factory()->create(['name' => 'edit users']);
    Permission::factory()->create(['name' => 'delete users']);
    Permission::factory()->create(['name' => 'create posts']);

    $response = actingAs($user)->get(route('auth.permissions.index', ['search' => 'users']));

    $response->assertStatus(200);
    $response->assertViewHas('permissions');
    expect($response->viewData('permissions')->items())->toHaveCount(2);
});

test('user without super-admin role cannot view permissions index', function () {
    /** @var \App\Models\User $user */
    $user = User::factory()->create();

    $response = actingAs($user)->get(route('auth.permissions.index'));

    $response->assertStatus(403);
});

test('guest cannot view permissions index and is redirected to login', function () {
    $response = get(route('auth.permissions.index'));

    $response->assertRedirect(route('login'));
});

/*
|--------------------------------------------------------------------------
 CREATE TESTS
|--------------------------------------------------------------------------
*/

test('super-admin can view create permission page', function () {
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = actingAs($user)->get(route('auth.permissions.create'));

    $response->assertStatus(200);
    $response->assertViewIs('permission.create');
});

test('user without super-admin role cannot view create page', function () {
    /** @var \App\Models\User $user */
    $user = User::factory()->create();

    $response = actingAs($user)->get(route('auth.permissions.create'));

    $response->assertStatus(403);
});

test('guest cannot view create permission page', function () {
    $response = get(route('auth.permissions.create'));

    $response->assertRedirect(route('login'));
});

/*
|--------------------------------------------------------------------------
 STORE TESTS
|--------------------------------------------------------------------------
*/

test('super-admin can store a new permission', function () {
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = actingAs($user)
        ->from(route('auth.permissions.create'))
        ->post(route('auth.permissions.store'), [
            'name' => 'edit posts',
        ]);

    $response->assertRedirect(route('auth.permissions.index'));
    $response->assertSessionHas('success', 'Permission baru berhasil dibuat');

    assertDatabaseHas('permissions', [
        'name' => 'edit posts',
        'guard_name' => 'web',
    ]);
});

test('store permission requires name field', function () {
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = actingAs($user)
        ->from(route('auth.permissions.create'))
        ->post(route('auth.permissions.store'), [
            'name' => '',
        ]);

    $response->assertRedirect(route('auth.permissions.create'));
    $response->assertSessionHasErrors(['name']);

    assertDatabaseMissing('permissions', [
        'name' => '',
    ]);
});

test('store permission validates name is unique', function () {
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    Permission::factory()->create(['name' => 'edit posts']);

    $response = actingAs($user)
        ->from(route('auth.permissions.create'))
        ->post(route('auth.permissions.store'), [
            'name' => 'edit posts',
        ]);

    $response->assertRedirect(route('auth.permissions.create'));
    $response->assertSessionHasErrors(['name']);
});

test('store permission validates name max length', function () {
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = actingAs($user)
        ->from(route('auth.permissions.create'))
        ->post(route('auth.permissions.store'), [
            'name' => str_repeat('a', 256),
        ]);

    $response->assertRedirect(route('auth.permissions.create'));
    $response->assertSessionHasErrors(['name']);
});

test('user without super-admin role cannot store permission', function () {
    /** @var \App\Models\User $user */
    $user = User::factory()->create();

    $response = actingAs($user)
        ->post(route('auth.permissions.store'), [
            'name' => 'edit posts',
        ]);

    $response->assertStatus(403);

    assertDatabaseMissing('permissions', [
        'name' => 'edit posts',
    ]);
});

test('guest cannot store permission', function () {
    $response = from(route('auth.permissions.create'))
        ->post(route('auth.permissions.store'), [
            'name' => 'edit posts',
        ]);

    $response->assertRedirect(route('login'));

    assertDatabaseMissing('permissions', [
        'name' => 'edit posts',
    ]);
});

/*
|--------------------------------------------------------------------------
 EDIT TESTS
|--------------------------------------------------------------------------
*/

test('super-admin can view edit permission page', function () {
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $permission = Permission::factory()->create(['name' => 'edit posts']);

    $response = actingAs($user)->get(route('auth.permissions.edit', $permission));

    $response->assertStatus(200);
    $response->assertViewIs('permission.create');
    $response->assertViewHas('permission', fn ($viewPermission) => $viewPermission->id === $permission->id);
});

test('user without super-admin role cannot view edit page', function () {
    /** @var \App\Models\User $user */
    $user = User::factory()->create();

    $permission = Permission::factory()->create(['name' => 'edit posts']);

    $response = actingAs($user)->get(route('auth.permissions.edit', $permission));

    $response->assertStatus(403);
});

test('guest cannot view edit permission page', function () {
    $permission = Permission::factory()->create(['name' => 'edit posts']);

    $response = get(route('auth.permissions.edit', $permission));

    $response->assertRedirect(route('login'));
});

/*
|--------------------------------------------------------------------------
 UPDATE TESTS
|--------------------------------------------------------------------------
*/

test('super-admin can update a permission', function () {
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $permission = Permission::factory()->create(['name' => 'edit posts']);

    $response = actingAs($user)
        ->from(route('auth.permissions.edit', $permission))
        ->put(route('auth.permissions.update', $permission), [
            'name' => 'update posts',
        ]);

    $response->assertRedirect(route('auth.permissions.index'));
    $response->assertSessionHas('success', 'Permission berhasil diperbarui');

    assertDatabaseHas('permissions', [
        'id' => $permission->id,
        'name' => 'update posts',
    ]);
});

test('update permission requires name field', function () {
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $permission = Permission::factory()->create(['name' => 'edit posts']);

    $response = actingAs($user)
        ->from(route('auth.permissions.edit', $permission))
        ->put(route('auth.permissions.update', $permission), [
            'name' => '',
        ]);

    $response->assertRedirect(route('auth.permissions.edit', $permission));
    $response->assertSessionHasErrors(['name']);

    expect($permission->fresh()->name)->toBe('edit posts');
});

test('update permission validates name is unique except current', function () {
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $permission1 = Permission::factory()->create(['name' => 'edit posts']);
    $permission2 = Permission::factory()->create(['name' => 'delete posts']);

    $response = actingAs($user)
        ->from(route('auth.permissions.edit', $permission1))
        ->put(route('auth.permissions.update', $permission1), [
            'name' => 'delete posts',
        ]);

    $response->assertRedirect(route('auth.permissions.edit', $permission1));
    $response->assertSessionHasErrors(['name']);
});

test('update permission validates name max length', function () {
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $permission = Permission::factory()->create(['name' => 'edit posts']);

    $response = actingAs($user)
        ->from(route('auth.permissions.edit', $permission))
        ->put(route('auth.permissions.update', $permission), [
            'name' => str_repeat('a', 256),
        ]);

    $response->assertRedirect(route('auth.permissions.edit', $permission));
    $response->assertSessionHasErrors(['name']);
});

test('user without super-admin role cannot update permission', function () {
    /** @var \App\Models\User $user */
    $user = User::factory()->create();

    $permission = Permission::factory()->create(['name' => 'edit posts']);

    $response = actingAs($user)
        ->put(route('auth.permissions.update', $permission), [
            'name' => 'update posts',
        ]);

    $response->assertStatus(403);

    expect($permission->fresh()->name)->toBe('edit posts');
});

test('guest cannot update permission', function () {
    $permission = Permission::factory()->create(['name' => 'edit posts']);

    $response = from(route('auth.permissions.edit', $permission))
        ->put(route('auth.permissions.update', $permission), [
            'name' => 'update posts',
        ]);

    $response->assertRedirect(route('login'));

    expect($permission->fresh()->name)->toBe('edit posts');
});

/*
|--------------------------------------------------------------------------
 DESTROY TESTS
|--------------------------------------------------------------------------
*/

test('super-admin can delete a permission', function () {
    /** @var \App\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $permission = Permission::factory()->create(['name' => 'edit posts']);

    $response = actingAs($user)
        ->from(route('auth.permissions.index'))
        ->delete(route('auth.permissions.destroy', $permission));

    $response->assertRedirect(route('auth.permissions.index'));
    $response->assertSessionHas('success', 'Permission berhasil dihapus');

    assertDatabaseMissing('permissions', [
        'id' => $permission->id,
        'name' => 'edit posts',
    ]);
});

test('user without super-admin role cannot delete permission', function () {
    /** @var \App\Models\User $user */
    $user = User::factory()->create();

    $permission = Permission::factory()->create(['name' => 'edit posts']);

    $response = actingAs($user)
        ->delete(route('auth.permissions.destroy', $permission));

    $response->assertStatus(403);

    assertDatabaseHas('permissions', [
        'id' => $permission->id,
        'name' => 'edit posts',
    ]);
});

test('guest cannot delete permission', function () {
    $permission = Permission::factory()->create(['name' => 'edit posts']);

    $response = from(route('auth.permissions.index'))
        ->delete(route('auth.permissions.destroy', $permission));

    $response->assertRedirect(route('login'));

    assertDatabaseHas('permissions', [
        'id' => $permission->id,
        'name' => 'edit posts',
    ]);
});
