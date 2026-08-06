<?php

namespace Tests\Feature;

use App\Enums\EventCategoryType;
use App\Enums\EventStatus;
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
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoryDiscountTest extends TestCase
{
    use RefreshDatabase;
    #[Test]
    public function it_calculates_fixed_discount_correctly(): void
    {
        $category = new EventCategory([
            'discount_type' => 'fixed',
            'discount_value' => 15000,
        ]);

        $this->assertEquals(15000, $category->calculateDiscountAmount(150000));
    }

    #[Test]
    public function it_calculates_percentage_discount_correctly(): void
    {
        $category = new EventCategory([
            'discount_type' => 'percentage',
            'discount_value' => 10,
        ]);

        // 10% of 150.000 = 15.000
        $this->assertEquals(15000, $category->calculateDiscountAmount(150000));
    }

    #[Test]
    public function it_returns_zero_when_no_discount(): void
    {
        $category = new EventCategory([
            'discount_type' => 'fixed',
            'discount_value' => 0,
        ]);

        $this->assertEquals(0, $category->calculateDiscountAmount(150000));
    }

    #[Test]
    public function it_calculates_invoice_total_with_percentage_and_fixed_discounts(): void
    {
        $user = User::factory()->create();
        $contingent = Contingent::factory()->create(['user_id' => $user->id]);
        $event = Event::factory()->create([
            'status' => EventStatus::RegistrationOpen,
            'event_fee' => 50000,
            'coach_fee' => 0,
        ]);

        // Category 1: 10% discount, SubCategory price 100.000 => Discount 10.000 per athlete
        $category1 = EventCategory::factory()->create([
            'event_id' => $event->id,
            'type' => EventCategoryType::Open,
            'class_name' => 'Kumite',
            'discount_type' => 'percentage',
            'discount_value' => 10,
        ]);
        $subCategory1 = SubCategory::factory()->create([
            'event_category_id' => $category1->id,
            'price' => 100000,
            'category_type' => 'individu',
            'min_participants' => 1,
            'max_participants' => 1,
        ]);

        // Category 2: Rp 15.000 fixed discount, SubCategory price 150.000 => Discount 15.000 per athlete
        $category2 = EventCategory::factory()->create([
            'event_id' => $event->id,
            'type' => EventCategoryType::Open,
            'class_name' => 'Kata',
            'discount_type' => 'fixed',
            'discount_value' => 15000,
        ]);
        $subCategory2 = SubCategory::factory()->create([
            'event_category_id' => $category2->id,
            'price' => 150000,
            'category_type' => 'individu',
            'min_participants' => 1,
            'max_participants' => 1,
        ]);

        $participant1 = Participant::factory()->create(['contingent_id' => $contingent->id]);
        $participant2 = Participant::factory()->create(['contingent_id' => $contingent->id]);

        $draft = RegistrationDraft::create([
            'event_id' => $event->id,
            'contingent_id' => $contingent->id,
            'status' => 'draft',
            'unique_code' => 123,
        ]);

        RegistrationDraftItem::create([
            'registration_draft_id' => $draft->id,
            'participant_id' => $participant1->id,
            'sub_category_id' => $subCategory1->id,
        ]);

        RegistrationDraftItem::create([
            'registration_draft_id' => $draft->id,
            'participant_id' => $participant2->id,
            'sub_category_id' => $subCategory2->id,
        ]);

        // Total athlete fee = 100.000 + 150.000 = 250.000
        // Event fee = 50.000
        // Unique code = 123
        // Total discount = 10.000 (10% of 100k) + 15.000 (fixed 15k) = 25.000
        // Expected totalAmount = 50.000 + 250.000 + 123 - 25.000 = 275.123

        Livewire::actingAs($user)
            ->test(\App\Livewire\EventRegistrationInvoice::class, ['event' => $event->id])
            ->assertSet('totalDiscount', 25000)
            ->assertSet('totalAmount', 275123)
            ->assertSee('-Rp 25.000');
    }
}
