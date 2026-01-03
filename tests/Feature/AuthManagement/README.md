# AuthManagement Feature Tests

This directory contains comprehensive Feature Tests for the AuthManagement module using Pest PHP testing framework.

## Test Files

### 1. UserControllerTest.php
Tests for [`UserController`](../../app/Modules/AuthManagement/Controllers/UserController.php) covering:
- **index**: List users with search functionality
- **create**: Display user creation form
- **store**: Create new users with validation
- **edit**: Display user edit form
- **update**: Update user information
- **destroy**: Delete users (with self-deletion prevention)
- **show**: Display user details

### 2. RoleControllerTest.php
Tests for [`RoleController`](../../app/Modules/AuthManagement/Controllers/RoleController.php) covering:
- **index**: List roles with search functionality
- **create**: Display role creation form
- **store**: Create new roles with permission assignment
- **edit**: Display role edit form
- **update**: Update role information and permissions
- **destroy**: Delete roles (with super-admin protection)

### 3. PermissionControllerTest.php
Tests for [`PermissionController`](../../app/Modules/AuthManagement/Controllers/PermissionController.php) covering:
- **index**: List permissions with search functionality
- **create**: Display permission creation form
- **store**: Create new permissions
- **edit**: Display permission edit form
- **update**: Update permission information
- **destroy**: Delete permissions

## Test Scenarios Covered

Each controller test includes comprehensive coverage of:

### 1. Unauthenticated Access
- Redirects unauthenticated users to login page

### 2. Unauthorized Access
- Returns 403 Forbidden for users without required permissions

### 3. Happy Path (Success)
- Successfully creates/updates/deletes records with valid data
- Verifies database changes
- Checks redirect responses
- Validates success messages in session

### 4. Validation Errors
- Missing required fields
- Invalid email format
- Duplicate values (email, role name, permission name)
- Password confirmation mismatch
- Empty arrays
- Invalid foreign keys (roles, permissions)
- Maximum length violations

## Running the Tests

### Run all AuthManagement tests:
```bash
php artisan test --filter=AuthManagement
```

### Run specific test file:
```bash
php artisan test tests/Feature/AuthManagement/UserControllerTest.php
php artisan test tests/Feature/AuthManagement/RoleControllerTest.php
php artisan test tests/Feature/AuthManagement/PermissionControllerTest.php
```

### Run specific test case:
```bash
php artisan test --filter="UserControllerTest"
php artisan test --filter="RoleControllerTest"
php artisan test --filter="PermissionControllerTest"
```

### Run with verbose output:
```bash
php artisan test --filter=AuthManagement --verbose
```

## Setup Requirements

These tests require:
- Laravel 11
- Pest PHP testing framework
- Spatie Permission package
- MySQL database with RefreshDatabase trait
- DatabaseSeeder with roles and permissions

## Test Structure

Each test follows this pattern:

```php
beforeEach(function () {
    Artisan::call('migrate:fresh');
    Artisan::call('db:seed', ['--class' => 'DatabaseSeeder']);
});

describe('ControllerName', function () {
    describe('methodName', function () {
        it('test description', function () {
            // Arrange
            $user = User::create([...]);
            
            // Act
            $response = actingAs($user)->post(route(...), [...]);
            
            // Assert
            $response->assertRedirect(...);
            assertDatabaseHas(...);
        });
    });
});
```

## Key Features

✅ **Authentication Testing**: Verifies login requirements for all routes
✅ **Authorization Testing**: Validates Spatie Permission middleware
✅ **Validation Testing**: Comprehensive form validation coverage
✅ **Database Assertions**: Verifies data persistence and deletion
✅ **Session Assertions**: Checks success/error messages
✅ **View Assertions**: Validates view rendering
✅ **Edge Cases**: Self-deletion prevention, super-admin protection

## Notes

- Tests use direct `User::create()` instead of `User::factory()->create()` to avoid type hinting issues with intelephense
- All tests run in isolated database environments
- Database is refreshed before each test suite
- Default seeder populates roles and permissions
