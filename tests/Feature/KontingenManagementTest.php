<?php

namespace Tests\Feature;

use App\Models\Contingent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KontingenManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup permissions and roles
        Permission::create(['name' => 'view kontingen']);
        Permission::create(['name' => 'delete kontingen']);
        $adminRole = Role::create(['name' => 'super-admin']);
        $adminRole->givePermissionTo(['view kontingen', 'delete kontingen']);
        Role::create(['name' => 'kontingen']);
    }

    public function test_admin_can_delete_kontingen_and_its_user()
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $user = User::factory()->create();
        $user->assignRole('kontingen');
        $contingent = Contingent::create([
            'user_id' => $user->id,
            'name' => 'Test Contingent',
            'official_name' => 'Official Test Contingent',
            'phone' => '08123456789',
            'address' => 'Test Address',
            'province' => 'Test Province',
            'regency' => 'Test Regency',
        ]);

        $this->assertDatabaseHas('contingents', ['id' => $contingent->id]);
        $this->assertDatabaseHas('users', ['id' => $user->id]);

        $response = $this->actingAs($admin)
            ->delete(route('kontingen.destroy', $contingent));

        $response->assertRedirect(route('kontingen.index'));
        $response->assertSessionHas('success', 'Kontingen berhasil dihapus');

        $this->assertDatabaseMissing('contingents', ['id' => $contingent->id]);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
