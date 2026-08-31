<?php

namespace Tests\Feature;

use App\Enums\EventCategoryType;
use App\Enums\MedalType;
use App\Models\Contingent;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Participant;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Result;
use App\Models\SubCategory;
use App\Services\StandingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StandingsServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function klasemen_hanya_menghitung_medali_tipe_open(): void
    {
        $event = Event::factory()->create();
        $kontingenA = Contingent::factory()->create();
        $kontingenB = Contingent::factory()->create();

        // Kategori OPEN: A dapat Gold
        $open = EventCategory::factory()->create([
            'event_id' => $event->id,
            'type' => EventCategoryType::Open,
        ]);
        $subOpen = SubCategory::factory()->create(['event_category_id' => $open->id]);
        $this->buatResultMedali($subOpen, $kontingenA, MedalType::Gold);

        // Kategori FESTIVAL: B dapat Gold — TIDAK boleh masuk klasemen
        $festival = EventCategory::factory()->create([
            'event_id' => $event->id,
            'type' => EventCategoryType::Festival,
        ]);
        $subFestival = SubCategory::factory()->create(['event_category_id' => $festival->id]);
        $this->buatResultMedali($subFestival, $kontingenB, MedalType::Gold);

        $standings = app(StandingsService::class)->forEvent($event);

        // Hanya kontingen A yang muncul (medali Festival B diabaikan)
        $this->assertCount(1, $standings);
        $this->assertSame($kontingenA->id, $standings[0]->contingent_id);
        $this->assertSame(1, (int) $standings[0]->gold); // SUM MySQL = string
    }

    #[Test]
    public function klasemen_kosong_bila_event_hanya_punya_kategori_festival(): void
    {
        $event = Event::factory()->create();
        $kontingen = Contingent::factory()->create();

        $festival = EventCategory::factory()->create([
            'event_id' => $event->id,
            'type' => EventCategoryType::Festival,
        ]);
        $subFestival = SubCategory::factory()->create(['event_category_id' => $festival->id]);
        $this->buatResultMedali($subFestival, $kontingen, MedalType::Gold);

        $standings = app(StandingsService::class)->forEvent($event);

        $this->assertSame([], $standings);
    }

    /**
     * Helper: bikin chain contingent → participant → payment → registration → result bermedali.
     */
    private function buatResultMedali(SubCategory $subCategory, Contingent $kontingen, MedalType $medal): void
    {
        $participant = Participant::factory()->create([
            'contingent_id' => $kontingen->id,
        ]);

        $payment = Payment::create([
            'contingent_id' => $kontingen->id,
            'event_id' => $subCategory->eventCategory->event_id,
            'total_amount' => 150000,
            'status' => 'verified',
        ]);

        $registration = Registration::create([
            'participant_id' => $participant->id,
            'payment_id' => $payment->id,
            'sub_category_id' => $subCategory->id,
            'status_berkas' => 'verified',
        ]);

        Result::create([
            'registration_id' => $registration->id,
            'medal_type' => $medal,
            'rank_name' => 'Juara 1',
        ]);
    }
}
