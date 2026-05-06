<?php

namespace Tests\Feature\Livewire;

use App\Livewire\AthleteSelectionForm;
use App\Models\Contingent;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\SubCategory;
use App\Models\TeamGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TeamNameCustomizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Contingent $contingent;
    protected Event $event;
    protected EventCategory $category;
    protected SubCategory $subCategoryBeregu;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->contingent = Contingent::factory()->create(['user_id' => $this->user->id]);
        $this->event = Event::factory()->create();
        
        $this->category = EventCategory::factory()->create([
            'event_id' => $this->event->id,
        ]);

        $this->subCategoryBeregu = SubCategory::factory()->beregu()->create([
            'event_category_id' => $this->category->id,
            'category_type' => 'beregu',
        ]);

        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_update_team_name()
    {
        $component = Livewire::test(AthleteSelectionForm::class, [
            'event' => $this->event->id,
            'category' => $this->category->id,
            'sub_category' => $this->subCategoryBeregu->id
        ])
        ->call('createTeam');

        $team = TeamGroup::first();
        $this->assertEquals('Tim A', $team->team_name);

        $component->call('updateTeamName', $team->id, 'Garuda Team');

        $this->assertEquals('Garuda Team', $team->refresh()->team_name);
    }

    /** @test */
    public function it_reverts_to_default_name_if_input_is_empty()
    {
        $component = Livewire::test(AthleteSelectionForm::class, [
            'event' => $this->event->id,
            'category' => $this->category->id,
            'sub_category' => $this->subCategoryBeregu->id
        ])
        ->call('createTeam');

        $team = TeamGroup::first();
        $component->call('updateTeamName', $team->id, 'Custom Name');
        $this->assertEquals('Custom Name', $team->refresh()->team_name);

        $component->call('updateTeamName', $team->id, '');
        $this->assertEquals('Tim A', $team->refresh()->team_name);
    }
}
