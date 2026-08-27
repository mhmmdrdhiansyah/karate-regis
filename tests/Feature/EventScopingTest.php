<?php

namespace Tests\Feature;

use App\Enums\EventStatus;
use App\Models\Contingent;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\BuildsCertificates;
use Tests\TestCase;

class EventScopingTest extends TestCase
{
    use BuildsCertificates, RefreshDatabase;

    private User $superAdmin;
    private User $panitiaA;
    private User $panitiaB;
    private User $kontingen;
    private Event $event1;
    private Event $event2;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['super-admin', 'panitia', 'kontingen'] as $role) {
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // Samakan permission panitia dengan RolesAndPermissionsSeeder agar
        // lolos middleware permission di route admin. Catatan: 'assign event
        // panitia' sengaja TIDAK diberikan — penugasan panitia hanya super-admin.
        $panitiaPermissions = [
            'view dashboard', 'view events', 'create events', 'edit events', 'delete events',
            'transition events', 'manage events', 'manage event categories', 'manage sub-categories',
            'manage participants', 'verify payments', 'verify documents', 'manage registrations',
            'view registrations', 'edit registrations', 'delete registrations', 'view reports',
            'manage results', 'manage event files', 'export reports',
        ];
        foreach ($panitiaPermissions as $perm) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $this->superAdmin = User::factory()->create()->assignRole('super-admin');
        $this->panitiaA = User::factory()->create()->assignRole('panitia');
        $this->panitiaB = User::factory()->create()->assignRole('panitia');
        $this->panitiaA->givePermissionTo($panitiaPermissions);
        $this->panitiaB->givePermissionTo($panitiaPermissions);

        $kontingenUser = User::factory()->create()->assignRole('kontingen');
        $kontingenUser->contingent()->create([
            'name' => 'Kontingen Test',
            'official_name' => 'Kontingen Test Official',
        ]);
        $this->kontingen = $kontingenUser;

        $this->event1 = Event::factory()->create(['name' => 'Event Satu']);
        $this->event2 = Event::factory()->create(['name' => 'Event Dua']);

        $this->event1->panitia()->attach($this->panitiaA->id);
        $this->event2->panitia()->attach($this->panitiaB->id);
    }

    #[Test]
    public function panitia_query_only_returns_assigned_events(): void
    {
        $this->actingAs($this->panitiaA);

        $this->assertSame(['Event Satu'], Event::pluck('name')->all());
    }

    #[Test]
    public function super_admin_sees_all_events(): void
    {
        $this->actingAs($this->superAdmin);

        $this->assertSame(2, Event::count());
    }

    #[Test]
    public function kontingen_sees_all_events(): void
    {
        $this->actingAs($this->kontingen);

        $this->assertSame(2, Event::count());
    }

    #[Test]
    public function guest_sees_all_events(): void
    {
        $this->assertSame(2, Event::count());
    }

    #[Test]
    public function payments_scoped_to_managed_events(): void
    {
        $payment1 = $this->makePaymentFor($this->event1);
        $this->makePaymentFor($this->event2);

        $this->actingAs($this->panitiaA);

        $this->assertSame([$payment1->id], Payment::forManagedEvents()->pluck('id')->all());

        $this->actingAs($this->superAdmin);
        $this->assertSame(2, Payment::forManagedEvents()->count());
    }

    #[Test]
    public function registrations_scoped_via_payment(): void
    {
        $reg1 = $this->makeRegistrationFor($this->event1);
        $this->makeRegistrationFor($this->event2);

        $this->actingAs($this->panitiaA);

        $this->assertSame([$reg1->id], Registration::forManagedEvents()->pluck('id')->all());
    }

    #[Test]
    public function panitia_cannot_edit_foreign_event_via_direct_url(): void
    {
        // Global scope menyembunyikan event asing → binding gagal → 404
        $this->actingAs($this->panitiaA)
            ->get(route('admin.events.edit', $this->event2))
            ->assertNotFound();
    }

    #[Test]
    public function panitia_can_edit_own_event(): void
    {
        $this->actingAs($this->panitiaA)
            ->get(route('admin.events.edit', $this->event1))
            ->assertOk();
    }

    #[Test]
    public function completed_event_is_read_only_for_panitia(): void
    {
        $this->event1->update(['status' => EventStatus::Completed]);

        $this->actingAs($this->panitiaA);

        $this->assertFalse($this->panitiaA->can('manage', $this->event1));
        $this->get(route('admin.events.edit', $this->event1))->assertForbidden();

        // Super-admin tetap bisa
        $this->actingAs($this->superAdmin);
        $this->assertTrue($this->superAdmin->can('manage', $this->event1));
    }

    #[Test]
    public function panitia_sees_all_events_in_index_but_manage_button_scoped(): void
    {
        $this->actingAs($this->panitiaA)
            ->get(route('admin.events.index'))
            ->assertOk()
            ->assertSee('Event Satu')
            ->assertSee('Event Dua');

        $this->assertTrue($this->panitiaA->can('manage', $this->event1));
        $this->assertFalse($this->panitiaA->can('manage', $this->event2));
    }

    #[Test]
    public function creating_event_auto_assigns_creator_as_panitia(): void
    {
        $this->actingAs($this->panitiaA)
            ->post(route('admin.events.store'), [
                'name' => 'Event Baru Panitia A',
                'event_date' => now()->addMonth()->format('Y-m-d'),
                'registration_deadline' => now()->addWeeks(2)->format('Y-m-d'),
                'coach_fee' => 50000,
                'event_fee' => 100000,
                'status' => EventStatus::RegistrationOpen->value,
            ]);

        $event = Event::withoutGlobalScopes()->where('name', 'Event Baru Panitia A')->first();

        $this->assertNotNull($event);
        $this->assertTrue($event->panitia->contains($this->panitiaA));
    }

    #[Test]
    public function super_admin_can_assign_panitia(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('admin.events.panitia.assign', $this->event1), [
                'panitia_ids' => [$this->panitiaA->id, $this->panitiaB->id],
            ])
            ->assertRedirect();

        $this->assertSame(
            [$this->panitiaA->id, $this->panitiaB->id],
            $this->event1->panitia()->pluck('user_id')->sort()->values()->all()
        );
    }

    #[Test]
    public function panitia_cannot_assign_panitia(): void
    {
        // Assign panitia hanya untuk pemegang permission 'assign event panitia'
        // (praktisnya super-admin); panitia biasa ditolak meski di event sendiri.
        $this->actingAs($this->panitiaA)
            ->put(route('admin.events.panitia.assign', $this->event1), [
                'panitia_ids' => [$this->panitiaA->id, $this->panitiaB->id],
            ])
            ->assertForbidden();

        $this->assertSame(
            [$this->panitiaA->id],
            $this->event1->panitia()->pluck('user_id')->all()
        );
    }

    #[Test]
    public function panitia_without_events_sees_empty_dashboard_state(): void
    {
        $lonely = User::factory()->create()->assignRole('panitia');

        $this->actingAs($lonely)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Belum Ada Event yang Ditugaskan');
    }

    private function makePaymentFor(Event $event): Payment
    {
        return Payment::create([
            'contingent_id' => Contingent::factory()->create()->id,
            'event_id' => $event->id,
            'total_amount' => 150000,
            'status' => 'pending',
        ]);
    }

    private function makeRegistrationFor(Event $event): Registration
    {
        $subCategory = SubCategory::factory()->create();

        $participant = \App\Models\Participant::factory()->create([
            'contingent_id' => Contingent::factory()->create()->id,
        ]);

        $payment = Payment::create([
            'contingent_id' => $participant->contingent_id,
            'event_id' => $event->id,
            'total_amount' => 150000,
            'status' => 'verified',
        ]);

        return Registration::create([
            'participant_id' => $participant->id,
            'payment_id' => $payment->id,
            'sub_category_id' => $subCategory->id,
            'status_berkas' => 'pending_review',
        ]);
    }
}
