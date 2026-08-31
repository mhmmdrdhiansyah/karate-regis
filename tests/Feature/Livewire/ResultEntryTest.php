<?php

namespace Tests\Feature\Livewire;

use App\Enums\PaymentStatus;
use App\Enums\SubCategoryGender;
use App\Livewire\Admin\ResultEntry;
use App\Models\Contingent;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Participant;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Result;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ResultEntryTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Event $event;
    protected EventCategory $category;
    protected SubCategory $subCategoryMale;
    protected SubCategory $subCategoryFemale;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'manage results', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'panitia', 'guard_name' => 'web']);

        $this->user = User::factory()->create();
        $this->user->givePermissionTo('manage results');
        $this->user->assignRole('panitia'); // EventPolicy::manage butuh role panitia + penugasan event

        $this->event = Event::factory()->create(['name' => 'Kejuaraan Karate 2026']);
        $this->event->panitia()->syncWithoutDetaching([$this->user->id]); // event-scoping: user harus ditugaskan
        $this->category = EventCategory::factory()->create(['event_id' => $this->event->id]);

        $this->subCategoryMale = SubCategory::factory()->create([
            'event_category_id' => $this->category->id,
            'name' => 'Kata Perorangan Male',
            'gender' => SubCategoryGender::Male,
        ]);

        $this->subCategoryFemale = SubCategory::factory()->create([
            'event_category_id' => $this->category->id,
            'name' => 'Kata Perorangan Female',
            'gender' => SubCategoryGender::Female,
        ]);

        $this->actingAs($this->user);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_renders_result_entry_and_displays_gender_in_subcategory_title()
    {
        Livewire::test(ResultEntry::class, ['event' => $this->event])
            ->assertSee('Kata Perorangan Male')
            ->assertSee('Kata Perorangan Female');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_groups_categories_by_type_with_open_first()
    {
        // Festival dibuat dulu di DB — Open tetap harus jadi tab pertama
        $festival = EventCategory::factory()->create([
            'event_id' => $this->event->id,
            'type' => \App\Enums\EventCategoryType::Festival,
            'class_name' => 'Pemula',
        ]);
        $open = EventCategory::factory()->create([
            'event_id' => $this->event->id,
            'type' => \App\Enums\EventCategoryType::Open,
            'class_name' => 'U21',
        ]);

        $component = Livewire::test(ResultEntry::class, ['event' => $this->event]);

        $grouped = $component->get('groupedCategories');
        $types = array_keys($grouped);
        $this->assertSame(['Open', 'Festival'], $types, 'Open harus jadi tipe pertama');

        $classes = collect($grouped['Open'])->pluck('class_name')->toArray();
        $this->assertContains('U21', $classes);
        $this->assertContains($this->category->class_name, $classes);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_only_renders_subcategories_of_active_type_and_class()
    {
        EventCategory::factory()->create([
            'event_id' => $this->event->id,
            'type' => \App\Enums\EventCategoryType::Festival,
            'class_name' => 'Pemula',
        ]);
        $festivalSub = SubCategory::factory()->create([
            'event_category_id' => EventCategory::where('event_id', $this->event->id)
                ->where('type', 'Festival')->first()->id,
            'name' => 'Kumite Festival Khusus',
        ]);

        // Default: tipe pertama (kategori utama setUp = type default factory), kelas pertama alphabet
        $component = Livewire::test(ResultEntry::class, ['event' => $this->event]);

        // Sub-kategori Festival tidak boleh ikut dirender sebelum tipe Festival dipilih
        $component->assertDontSee('Kumite Festival Khusus');

        // Pindah ke tipe Festival → kini tampil
        $component->call('selectType', 'Festival')
            ->assertSee('Kumite Festival Khusus');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_save_match_results_for_a_subcategory()
    {
        $contingent = Contingent::factory()->create(['user_id' => $this->user->id]);
        $participant = Participant::factory()->create(['contingent_id' => $contingent->id, 'name' => 'Budi Santoso']);
        $payment = Payment::create([
            'contingent_id' => $contingent->id,
            'event_id' => $this->event->id,
            'total_amount' => 150000,
            'status' => PaymentStatus::Verified,
        ]);
        $registration = Registration::create([
            'payment_id' => $payment->id,
            'sub_category_id' => $this->subCategoryMale->id,
            'participant_id' => $participant->id,
        ]);

        $component = Livewire::test(ResultEntry::class, ['event' => $this->event]);
        
        $slots = $component->get('slots.' . $this->subCategoryMale->id);
        $slots[0]['registration_id'] = $registration->id;
        $component->set('slots.' . $this->subCategoryMale->id, $slots);

        $component->call('save', $this->subCategoryMale->id)
            ->assertSee('Hasil berhasil disimpan!');

        $this->assertDatabaseHas('results', [
            'registration_id' => $registration->id,
            'rank_name' => 'Juara 1',
            'medal_type' => 'Gold',
        ]);
    }
}
