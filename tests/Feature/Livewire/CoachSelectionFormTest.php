<?php

use App\Livewire\CoachSelectionForm;
use App\Models\Contingent;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\User;
use App\Enums\ParticipantType;
use App\Enums\PaymentStatus;
use App\Enums\EventStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create(['username' => 'user_' . Str::random(5)]);
    $this->contingent = Contingent::create([
        'user_id' => $this->user->id,
        'name' => 'Dojo Testing',
        'official_name' => 'Manager Dojo',
        'phone' => '08123456789',
        'address' => 'Test Address',
    ]);

    // Create coaches
    $this->coach1 = Participant::factory()->create([
        'type' => ParticipantType::Coach,
        'contingent_id' => $this->contingent->id,
        'name' => 'Coach One',
        'photo' => 'coach1.jpg',
    ]);

    $this->coach2 = Participant::factory()->create([
        'type' => ParticipantType::Coach,
        'contingent_id' => $this->contingent->id,
        'name' => 'Coach Two',
        'photo' => 'coach2.jpg',
    ]);

    // Create open event
    $this->event = Event::create([
        'name' => 'Test Event Open',
        'event_date' => now()->addDays(20),
        'registration_deadline' => now()->addDays(10),
        'status' => EventStatus::RegistrationOpen,
        'coach_fee' => 50000,
        'event_fee' => 100000,
    ]);
});

test('user can see coach selection form', function () {
    $this->actingAs($this->user);

    Livewire::test(CoachSelectionForm::class)
        ->assertStatus(200)
        ->assertSee('Pendaftaran Pelatih')
        ->assertSee('Pilih Event');
});

test('user can select event', function () {
    $this->actingAs($this->user);

    Livewire::test(CoachSelectionForm::class)
        ->set('selectedEventId', $this->event->id)
        ->assertSet('selectedEventId', $this->event->id);
});

test('user can select coaches and they are saved to registration', function () {
    $this->actingAs($this->user);

    Livewire::test(CoachSelectionForm::class)
        ->set('selectedEventId', $this->event->id)
        ->set('selectedCoachIds', [$this->coach1->id])
        ->assertHasNoErrors();

    $this->assertDatabaseHas('registrations', [
        'participant_id' => $this->coach1->id,
        'sub_category_id' => null,
    ]);
});

test('unchecking coach removes registration', function () {
    $this->actingAs($this->user);

    // First select a coach to create registration, then uncheck it
    Livewire::test(CoachSelectionForm::class)
        ->set('selectedEventId', $this->event->id)
        ->set('selectedCoachIds', [$this->coach1->id])
        ->assertHasNoErrors()
        ->set('selectedCoachIds', [])
        ->assertHasNoErrors();

    // Verify registration was soft-deleted
    $this->assertDatabaseHas('registrations', [
        'participant_id' => $this->coach1->id,
    ]);
    $this->assertSoftDeleted('registrations', [
        'participant_id' => $this->coach1->id,
    ]);
});

test('only shows open events', function () {
    $this->actingAs($this->user);

    $closedEvent = Event::create([
        'name' => 'Test Event Closed',
        'event_date' => now()->addDays(20),
        'registration_deadline' => now()->subDays(1),
        'status' => EventStatus::RegistrationClosed,
        'coach_fee' => 50000,
        'event_fee' => 100000,
    ]);

    Livewire::test(CoachSelectionForm::class)
        ->set('selectedEventId', $closedEvent->id)
        ->assertSet('selectedEventId', null)
        ->assertSee('Event tidak valid atau pendaftaran sudah ditutup');
});

test('validates user has contingent', function () {
    $userWithoutContingent = User::factory()->create(['username' => 'user_' . Str::random(5)]);

    $this->actingAs($userWithoutContingent);

    Livewire::test(CoachSelectionForm::class)
        ->assertStatus(403);
});

test('shows only coaches from users contingent', function () {
    $otherUser = User::factory()->create(['username' => 'user_' . Str::random(5)]);
    $otherContingent = Contingent::create([
        'user_id' => $otherUser->id,
        'name' => 'Other Dojo',
        'official_name' => 'Other Manager',
        'phone' => '08987654321',
        'address' => 'Other Address',
    ]);
    $otherCoach = Participant::factory()->create([
        'type' => ParticipantType::Coach,
        'contingent_id' => $otherContingent->id,
        'photo' => 'other-coach.jpg',
    ]);

    $this->actingAs($this->user);

    Livewire::test(CoachSelectionForm::class)
        ->set('selectedEventId', $this->event->id)
        ->assertSee($this->coach1->name)
        ->assertSee($this->coach2->name)
        ->assertDontSee($otherCoach->name);
});
