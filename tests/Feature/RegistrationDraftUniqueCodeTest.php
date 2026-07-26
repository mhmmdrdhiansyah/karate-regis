<?php

use App\Enums\EventStatus;
use App\Models\Contingent;
use App\Models\Event;
use App\Models\RegistrationDraft;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->event = Event::create([
        'name' => 'Test Event',
        'event_date' => now()->addDays(20),
        'registration_deadline' => now()->addDays(10),
        'status' => EventStatus::RegistrationOpen,
        'coach_fee' => 50000,
        'event_fee' => 100000,
    ]);
});

function makeContingent(): Contingent
{
    $user = User::factory()->create(['username' => 'user_' . Str::random(5)]);

    return Contingent::create([
        'user_id' => $user->id,
        'name' => 'Dojo ' . Str::random(5),
        'official_name' => 'Official ' . Str::random(5),
        'phone' => '08123456789',
        'address' => 'Address',
    ]);
}

it('generates and persists a unique code once per draft', function () {
    $contingent = makeContingent();
    $draft = RegistrationDraft::create([
        'contingent_id' => $contingent->id,
        'event_id' => $this->event->id,
        'status' => 'draft',
    ]);

    $first = $draft->getOrAssignUniqueCode();

    expect($first)
        ->toBeGreaterThanOrEqual(100)
        ->toBeLessThanOrEqual(999)
        ->and((int) RegistrationDraft::find($draft->id)->unique_code)->toBe($first);

    // Stable: a second call returns the same value (not regenerated).
    expect($draft->getOrAssignUniqueCode())->toBe($first)
        ->and((int) RegistrationDraft::find($draft->id)->unique_code)->toBe($first);
});

it('assigns different unique codes to different contingents in the same event', function () {
    $draftA = RegistrationDraft::create([
        'contingent_id' => makeContingent()->id,
        'event_id' => $this->event->id,
        'status' => 'draft',
    ]);
    $draftB = RegistrationDraft::create([
        'contingent_id' => makeContingent()->id,
        'event_id' => $this->event->id,
        'status' => 'draft',
    ]);

    expect($draftA->getOrAssignUniqueCode())->not->toBe($draftB->getOrAssignUniqueCode());
});
