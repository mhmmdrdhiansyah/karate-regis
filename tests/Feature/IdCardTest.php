<?php

namespace Tests\Feature;

use App\Models\Contingent;
use App\Models\Event;
use App\Models\IdCardTemplate;
use App\Models\Registration;
use App\Models\Participant;
use App\Models\User;
use App\Services\IdCardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Concerns\BuildsCertificates;
use Tests\TestCase;

class IdCardTest extends TestCase
{
    use BuildsCertificates, RefreshDatabase;

    #[Test]
    public function peserta_verified_dapat_kartu_per_eventnya(): void
    {
        $reg = $this->makeRegistration();
        $event = $reg->subCategory->eventCategory->event;

        $cards = app(IdCardService::class)->forParticipant($reg->participant);

        $this->assertCount(1, $cards);
        $this->assertSame($event->id, $cards[0]['event']->id);
        $this->assertSame($reg->participant->name, $cards[0]['participant']->name);
    }

    #[Test]
    public function registrasi_belum_verified_tidak_dapat_kartu(): void
    {
        $reg = $this->makeRegistration(null, 'pending');
        $reg->update(['status_berkas' => 'pending_review']);

        $cards = app(IdCardService::class)->forParticipant($reg->participant);

        $this->assertCount(0, $cards);
    }

    #[Test]
    public function template_event_spesifik_menang_atas_fallback_global(): void
    {
        $reg = $this->makeRegistration();
        $event = $reg->subCategory->eventCategory->event;

        IdCardTemplate::create([
            'name' => 'Global',
            'image_path' => 'id-card-templates/global.png',
        ]);

        $specific = IdCardTemplate::create([
            'event_id' => $event->id,
            'name' => 'Khusus Event',
            'image_path' => 'id-card-templates/event.png',
        ]);

        $resolved = app(IdCardService::class)->resolveTemplate($event);

        $this->assertTrue($resolved->is($specific));
    }

    #[Test]
    public function event_tanpa_template_apapun_return_null(): void
    {
        $reg = $this->makeRegistration();

        $this->assertNull(app(IdCardService::class)->resolveTemplate($reg->subCategory->eventCategory->event));
    }

    #[Test]
    public function lookup_nik_menampilkan_halaman_kartu(): void
    {
        $reg = $this->makeRegistration(null, 'verified', '3201234567890001');

        $this->post('/id-cards', ['nik' => '3201234567890001'])
            ->assertOk()
            ->assertSee($reg->participant->name);
    }

    #[Test]
    public function lookup_nik_tidak_dikenal_kembali_dengan_error(): void
    {
        $this->from('/id-cards')
            ->post('/id-cards', ['nik' => '9999999999999999'])
            ->assertSessionHasErrors('nik');
    }

    #[Test]
    public function lookup_nama_ganda_menampilkan_kandidat(): void
    {
        $a = $this->makeRegistration(null, 'verified', '3201234567890001');
        $a->participant->update(['birth_date' => '2010-05-15']);

        $b = $this->makeRegistration(null, 'verified', '3201234567890002');
        $b->participant->update([
            'name' => $a->participant->name,
            'birth_date' => '2010-05-15',
        ]);

        $this->post('/id-cards/nama', [
            'nama' => $a->participant->name,
            'birth_date' => '2010-05-15',
        ])->assertOk()->assertSee('Kandidat');
    }

    #[Test]
    public function pdf_kartu_butuh_template_aktif(): void
    {
        $reg = $this->makeRegistration(null, 'verified', '3201234567890001');
        $event = $reg->subCategory->eventCategory->event;

        $url = URL::temporarySignedRoute('id-cards.pdf', now()->addMinutes(10), [
            'participant' => $reg->participant->id,
            'event' => $event->id,
        ]);

        $this->get($url)->assertNotFound();

        IdCardTemplate::create([
            'name' => 'Global',
            'image_path' => 'id-card-templates/global.png',
        ]);

        $this->get($url)->assertOk();
    }

    #[Test]
    public function pdf_kartu_tanpa_signature_ditolak(): void
    {
        $reg = $this->makeRegistration(null, 'verified', '3201234567890001');
        $event = $reg->subCategory->eventCategory->event;

        IdCardTemplate::create([
            'name' => 'Global',
            'image_path' => 'id-card-templates/global.png',
        ]);

        // URL polos tanpa signature → 403 (middleware signed), bukan redirect login
        $this->get("/id-cards/{$reg->participant->id}/{$event->id}/pdf")
            ->assertForbidden();
    }

    #[Test]
    public function pdf_kartu_signature_kedaluwarsa_ditolak(): void
    {
        $reg = $this->makeRegistration(null, 'verified', '3201234567890001');
        $event = $reg->subCategory->eventCategory->event;

        IdCardTemplate::create([
            'name' => 'Global',
            'image_path' => 'id-card-templates/global.png',
        ]);

        $url = URL::temporarySignedRoute('id-cards.pdf', now()->addMinutes(10), [
            'participant' => $reg->participant->id,
            'event' => $event->id,
        ]);

        $this->travel(11)->minutes();
        $this->get($url)->assertForbidden();
    }

    #[Test]
    public function tombol_cetak_idcard_muncul_di_detail_peserta(): void
    {
        Role::firstOrCreate(['name' => 'kontingen', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view participants', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole('kontingen');
        $user->givePermissionTo('view participants');

        $kontingen = Contingent::factory()->create(['user_id' => $user->id]);

        $reg = $this->makeRegistration();
        $reg->participant->update(['contingent_id' => $kontingen->id]);

        $this->actingAs($user)
            ->get("/participants/{$reg->participant->id}")
            ->assertOk()
            ->assertSee('id-cards');
    }

    #[Test]
    public function tombol_cetak_idcard_muncul_per_event_di_detail_peserta(): void
    {
        Role::firstOrCreate(['name' => 'kontingen', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view participants', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole('kontingen');
        $user->givePermissionTo('view participants');

        $kontingen = Contingent::factory()->create(['user_id' => $user->id]);

        // Peserta ikut 2 event berbeda: registrasi B diarahkan ke participant A
        $regA = $this->makeRegistration(null, 'verified', '3201234567890001');
        $regB = $this->makeRegistration(null, 'verified', '3201234567890002');
        $regA->participant->update(['contingent_id' => $kontingen->id]);
        Registration::where('id', $regB->id)->update(['participant_id' => $regA->participant->id]);

        // Buat IdCardTemplate global supaya kartu bisa dibuat
        IdCardTemplate::create([
            'name' => 'Global',
            'image_path' => 'id-card-templates/global.png',
        ]);

        $response = $this->actingAs($user)
            ->get("/participants/{$regA->participant->id}");
        $response->assertOk();

        // Tepat 2 tombol PDF id-cards (per event), bukan 1 (event pertama saja)
        $this->assertSame(2, substr_count($response->getContent(), '/id-cards/'));
        $this->assertStringContainsString('signature=', $response->getContent());
    }
}
