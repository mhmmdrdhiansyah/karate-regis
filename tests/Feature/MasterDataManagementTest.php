<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\Perguruan;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MasterDataManagementTest extends TestCase
{
    use DatabaseTransactions;

    private User $superAdmin;
    private User $panitia;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['super-admin', 'panitia', 'kontingen'] as $role) {
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'manage master data', 'guard_name' => 'web']);

        $this->superAdmin = User::factory()->create()->assignRole('super-admin');
        $this->panitia = User::factory()->create()->assignRole('panitia');
    }

    #[Test]
    public function page_forbidden_without_manage_master_data_permission(): void
    {
        $this->actingAs($this->panitia)
            ->get(route('master-data.index'))
            ->assertForbidden();
    }

    #[Test]
    public function page_accessible_for_super_admin(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('master-data.index'))
            ->assertOk()
            ->assertSeeLivewire('admin.master-data-management');
    }

    #[Test]
    public function can_create_sport(): void
    {
        $sportCode = 'panjat-'.uniqid();

        Livewire::actingAs($this->superAdmin)
            ->test('admin.master-data-management')
            ->set('sportName', 'Panjat Tebing')
            ->set('sportCode', $sportCode)
            ->call('saveSport')
            ->assertHasNoErrors()
            ->assertSet('sportName', '');

        $this->assertDatabaseHas('sports', [
            'name' => 'Panjat Tebing',
            'code' => $sportCode,
            'is_active' => true,
        ]);
    }

    #[Test]
    public function sport_requires_name(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test('admin.master-data-management')
            ->set('sportName', '')
            ->call('saveSport')
            ->assertHasErrors(['sportName']);
    }

    #[Test]
    public function can_toggle_sport_active(): void
    {
        $sport = Sport::create(['name' => 'Karate', 'code' => 'karate-'.uniqid(), 'is_active' => true]);

        Livewire::actingAs($this->superAdmin)
            ->test('admin.master-data-management')
            ->call('toggleSportActive', $sport->id);

        $this->assertFalse($sport->fresh()->is_active);
    }

    #[Test]
    public function cannot_delete_sport_that_still_has_perguruan(): void
    {
        $sport = Sport::create(['name' => 'Karate', 'code' => 'karate-'.uniqid(), 'is_active' => true]);
        Perguruan::create(['sport_id' => $sport->id, 'name' => 'INKAI', 'is_active' => true]);

        Livewire::actingAs($this->superAdmin)
            ->test('admin.master-data-management')
            ->call('deleteSport', $sport->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('sports', ['id' => $sport->id]);
        $this->assertDatabaseHas('perguruan', ['name' => 'INKAI']);
    }

    #[Test]
    public function can_delete_empty_sport(): void
    {
        $sport = Sport::create(['name' => 'Renang', 'code' => 'renang-'.uniqid(), 'is_active' => true]);

        Livewire::actingAs($this->superAdmin)
            ->test('admin.master-data-management')
            ->call('deleteSport', $sport->id);

        $this->assertDatabaseMissing('sports', ['id' => $sport->id]);
    }

    #[Test]
    public function can_create_perguruan_under_sport(): void
    {
        $sport = Sport::create(['name' => 'Karate', 'code' => 'karate-'.uniqid(), 'is_active' => true]);

        Livewire::actingAs($this->superAdmin)
            ->test('admin.master-data-management')
            ->set('selectedSportId', $sport->id)
            ->set('perguruanName', 'SHOTOKAI')
            ->call('savePerguruan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('perguruan', [
            'sport_id' => $sport->id,
            'name' => 'SHOTOKAI',
            'is_active' => true,
        ]);
    }

    #[Test]
    public function perguruan_requires_name_and_sport(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test('admin.master-data-management')
            ->set('perguruanName', 'INKAI')
            ->set('selectedSportId', null)
            ->call('savePerguruan')
            ->assertHasErrors(['selectedSportId']);
    }

    #[Test]
    public function can_delete_perguruan_without_touching_participants(): void
    {
        $sport = Sport::create(['name' => 'Karate', 'code' => 'karate-'.uniqid(), 'is_active' => true]);
        $perguruan = Perguruan::create(['sport_id' => $sport->id, 'name' => 'INKAI', 'is_active' => true]);

        // Participant menyimpan nama institusi sebagai string, bukan FK
        $participant = Participant::factory()->create([
            'institusi' => 'INKAI',
            'sport_id' => $sport->id,
        ]);

        Livewire::actingAs($this->superAdmin)
            ->test('admin.master-data-management')
            ->call('deletePerguruan', $perguruan->id);

        $this->assertDatabaseMissing('perguruan', ['id' => $perguruan->id]);
        $this->assertDatabaseHas('participants', [
            'id' => $participant->id,
            'institusi' => 'INKAI',
        ]);
    }
}
