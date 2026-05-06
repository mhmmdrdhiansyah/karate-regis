<?php

namespace Tests\Feature\Livewire;

use App\Enums\ParticipantGender;
use App\Enums\ParticipantType;
use App\Enums\SubCategoryGender;
use App\Livewire\EventRegistrationInvoice;
use App\Models\Contingent;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Participant;
use App\Models\RegistrationDraft;
use App\Models\RegistrationDraftItem;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MixedRegistrationInsertTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Contingent $contingent;
    protected Event $event;
    protected EventCategory $category;
    protected SubCategory $subCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->contingent = Contingent::factory()->create(['user_id' => $this->user->id]);
        $this->event = Event::factory()->create();
        
        $this->category = EventCategory::factory()->create([
            'event_id' => $this->event->id,
            'min_birth_date' => now()->subYears(20),
            'max_birth_date' => now()->addYear(),
        ]);

        $this->subCategory = SubCategory::factory()->create([
            'event_category_id' => $this->category->id,
            'category_type' => 'individu',
            'gender' => SubCategoryGender::Male,
        ]);

        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_insert_mixed_athlete_and_coach_registrations()
    {
        $athlete = Participant::factory()->create([
            'contingent_id' => $this->contingent->id,
            'type' => ParticipantType::Athlete,
            'gender' => ParticipantGender::Male,
            'birth_date' => now()->subYears(10),
        ]);

        $coach = Participant::factory()->create([
            'contingent_id' => $this->contingent->id,
            'type' => ParticipantType::Coach,
        ]);

        $draft = RegistrationDraft::create([
            'contingent_id' => $this->contingent->id,
            'event_id' => $this->event->id,
            'status' => 'draft',
        ]);

        // Add athlete to draft
        RegistrationDraftItem::create([
            'registration_draft_id' => $draft->id,
            'participant_id' => $athlete->id,
            'sub_category_id' => $this->subCategory->id,
        ]);

        // Add coach to draft
        RegistrationDraftItem::create([
            'registration_draft_id' => $draft->id,
            'participant_id' => $coach->id,
            'sub_category_id' => null, // Coach
        ]);

        $test = Livewire::test(EventRegistrationInvoice::class, ['event' => $this->event->id])
            ->call('submit');

        if ($test->get('errorMessage')) {
            dd('Error Message: ' . $test->get('errorMessage'));
        }

        if (session('error')) {
            dd('Session Error: ' . session('error'));
        }

        $test->assertHasNoErrors();
            
        $this->assertDatabaseCount('registrations', 2);
        $this->assertDatabaseHas('registrations', [
            'participant_id' => $athlete->id,
            'sub_category_id' => $this->subCategory->id,
        ]);
        $this->assertDatabaseHas('registrations', [
            'participant_id' => $coach->id,
            'sub_category_id' => null,
        ]);
    }
}
