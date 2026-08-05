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

        $this->assertSoftDeleted('contingents', ['id' => $contingent->id]);
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_kontingen_user_can_upload_and_remove_profile_photo()
    {
        Permission::create(['name' => 'edit own kontingen']);
        $user = User::factory()->create();
        $user->givePermissionTo('edit own kontingen');
        $user->assignRole('kontingen');

        $contingent = Contingent::create([
            'user_id' => $user->id,
            'name' => 'Test Kontingen',
            'official_name' => 'Official Kontingen',
            'phone' => '08123456789',
            'address' => 'Test Address',
            'province' => 'Jawa Barat',
            'regency' => 'Kota Bandung',
        ]);

        \Illuminate\Support\Facades\Storage::fake('public');
        $file = \Illuminate\Http\UploadedFile::fake()->image('logo.png');

        $response = $this->actingAs($user)
            ->patch(route('profile.update.kontingen'), [
                'name' => 'Test Kontingen Updated',
                'official_name' => 'Official Kontingen Updated',
                'province' => 'Jawa Barat',
                'regency' => 'Kota Bandung',
                'photo' => $file,
            ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('status', 'kontingen-updated');

        $contingent->refresh();
        $this->assertNotNull($contingent->photo);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($contingent->photo);

        // Test removing photo
        $responseRemove = $this->actingAs($user)
            ->patch(route('profile.update.kontingen'), [
                'name' => 'Test Kontingen Updated',
                'official_name' => 'Official Kontingen Updated',
                'province' => 'Jawa Barat',
                'regency' => 'Kota Bandung',
                'remove_photo' => 1,
            ]);

        $responseRemove->assertRedirect(route('profile.edit'));
        $contingent->refresh();
        $this->assertNull($contingent->photo);
    }
}
