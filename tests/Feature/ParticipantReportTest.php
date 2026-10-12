<?php

namespace Tests\Feature;

use App\Models\Contingent;
use App\Models\Participant;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ParticipantReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $this->admin = User::factory()->create()->assignRole('super-admin');
    }

    private function makeRegistration(string $statusBerkas, bool $isVerified, ?string $rejectionReason = null): Registration
    {
        $subCategory = SubCategory::factory()->create();

        $participant = Participant::factory()->create([
            'contingent_id' => Contingent::factory(),
            'is_verified' => $isVerified,
            'rejection_reason' => $rejectionReason,
        ]);

        $payment = Payment::create([
            'contingent_id' => $participant->contingent_id,
            'event_id' => $subCategory->eventCategory->event_id,
            'total_amount' => 150000,
            'status' => 'verified',
        ]);

        return Registration::create([
            'participant_id' => $participant->id,
            'payment_id' => $payment->id,
            'sub_category_id' => $subCategory->id,
            'status_berkas' => $statusBerkas,
        ]);
    }

    #[Test]
    public function participants_report_loads_with_pending_participants(): void
    {
        // is_verified=false + rejection_reason=null → cabang "pending" formatRegistrationRecord
        $this->makeRegistration('pending_review', isVerified: false);

        $this->actingAs($this->admin)
            ->get('/reports/participants')
            ->assertOk();
    }

    #[Test]
    public function participants_pdf_shows_correct_status_badge(): void
    {
        $this->makeRegistration('verified', isVerified: true);
        $this->makeRegistration('pending_review', isVerified: false);

        $this->actingAs($this->admin)
            ->get('/reports/participants/pdf')
            ->assertOk()
            ->assertSee('tag-status-verified')
            ->assertSee('tag-status-pending');
    }
}
