<?php

use App\Enums\MedalType;
use App\Enums\PaymentStatus;
use App\Enums\RegistrationStatus;
use App\Models\Contingent;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Participant;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\RegistrationDraft;
use App\Models\RegistrationDraftItem;
use App\Models\Result;
use App\Models\SubCategory;
use App\Models\User;
use App\Services\ParticipantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

function setupWorld(): array
{
    $user = User::factory()->create();
    $contingent = Contingent::factory()->create(['user_id' => $user->id]);
    $event = Event::factory()->create();
    $category = EventCategory::factory()->create([
        'event_id' => $event->id,
        'min_birth_date' => now()->subYears(20),
        'max_birth_date' => now()->subYears(10),
    ]);
    $subCategory = SubCategory::factory()->create(['event_category_id' => $category->id]);

    return compact('user', 'contingent', 'event', 'category', 'subCategory');
}

it('force-deletes soft-deleted registrations without throwing (regresses the 500 bug)', function () {
    ['contingent' => $contingent, 'event' => $event, 'subCategory' => $subCategory] = setupWorld();

    $participant = Participant::factory()->create(['contingent_id' => $contingent->id]);
    $payment = Payment::create([
        'contingent_id' => $contingent->id,
        'event_id' => $event->id,
        'total_amount' => 100000,
        'status' => PaymentStatus::Cancelled,
    ]);
    $registration = Registration::create([
        'participant_id' => $participant->id,
        'payment_id' => $payment->id,
        'sub_category_id' => $subCategory->id,
        'status_berkas' => RegistrationStatus::PendingReview,
    ]);

    // Simulate the production trigger: PaymentList::cancelPayment() soft-deletes the registration.
    $registration->delete();
    expect(Registration::withTrashed()->count())->toBe(1);

    // Previously this would throw a 1451 FK violation. Cascade must succeed.
    app(ParticipantService::class)->cascadeDelete($participant);

    expect(Participant::find($participant->id))->toBeNull()
        ->and(Registration::withTrashed()->count())->toBe(0)
        ->and(Payment::find($payment->id))->not->toBeNull(); // shared payment survives
});

it('deletes registration draft items belonging to the participant', function () {
    ['contingent' => $contingent, 'event' => $event, 'subCategory' => $subCategory] = setupWorld();

    $participant = Participant::factory()->create(['contingent_id' => $contingent->id]);
    $draft = RegistrationDraft::create([
        'contingent_id' => $contingent->id,
        'event_id' => $event->id,
        'status' => 'draft',
    ]);
    RegistrationDraftItem::create([
        'registration_draft_id' => $draft->id,
        'participant_id' => $participant->id,
        'sub_category_id' => $subCategory->id,
    ]);

    app(ParticipantService::class)->cascadeDelete($participant);

    expect(Participant::find($participant->id))->toBeNull()
        ->and(RegistrationDraftItem::count())->toBe(0)
        ->and(RegistrationDraft::find($draft->id))->not->toBeNull(); // shared draft survives
});

it('deletes competition results/medals tied to the participant', function () {
    ['contingent' => $contingent, 'event' => $event, 'subCategory' => $subCategory] = setupWorld();

    $participant = Participant::factory()->create(['contingent_id' => $contingent->id]);
    $payment = Payment::create([
        'contingent_id' => $contingent->id,
        'event_id' => $event->id,
        'total_amount' => 100000,
        'status' => PaymentStatus::Verified,
    ]);
    $registration = Registration::create([
        'participant_id' => $participant->id,
        'payment_id' => $payment->id,
        'sub_category_id' => $subCategory->id,
        'status_berkas' => RegistrationStatus::Verified,
    ]);
    Result::create([
        'registration_id' => $registration->id,
        'medal_type' => MedalType::Gold,
    ]);

    app(ParticipantService::class)->cascadeDelete($participant);

    expect(Result::count())->toBe(0)
        ->and(Registration::withTrashed()->count())->toBe(0);
});

it('does not affect other participants or their data in the same contingent', function () {
    ['contingent' => $contingent, 'event' => $event, 'subCategory' => $subCategory] = setupWorld();

    $a = Participant::factory()->create(['contingent_id' => $contingent->id]);
    $b = Participant::factory()->create(['contingent_id' => $contingent->id]);
    $payment = Payment::create([
        'contingent_id' => $contingent->id,
        'event_id' => $event->id,
        'total_amount' => 200000,
        'status' => PaymentStatus::Verified,
    ]);
    Registration::create([
        'participant_id' => $a->id, 'payment_id' => $payment->id,
        'sub_category_id' => $subCategory->id, 'status_berkas' => RegistrationStatus::Verified,
    ]);
    Registration::create([
        'participant_id' => $b->id, 'payment_id' => $payment->id,
        'sub_category_id' => $subCategory->id, 'status_berkas' => RegistrationStatus::Verified,
    ]);

    app(ParticipantService::class)->cascadeDelete($a);

    expect(Participant::find($a->id))->toBeNull()
        ->and(Participant::find($b->id))->not->toBeNull()
        ->and(Registration::where('participant_id', $b->id)->count())->toBe(1)
        ->and(Payment::find($payment->id))->not->toBeNull();
});

it('reports delete impact including soft-deleted registrations', function () {
    ['contingent' => $contingent, 'event' => $event, 'subCategory' => $subCategory] = setupWorld();

    $participant = Participant::factory()->create([
        'contingent_id' => $contingent->id,
        'name' => 'Atlet Uji',
    ]);
    $payment = Payment::create([
        'contingent_id' => $contingent->id, 'event_id' => $event->id,
        'total_amount' => 100000, 'status' => PaymentStatus::Cancelled,
    ]);
    $registration = Registration::create([
        'participant_id' => $participant->id, 'payment_id' => $payment->id,
        'sub_category_id' => $subCategory->id, 'status_berkas' => RegistrationStatus::PendingReview,
    ]);
    $registration->delete(); // soft-deleted

    $impact = app(ParticipantService::class)->getDeleteImpact($participant);

    expect($impact['participant']['name'])->toBe('Atlet Uji')
        ->and($impact['counts']['registrations'])->toBe(1)
        ->and($impact['counts']['results'])->toBe(0)
        ->and($impact['counts']['draft_items'])->toBe(0)
        ->and($impact['details']['registrations'])->toHaveCount(1);
});

it('returns delete-preview as json for an authorized user', function () {
    ['user' => $user, 'contingent' => $contingent] = setupWorld();
    Permission::findOrCreate('delete participants');
    $user->givePermissionTo('delete participants');

    $participant = Participant::factory()->create(['contingent_id' => $contingent->id]);

    $this->actingAs($user)
        ->getJson(route('participants.delete-preview', $participant))
        ->assertOk()
        ->assertJsonStructure([
            'participant' => ['name', 'type'],
            'counts' => ['registrations', 'results', 'draft_items'],
            'details' => ['registrations', 'results'],
        ]);
});

it('deletes a participant with connected data over HTTP without a 500', function () {
    ['user' => $user, 'contingent' => $contingent, 'event' => $event, 'subCategory' => $subCategory] = setupWorld();
    Permission::findOrCreate('delete participants');
    $user->givePermissionTo('delete participants');

    $participant = Participant::factory()->create(['contingent_id' => $contingent->id]);
    $payment = Payment::create([
        'contingent_id' => $contingent->id, 'event_id' => $event->id,
        'total_amount' => 100000, 'status' => PaymentStatus::Cancelled,
    ]);
    $registration = Registration::create([
        'participant_id' => $participant->id, 'payment_id' => $payment->id,
        'sub_category_id' => $subCategory->id, 'status_berkas' => RegistrationStatus::PendingReview,
    ]);
    $registration->delete(); // reproduce the exact production 500 trigger

    $this->actingAs($user)
        ->delete(route('participants.destroy', $participant))
        ->assertRedirect(route('participants.index'));

    expect(Participant::find($participant->id))->toBeNull()
        ->and(Registration::withTrashed()->count())->toBe(0)
        ->and(Payment::find($payment->id))->not->toBeNull();
});
