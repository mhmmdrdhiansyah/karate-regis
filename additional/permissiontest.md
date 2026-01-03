Act as a Senior Laravel QA Engineer.

I need you to analyze my code and generate a comprehensive Feature Test using **Pest PHP**.

CONTEXT:
- Framework: Laravel 11
- Testing: Pest PHP
- Auth: Spatie Permission (Role & Permission based)
- Database: MySQL (Use `RefreshDatabase` trait)

INSTRUCTIONS:
1. **ANALYZE**: First, read the Controller, Model, and Request logic I provided below. Understand the validation rules and authorization checks (e.g., middleware `permission:create users`).
2. **SETUP PHASE (`beforeEach`)**:
   - The test MUST set up the necessary Roles and Permissions before running.
   - Example: Create a 'super-admin' role, give it permissions, create a user, and assign the role.
   - This is crucial to avoid 403 Forbidden errors during testing.
3. **TEST SCENARIOS**:
   - **Happy Path**: Test that Create, Read, Update, and Delete work correctly when the user has the right permission. Assert database changes and redirects.
   - **Validation**: Test that invalid input (e.g., empty fields, duplicate email) fails and returns errors.
   - **Authorization**: Test that a user WITHOUT permission cannot perform the action (should be 403 Forbidden).
   - **Unauthenticated**: Test that a guest is redirected to login.
4. **CODE QUALITY**:
   - Use `/** @var \App\Models\User $user */` type hints before `actingAs($user)` to prevent VS Code/Intelephense false positive errors.
   - Keep the code clean and readable.

INPUT CODE:

check controller on /app/modules/AuthManagement/Controllers/PermissionController.php

check models on /app/modules/AuthManagement/Models/Permission.php

check Requests on /app/modules/AuthManagement/Requests/StorePermissionRequest.php&&UpdatePermissionRequest.php

Please generate the `tests/Feature/AuthManagement/PermissionCrudTest.php` file based on the analysis above.