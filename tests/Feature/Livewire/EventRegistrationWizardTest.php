<?php

use App\Livewire\EventRegistrationWizard;
use App\Models\User;
use App\Models\Contingent;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\SubCategory;
use App\Models\Payment;
use App\Enums\EventStatus;
use App\Enums\EventCategoryType;
use App\Enums\SubCategoryGender;
use App\Enums\PaymentStatus;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['username' => 'user_' . Str::random(5)]);
    $this->contingent = Contingent::create([
        'user_id' => $this->user->id,
        'name' => 'Dojo Testing',
        'official_name' => 'Manager Dojo',
        'phone' => '08123456789',
        'address' => 'Test Address',
    ]);
});

it('renders the component and shows open events', function () {
    $event = Event::create([
        'name' => 'Test Event Open',
        'event_date' => now()->addDays(20),
        'registration_deadline' => now()->addDays(10),
        'status' => EventStatus::RegistrationOpen,
        'coach_fee' => 50000,
        'event_fee' => 100000,
    ]);

    Livewire::test(EventRegistrationWizard::class)
        ->assertStatus(200)
        ->assertSee('Test Event Open')
        ->assertSee('Pilih Event');
});

it('validates contingent data before selecting event', function () {
    $userWithoutContingent = User::factory()->create(['username' => 'user_' . Str::random(5)]);
    $this->actingAs($userWithoutContingent);

    $event = Event::create([
        'name' => 'Test Event Open',
        'event_date' => now()->addDays(20),
        'registration_deadline' => now()->addDays(10),
        'status' => EventStatus::RegistrationOpen,
        'coach_fee' => 50000,
        'event_fee' => 100000,
    ]);

    Livewire::test(EventRegistrationWizard::class)
        ->call('selectEvent', $event->id)
        ->assertSee('Anda belum memiliki data kontingen');
});

it('validates existing payment before selecting event', function () {
    $this->actingAs($this->user);

    $event = Event::create([
        'name' => 'Test Event Open',
        'event_date' => now()->addDays(20),
        'registration_deadline' => now()->addDays(10),
        'status' => EventStatus::RegistrationOpen,
        'coach_fee' => 50000,
        'event_fee' => 100000,
    ]);

    Payment::create([
        'event_id' => $event->id,
        'contingent_id' => $this->contingent->id,
        'total_amount' => 100000,
        'status' => PaymentStatus::Pending,
    ]);

    Livewire::test(EventRegistrationWizard::class)
        ->call('selectEvent', $event->id)
        ->assertSee('Anda sudah memiliki invoice aktif untuk event ini');
});

it('navigates through steps correctly', function () {
    $this->actingAs($this->user);

    $event = Event::create([
        'name' => 'Test Event Open',
        'event_date' => now()->addDays(20),
        'registration_deadline' => now()->addDays(10),
        'status' => EventStatus::RegistrationOpen,
        'coach_fee' => 50000,
        'event_fee' => 100000,
    ]);

    $category = EventCategory::create([
        'event_id' => $event->id,
        'type' => EventCategoryType::Open,
        'class_name' => 'Senior',
        'min_birth_date' => now()->subYears(30),
        'max_birth_date' => now()->subYears(20),
    ]);

    Livewire::test(EventRegistrationWizard::class)
        ->call('selectEvent', $event->id)
        ->assertSet('currentStep', 2)
        ->assertSee('Senior')
        ->call('selectCategory', $category->id)
        ->assertSet('currentStep', 3);
});

it('redirects to create registration form after selecting sub-category', function () {
    $this->actingAs($this->user);

    $event = Event::create([
        'name' => 'Test Event Open',
        'event_date' => now()->addDays(20),
        'registration_deadline' => now()->addDays(10),
        'status' => EventStatus::RegistrationOpen,
        'coach_fee' => 50000,
        'event_fee' => 100000,
    ]);

    $category = EventCategory::create([
        'event_id' => $event->id,
        'type' => EventCategoryType::Open,
        'class_name' => 'Senior',
        'min_birth_date' => now()->subYears(30),
        'max_birth_date' => now()->subYears(20),
    ]);

    $subCategory = SubCategory::create([
        'event_category_id' => $category->id,
        'name' => 'Kumite -55kg',
        'gender' => SubCategoryGender::Male,
        'min_participants' => 1,
        'max_participants' => 1,
        'price' => 150000,
    ]);

    Livewire::test(EventRegistrationWizard::class)
        ->set('selectedEventId', $event->id)
        ->set('selectedCategoryId', $category->id)
        ->call('selectSubCategory', $subCategory->id)
        ->assertRedirect(route('registration.create', [
            'event' => $event->id,
            'category' => $category->id,
            'sub_category' => $subCategory->id,
        ]));
});
