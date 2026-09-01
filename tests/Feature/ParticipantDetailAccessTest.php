<?php

namespace Tests\Feature;

use App\Models\Contingent;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ParticipantDetailAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $panitiaA;
    private User $panitiaB;
    private Participant $pesertaEventA;
    private Participant $pesertaLepas;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['super-admin', 'panitia', 'kontingen'] as $role) {
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // Permission yang dipakai route participants.* dan admin.participants.index
        foreach (['view participants', 'manage participants', 'delete participants'] as $perm) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $this->superAdmin = User::factory()->create()->assignRole('super-admin');
        $this->superAdmin->givePermissionTo(['view participants', 'manage participants']);

        $this->panitiaA = User::factory()->create()->assignRole('panitia');
        $this->panitiaA->givePermissionTo(['view participants', 'manage participants']);

        $this->panitiaB = User::factory()->create()->assignRole('panitia');
        $this->panitiaB->givePermissionTo(['view participants', 'manage participants']);

        $kontingen = Contingent::factory()->create();

        $this->pesertaEventA = Participant::factory()->create(['contingent_id' => $kontingen->id]);
        $this->pesertaLepas = Participant::factory()->create(['contingent_id' => $kontingen->id]);

        // pesertaEventA mendaftar event1; event1 dipegang panitiaA
        $event1 = Event::factory()->create();
        $event1->panitia()->attach($this->panitiaA->id);
        $sub = SubCategory::factory()->create();
        // SubCategory factory default nempel ke EventCategory baru; pastikan event-nya event1
        $sub->eventCategory->update(['event_id' => $event1->id]);
        $payment = Payment::create([
            'contingent_id' => $kontingen->id,
            'event_id' => $event1->id,
            'total_amount' => 150000,
            'status' => 'verified',
        ]);
        Registration::create([
            'participant_id' => $this->pesertaEventA->id,
            'payment_id' => $payment->id,
            'sub_category_id' => $sub->id,
            'status_berkas' => 'verified',
        ]);
    }

    #[Test]
    public function super_admin_bisa_buka_detail_semua_peserta(): void
    {
        $this->actingAs($this->superAdmin)
            ->get("/participants/{$this->pesertaEventA->id}")
            ->assertOk();

        $this->actingAs($this->superAdmin)
            ->get("/participants/{$this->pesertaLepas->id}")
            ->assertOk();
    }

    #[Test]
    public function panitia_hanya_bisa_buka_detail_peserta_eventnya(): void
    {
        // Peserta yang mendaftar event1 (dipegang panitiaA) → boleh
        $this->actingAs($this->panitiaA)
            ->get("/participants/{$this->pesertaEventA->id}")
            ->assertOk();

        // Peserta yang tidak mendaftar event panitiaA → 403
        $this->actingAs($this->panitiaA)
            ->get("/participants/{$this->pesertaLepas->id}")
            ->assertForbidden();

        // Panitia lain (pegang event lain) → 403 juga
        $this->actingAs($this->panitiaB)
            ->get("/participants/{$this->pesertaEventA->id}")
            ->assertForbidden();
    }

    #[Test]
    public function kontingen_tetap_hanya_bisa_buka_peserta_miliknya(): void
    {
        $kontingenUser = User::factory()->create()->assignRole('kontingen');
        $kontingenUser->contingent()->create([
            'name' => 'Kontingen Lain',
            'official_name' => 'Kontingen Lain Official',
        ]);
        $kontingenUser->givePermissionTo('view participants');

        // Peserta milik kontingen lain → 403
        $this->actingAs($kontingenUser)
            ->get("/participants/{$this->pesertaEventA->id}")
            ->assertForbidden();
    }

    #[Test]
    public function tombol_detail_muncul_di_tabel_daftar_peserta_untuk_super_admin_dan_panitia(): void
    {
        foreach ([$this->superAdmin, $this->panitiaA] as $user) {
            $response = $this->actingAs($user)
                ->get('/admin/participants')
                ->assertOk();

            $response->assertSee('participants/' . $this->pesertaEventA->id);
            $response->assertSee('bi-person-lines-fill');
        }
    }

    #[Test]
    public function panitia_tetap_tidak_bisa_menghapus_peserta_meski_diberi_permission_delete(): void
    {
        $this->panitiaA->givePermissionTo('delete participants');

        $this->actingAs($this->panitiaA)
            ->get("/participants/{$this->pesertaEventA->id}/delete-preview")
            ->assertForbidden();

        $this->actingAs($this->panitiaA)
            ->delete("/participants/{$this->pesertaEventA->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('participants', [
            'id' => $this->pesertaEventA->id,
        ]);
    }
}
