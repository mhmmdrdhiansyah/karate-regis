<?php

use App\Modules\AuthManagement\Models\User;
use App\Modules\AuthManagement\Models\Role;
use App\Modules\AuthManagement\Models\Permission;
use Illuminate\Support\Facades\Hash;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;
use function Pest\Laravel\delete;

beforeEach(function () {
    // Create necessary permissions
    Permission::firstOrCreate(['name' => 'create users', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'edit users', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'delete users', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'view users', 'guard_name' => 'web']);

    // Create super-admin role and assign all permissions
    $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
    $superAdminRole->syncPermissions([
        'create users',
        'edit users',
        'delete users',
        'view users',
    ]);

    // Create regular user role
    Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
});

// ==================== HAPPY PATH TESTS ====================

test('authenticated user with permission can view users list', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    // Create some test users
    User::factory()->count(5)->create();

    $response = actingAs($user)
        ->get(route('auth.users.index'));

    $response->assertStatus(200);
    $response->assertViewIs('user.index');
    $response->assertViewHas('users');
});

test('authenticated user with permission can view users list with search', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    // Create users with specific names
    User::factory()->create(['name' => 'John Doe']);
    User::factory()->create(['name' => 'Jane Smith']);
    User::factory()->create(['name' => 'Bob Johnson']);

    $response = actingAs($user)
        ->get(route('auth.users.index', ['search' => 'John']));

    $response->assertStatus(200);
    $response->assertViewHas('users');
});

test('authenticated user with create permission can view create user form', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = actingAs($user)
        ->get(route('auth.users.create'));

    $response->assertStatus(200);
    $response->assertViewIs('user.create');
    $response->assertViewHas('roles');
});

test('authenticated user with create permission can store a new user', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $userData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'role' => 'user',
    ];

    $response = actingAs($user)
        ->post(route('auth.users.store'), $userData);

    $response->assertRedirect(route('auth.users.index'));
    $response->assertSessionHas('success', 'User berhasil ditambahkan');

    assertDatabaseHas('users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    $createdUser = User::where('email', 'test@example.com')->first();
    expect($createdUser->hasRole('user'))->toBeTrue();
});

test('authenticated user with edit permission can view edit user form', function () {
    /** @var \App\Modules\AuthManagement\Models\User $adminUser */
    $adminUser = User::factory()->create();
    $adminUser->assignRole('super-admin');

    /** @var \App\Modules\AuthManagement\Models\User $targetUser */
    $targetUser = User::factory()->create();

    $response = actingAs($adminUser)
        ->get(route('auth.users.edit', $targetUser));

    $response->assertStatus(200);
    $response->assertViewIs('user.create');
    $response->assertViewHas('user');
    $response->assertViewHas('roles');
    $response->assertViewHas('userRole');
});

test('authenticated user with permission can view user details', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    /** @var \App\Modules\AuthManagement\Models\User $targetUser */
    $targetUser = User::factory()->create();

    $response = actingAs($user)
        ->get(route('auth.users.show', $targetUser));

    $response->assertStatus(200);
    $response->assertViewIs('user.show');
    $response->assertViewHas('user');
});

test('authenticated user with edit permission can update a user', function () {
    /** @var \App\Modules\AuthManagement\Models\User $adminUser */
    $adminUser = User::factory()->create();
    $adminUser->assignRole('super-admin');

    /** @var \App\Modules\AuthManagement\Models\User $targetUser */
    $targetUser = User::factory()->create(['name' => 'Old Name']);
    $targetUser->assignRole('user');

    $updateData = [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'role' => 'user',
    ];

    $response = actingAs($adminUser)
        ->put(route('auth.users.update', $targetUser), $updateData);

    $response->assertRedirect(route('auth.users.index'));
    $response->assertSessionHas('success', 'User berhasil diperbarui');

    assertDatabaseHas('users', [
        'id' => $targetUser->id,
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ]);

    $targetUser->refresh();
    expect($targetUser->hasRole('user'))->toBeTrue();
});

test('authenticated user with delete permission can delete a user', function () {
    /** @var \App\Modules\AuthManagement\Models\User $adminUser */
    $adminUser = User::factory()->create();
    $adminUser->assignRole('super-admin');

    /** @var \App\Modules\AuthManagement\Models\User $targetUser */
    $targetUser = User::factory()->create();

    $response = actingAs($adminUser)
        ->delete(route('auth.users.destroy', $targetUser));

    $response->assertRedirect(route('auth.users.index'));
    $response->assertSessionHas('success', 'User berhasil dihapus');

    assertDatabaseMissing('users', [
        'id' => $targetUser->id,
    ]);
});

// ==================== VALIDATION TESTS ====================

test('store user fails validation when name is missing', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $userData = [
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'role' => 'user',
    ];

    $response = actingAs($user)
        ->post(route('auth.users.store'), $userData);

    $response->assertSessionHasErrors(['name']);
});

test('store user fails validation when email is invalid', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $userData = [
        'name' => 'Test User',
        'email' => 'invalid-email',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'role' => 'user',
    ];

    $response = actingAs($user)
        ->post(route('auth.users.store'), $userData);

    $response->assertSessionHasErrors(['email']);
});

test('store user fails validation when email is not unique', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    // Create existing user with the same email
    User::factory()->create(['email' => 'existing@example.com']);

    $userData = [
        'name' => 'Test User',
        'email' => 'existing@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'role' => 'user',
    ];

    $response = actingAs($user)
        ->post(route('auth.users.store'), $userData);

    $response->assertSessionHasErrors(['email']);
});

test('store user fails validation when password is missing', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $userData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'role' => 'user',
    ];

    $response = actingAs($user)
        ->post(route('auth.users.store'), $userData);

    $response->assertSessionHasErrors(['password']);
});

test('store user fails validation when password confirmation does not match', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $userData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'DifferentPassword!',
        'role' => 'user',
    ];

    $response = actingAs($user)
        ->post(route('auth.users.store'), $userData);

    $response->assertSessionHasErrors(['password']);
});

test('store user fails validation when role is missing', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $userData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];

    $response = actingAs($user)
        ->post(route('auth.users.store'), $userData);

    $response->assertSessionHasErrors(['role']);
});

test('store user fails validation when role does not exist', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $userData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'role' => 'non-existent-role',
    ];

    $response = actingAs($user)
        ->post(route('auth.users.store'), $userData);

    $response->assertSessionHasErrors(['role']);
});

test('update user fails validation when name is missing', function () {
    /** @var \App\Modules\AuthManagement\Models\User $adminUser */
    $adminUser = User::factory()->create();
    $adminUser->assignRole('super-admin');

    /** @var \App\Modules\AuthManagement\Models\User $targetUser */
    $targetUser = User::factory()->create();

    $updateData = [
        'email' => 'updated@example.com',
        'role' => 'user',
    ];

    $response = actingAs($adminUser)
        ->put(route('auth.users.update', $targetUser), $updateData);

    $response->assertSessionHasErrors(['name']);
});

test('update user fails validation when email is invalid', function () {
    /** @var \App\Modules\AuthManagement\Models\User $adminUser */
    $adminUser = User::factory()->create();
    $adminUser->assignRole('super-admin');

    /** @var \App\Modules\AuthManagement\Models\User $targetUser */
    $targetUser = User::factory()->create();

    $updateData = [
        'name' => 'Updated Name',
        'email' => 'invalid-email',
        'role' => 'user',
    ];

    $response = actingAs($adminUser)
        ->put(route('auth.users.update', $targetUser), $updateData);

    $response->assertSessionHasErrors(['email']);
});

test('update user fails validation when email is not unique (excluding current user)', function () {
    /** @var \App\Modules\AuthManagement\Models\User $adminUser */
    $adminUser = User::factory()->create();
    $adminUser->assignRole('super-admin');

    /** @var \App\Modules\AuthManagement\Models\User $targetUser */
    $targetUser = User::factory()->create();

    // Create another user with the email we want to use
    User::factory()->create(['email' => 'existing@example.com']);

    $updateData = [
        'name' => 'Updated Name',
        'email' => 'existing@example.com',
        'role' => 'user',
    ];

    $response = actingAs($adminUser)
        ->put(route('auth.users.update', $targetUser), $updateData);

    $response->assertSessionHasErrors(['email']);
});

test('update user allows same email for current user', function () {
    /** @var \App\Modules\AuthManagement\Models\User $adminUser */
    $adminUser = User::factory()->create();
    $adminUser->assignRole('super-admin');

    /** @var \App\Modules\AuthManagement\Models\User $targetUser */
    $targetUser = User::factory()->create(['email' => 'same@example.com']);

    $updateData = [
        'name' => 'Updated Name',
        'email' => 'same@example.com',
        'role' => 'user',
    ];

    $response = actingAs($adminUser)
        ->put(route('auth.users.update', $targetUser), $updateData);

    $response->assertRedirect(route('auth.users.index'));
    $response->assertSessionHasNoErrors();
});

test('update user fails validation when role is missing', function () {
    /** @var \App\Modules\AuthManagement\Models\User $adminUser */
    $adminUser = User::factory()->create();
    $adminUser->assignRole('super-admin');

    /** @var \App\Modules\AuthManagement\Models\User $targetUser */
    $targetUser = User::factory()->create();

    $updateData = [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ];

    $response = actingAs($adminUser)
        ->put(route('auth.users.update', $targetUser), $updateData);

    $response->assertSessionHasErrors(['role']);
});

// ==================== AUTHORIZATION TESTS ====================

test('user without create permission cannot view create user form', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('user'); // Regular user without create permission

    $response = actingAs($user)
        ->get(route('auth.users.create'));

    $response->assertStatus(403);
});

test('user without create permission cannot store a new user', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('user');

    $userData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'role' => 'user',
    ];

    $response = actingAs($user)
        ->post(route('auth.users.store'), $userData);

    $response->assertStatus(403);
    assertDatabaseMissing('users', ['email' => 'test@example.com']);
});

test('user without edit permission cannot view edit user form', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('user');

    /** @var \App\Modules\AuthManagement\Models\User $targetUser */
    $targetUser = User::factory()->create();

    $response = actingAs($user)
        ->get(route('auth.users.edit', $targetUser));

    $response->assertStatus(403);
});

test('user without edit permission cannot update a user', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('user');

    /** @var \App\Modules\AuthManagement\Models\User $targetUser */
    $targetUser = User::factory()->create(['name' => 'Original Name']);

    $updateData = [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'role' => 'user',
    ];

    $response = actingAs($user)
        ->put(route('auth.users.update', $targetUser), $updateData);

    $response->assertStatus(403);
    assertDatabaseHas('users', [
        'id' => $targetUser->id,
        'name' => 'Original Name',
    ]);
});

test('user without delete permission cannot delete a user', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('user');

    /** @var \App\Modules\AuthManagement\Models\User $targetUser */
    $targetUser = User::factory()->create();

    $response = actingAs($user)
        ->delete(route('auth.users.destroy', $targetUser));

    $response->assertStatus(403);
    assertDatabaseHas('users', ['id' => $targetUser->id]);
});

test('user cannot delete their own account', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = actingAs($user)
        ->delete(route('auth.users.destroy', $user));

    $response->assertRedirect();
    $response->assertSessionHas('error', 'Anda tidak bisa menghapus akun sendiri!');
    assertDatabaseHas('users', ['id' => $user->id]);
});

// ==================== UNAUTHENTICATED TESTS ====================

test('unauthenticated user cannot view users list', function () {
    $response = get(route('auth.users.index'));

    $response->assertRedirect(route('login'));
});

test('unauthenticated user cannot view create user form', function () {
    $response = get(route('auth.users.create'));

    $response->assertRedirect(route('login'));
});

test('unauthenticated user cannot store a new user', function () {
    $userData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'role' => 'user',
    ];

    $response = post(route('auth.users.store'), $userData);

    $response->assertRedirect(route('login'));
    assertDatabaseMissing('users', ['email' => 'test@example.com']);
});

test('unauthenticated user cannot view edit user form', function () {
    /** @var \App\Modules\AuthManagement\Models\User $targetUser */
    $targetUser = User::factory()->create();

    $response = get(route('auth.users.edit', $targetUser));

    $response->assertRedirect(route('login'));
});

test('unauthenticated user cannot view user details', function () {
    /** @var \App\Modules\AuthManagement\Models\User $targetUser */
    $targetUser = User::factory()->create();

    $response = get(route('auth.users.show', $targetUser));

    $response->assertRedirect(route('login'));
});

test('unauthenticated user cannot update a user', function () {
    /** @var \App\Modules\AuthManagement\Models\User $targetUser */
    $targetUser = User::factory()->create(['name' => 'Original Name']);

    $updateData = [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'role' => 'user',
    ];

    $response = put(route('auth.users.update', $targetUser), $updateData);

    $response->assertRedirect(route('login'));
    assertDatabaseHas('users', [
        'id' => $targetUser->id,
        'name' => 'Original Name',
    ]);
});

test('unauthenticated user cannot delete a user', function () {
    /** @var \App\Modules\AuthManagement\Models\User $targetUser */
    $targetUser = User::factory()->create();

    $response = delete(route('auth.users.destroy', $targetUser));

    $response->assertRedirect(route('login'));
    assertDatabaseHas('users', ['id' => $targetUser->id]);
});

// ==================== EDGE CASES ====================

test('user can be updated with a different role', function () {
    /** @var \App\Modules\AuthManagement\Models\User $adminUser */
    $adminUser = User::factory()->create();
    $adminUser->assignRole('super-admin');

    /** @var \App\Modules\AuthManagement\Models\User $targetUser */
    $targetUser = User::factory()->create();
    $targetUser->assignRole('user');

    $updateData = [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'role' => 'super-admin',
    ];

    $response = actingAs($adminUser)
        ->put(route('auth.users.update', $targetUser), $updateData);

    $response->assertRedirect(route('auth.users.index'));

    $targetUser->refresh();
    expect($targetUser->hasRole('super-admin'))->toBeTrue();
    expect($targetUser->hasRole('user'))->toBeFalse();
});

test('password is hashed when storing new user', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $userData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'role' => 'user',
    ];

    actingAs($user)
        ->post(route('auth.users.store'), $userData);

    $createdUser = User::where('email', 'test@example.com')->first();
    expect(Hash::check('Password123!', $createdUser->password))->toBeTrue();
});

test('user email is case-insensitive for uniqueness', function () {
    /** @var \App\Modules\AuthManagement\Models\User $user */
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    // Create existing user with lowercase email
    User::factory()->create(['email' => 'existing@example.com']);

    $userData = [
        'name' => 'Test User',
        'email' => 'EXISTING@EXAMPLE.COM', // Uppercase version
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'role' => 'user',
    ];

    $response = actingAs($user)
        ->post(route('auth.users.store'), $userData);

    $response->assertSessionHasErrors(['email']);
});
