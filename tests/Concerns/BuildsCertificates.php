<?php

namespace Tests\Concerns;

use App\Models\Contingent;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Result;
use App\Models\SubCategory;

trait BuildsCertificates
{
    /**
     * Buat chain lengkap: contingent → participant (NIK) → payment → registration verified.
     * Return registration.
     */
    protected function makeRegistration(
        ?array $resultAttributes = null,
        ?string $paymentStatus = 'verified',
        ?string $nik = '1234567890123456',
    ): Registration {
        $subCategory = SubCategory::factory()->create();

        $participant = \App\Models\Participant::factory()->create([
            'nik' => $nik,
            'name' => 'Atlet Publik',
            'contingent_id' => Contingent::factory(),
        ]);

        $payment = Payment::create([
            'contingent_id' => $participant->contingent_id,
            'event_id' => $subCategory->eventCategory->event_id,
            'total_amount' => 150000,
            'status' => $paymentStatus,
        ]);

        $registration = Registration::create([
            'participant_id' => $participant->id,
            'payment_id' => $payment->id,
            'sub_category_id' => $subCategory->id,
            'status_berkas' => 'verified',
        ]);

        if ($resultAttributes !== null) {
            Result::create([
                'registration_id' => $registration->id,
                ...$resultAttributes,
            ]);
        }

        return $registration;
    }
}
