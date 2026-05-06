<?php

use App\Livewire\CoachSelectionForm;
use App\Models\Contingent;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\User;
use App\Enums\ParticipantType;
use App\Enums\PaymentStatus;
use App\Enums\RegistrationStatus;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    // Create permission
    Permission::firstOrCreate(['name' => 'create registrations', 'guard_name' => 'web']);

    $this->contingent = Contingent::factory()->create();
    $this->user = $this->contingent->user;
    $this->user->givePermissionTo('create registrations');

    // Create coaches
    $this->coach1 = Participant::factory()->create([
        'type' => ParticipantType::Coach,
        'contingent_id' => $this->contingent->id,
        'name' => 'Coach One',
    ]);

    $this->coach2 = Participant::factory()->create([
        'type' => ParticipantType::Coach,
        'contingent_id' => $this->contingent->id,
        'name' => 'Coach Two',
    ]);

    // Create open event
    $this->event = Event::factory()->create([
        'registration_deadline' => Carbon::now()->addDays(7),
        'status' => \App\Enums\EventStatus::RegistrationOpen,
        'coach_fee' => 50000,
    ]);
});

test('user can see coach selection form', function () {
    $this->actingAs($this->user);

    Livewire::test(CoachSelectionForm::class)
        ->assertStatus(200)
        ->assertSee('Pendaftaran Pelatih');
});

test('user can select event and see coaches', function () {
    $this->actingAs($this->user);

    Livewire::test(CoachSelectionForm::class)
        ->set('selectedEventId', $this->event->id)
        ->assertSee('Coach One')
        ->assertSee('Coach Two');
});

test('user can register coaches', function () {
    $this->actingAs($this->user);

    Livewire::test(CoachSelectionForm::class)
        ->set('selectedEventId', $this->event->id)
        ->set('selectedCoachIds', [$this->coach1->id])
        ->assertSet('showSavedIndicator', true);

    $this->assertDatabaseHas('registration_draft_items', [
        'participant_id' => $this->coach1->id,
        'sub_category_id' => null,
    ]);

    $this->assertDatabaseHas('registration_drafts', [
        'contingent_id' => $this->contingent->id,
        'event_id' => $this->event->id,
    ]);
});

test('user can unregister coaches', function () {
    $this->actingAs($this->user);
    
    // Setup initial draft
    $draft = \App\Models\RegistrationDraft::create([
        'contingent_id' => $this->contingent->id,
        'event_id' => $this->event->id,
        'status' => 'draft',
    ]);
    
    \App\Models\RegistrationDraftItem::create([
        'registration_draft_id' => $draft->id,
        'participant_id' => $this->coach1->id,
        'sub_category_id' => null,
    ]);

    Livewire::test(CoachSelectionForm::class)
        ->set('selectedEventId', $this->event->id)
        ->set('selectedCoachIds', [])
        ->assertSet('showSavedIndicator', true);

    $this->assertDatabaseMissing('registration_draft_items', [
        'participant_id' => $this->coach1->id,
    ]);
});

test('cannot modify coaches if payment is confirmed', function () {
    $this->actingAs($this->user);
    
    // Setup confirmed registration
    $payment = \App\Models\Payment::create([
        'contingent_id' => $this->contingent->id,
        'event_id' => $this->event->id,
        'status' => PaymentStatus::Verified,
        'total_amount' => 50000,
    ]);
    
    Registration::create([
        'participant_id' => $this->coach1->id,
        'payment_id' => $payment->id,
        'sub_category_id' => null,
        'status_berkas' => RegistrationStatus::Verified,
    ]);

    Livewire::test(CoachSelectionForm::class)
        ->set('selectedEventId', $this->event->id)
        ->set('selectedCoachIds', [])
        ->assertSee('Pelatih yang sudah masuk invoice confirmed tidak dapat diubah');

    // Registration should still exist
    $this->assertDatabaseHas('registrations', [
        'participant_id' => $this->coach1->id,
    ]);
});
