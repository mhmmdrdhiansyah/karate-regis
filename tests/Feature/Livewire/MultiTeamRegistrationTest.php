<?php

namespace Tests\Feature\Livewire;

use App\Enums\ParticipantGender;
use App\Enums\ParticipantType;
use App\Livewire\AthleteSelectionForm;
use App\Livewire\EventRegistrationInvoice;
use App\Models\Contingent;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Participant;
use App\Models\RegistrationDraft;
use App\Models\RegistrationDraftItem;
use App\Models\SubCategory;
use App\Models\TeamGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MultiTeamRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Contingent $contingent;
    protected Event $event;
    protected EventCategory $category;
    protected SubCategory $subCategoryBeregu;
    protected SubCategory $subCategoryIndividu;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->contingent = Contingent::factory()->create(['user_id' => $this->user->id]);
        $this->event = Event::factory()->create();
        
        $this->category = EventCategory::factory()->create([
            'event_id' => $this->event->id,
            'min_birth_date' => now()->subYears(20),
            'max_birth_date' => now()->subYears(10),
        ]);

        $this->subCategoryBeregu = SubCategory::factory()->beregu()->create([
            'event_category_id' => $this->category->id,
            'price' => 300000,
            'max_teams' => 2,
            'min_participants' => 3,
            'max_participants' => 3,
        ]);

        $this->subCategoryIndividu = SubCategory::factory()->create([
            'event_category_id' => $this->category->id,
            'price' => 150000,
            'min_participants' => 1,
            'max_participants' => 1,
        ]);

        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_create_multiple_teams_for_beregu_category()
    {
        Livewire::test(AthleteSelectionForm::class, [
            'event' => $this->event->id,
            'category' => $this->category->id,
            'sub_category' => $this->subCategoryBeregu->id
        ])
        ->call('createTeam')
        ->assertSet('activeTeamId', function($id) { return $id !== null; })
        ->call('createTeam')
        ->assertCount('teams', 2)
        ->call('createTeam') // Should fail because max_teams is 2
        ->assertSee('Maksimal 2 tim');
    }

    /** @test */
    public function it_cannot_add_same_athlete_to_two_different_teams()
    {
        $athlete = Participant::factory()->create([
            'contingent_id' => $this->contingent->id,
            'gender' => ParticipantGender::Male,
            'birth_date' => now()->subYears(15),
            'type' => ParticipantType::Athlete
        ]);

        $component = Livewire::test(AthleteSelectionForm::class, [
            'event' => $this->event->id,
            'category' => $this->category->id,
            'sub_category' => $this->subCategoryBeregu->id
        ])
        ->call('createTeam'); // Team 1
        
        $team1Id = $component->get('activeTeamId');
        
        $component->call('toggleTeamMember', $athlete->id)
            ->call('createTeam'); // Team 2
            
        $team2Id = $component->get('activeTeamId');
        
        $component->call('toggleTeamMember', $athlete->id)
            ->assertSee('Atlet sudah terdaftar di tim lain');
            
        $this->assertEquals(1, RegistrationDraftItem::where('participant_id', $athlete->id)->count());
    }

    /** @test */
    public function it_validates_min_participants_per_team_before_invoice()
    {
        $athletes = Participant::factory()->count(2)->create([
            'contingent_id' => $this->contingent->id,
            'gender' => ParticipantGender::Male,
            'birth_date' => now()->subYears(15),
            'type' => ParticipantType::Athlete
        ]);

        // Create draft with incomplete team (needs 3, only has 2)
        $draft = RegistrationDraft::create([
            'contingent_id' => $this->contingent->id,
            'event_id' => $this->event->id,
            'status' => 'draft'
        ]);

        $team = TeamGroup::create([
            'contingent_id' => $this->contingent->id,
            'sub_category_id' => $this->subCategoryBeregu->id,
            'team_name' => 'Tim A',
            'team_number' => 1
        ]);

        foreach ($athletes as $a) {
            RegistrationDraftItem::create([
                'registration_draft_id' => $draft->id,
                'participant_id' => $a->id,
                'sub_category_id' => $this->subCategoryBeregu->id,
                'team_group_id' => $team->id
            ]);
        }

        Livewire::test(EventRegistrationInvoice::class, ['event' => $this->event->id])
            ->call('submit')
            ->assertSee('harus berisi 3-3 atlet');
    }

    /** @test */
    public function it_calculates_correct_fee_for_multiple_teams()
    {
        $athletes = Participant::factory()->count(6)->create([
            'contingent_id' => $this->contingent->id,
            'gender' => ParticipantGender::Male,
            'birth_date' => now()->subYears(15),
            'type' => ParticipantType::Athlete
        ]);

        $draft = RegistrationDraft::create([
            'contingent_id' => $this->contingent->id,
            'event_id' => $this->event->id,
            'status' => 'draft'
        ]);

        // Team A
        $teamA = TeamGroup::create([
            'contingent_id' => $this->contingent->id,
            'sub_category_id' => $this->subCategoryBeregu->id,
            'team_name' => 'Tim A',
            'team_number' => 1
        ]);
        foreach ($athletes->take(3) as $a) {
            RegistrationDraftItem::create([
                'registration_draft_id' => $draft->id,
                'participant_id' => $a->id,
                'sub_category_id' => $this->subCategoryBeregu->id,
                'team_group_id' => $teamA->id
            ]);
        }

        // Team B
        $teamB = TeamGroup::create([
            'contingent_id' => $this->contingent->id,
            'sub_category_id' => $this->subCategoryBeregu->id,
            'team_name' => 'Tim B',
            'team_number' => 2
        ]);
        foreach ($athletes->skip(3)->take(3) as $a) {
            RegistrationDraftItem::create([
                'registration_draft_id' => $draft->id,
                'participant_id' => $a->id,
                'sub_category_id' => $this->subCategoryBeregu->id,
                'team_group_id' => $teamB->id
            ]);
        }

        // Total should be: Event Fee (250k) + 2 teams * 300k = 850k
        Livewire::test(EventRegistrationInvoice::class, ['event' => $this->event->id])
            ->assertSet('totalAmount', 850000);
    }

    /** @test */
    public function it_maintains_normal_behavior_for_individual_category()
    {
        $athlete = Participant::factory()->create([
            'contingent_id' => $this->contingent->id,
            'gender' => ParticipantGender::Male,
            'birth_date' => now()->subYears(15),
            'type' => ParticipantType::Athlete
        ]);

        Livewire::test(AthleteSelectionForm::class, [
            'event' => $this->event->id,
            'category' => $this->category->id,
            'sub_category' => $this->subCategoryIndividu->id
        ])
        ->set('selectedAthleteIds', [$athlete->id])
        ->assertCount('selectedAthleteIds', 1);

        $this->assertEquals(1, RegistrationDraftItem::where('participant_id', $athlete->id)->count());
        $this->assertNull(RegistrationDraftItem::first()->team_group_id);
    }
}
