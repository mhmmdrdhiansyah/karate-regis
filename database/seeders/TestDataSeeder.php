<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\SubCategory;
use App\Models\Contingent;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Participant;
use App\Enums\PaymentStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // 1. Create Event
        $event = Event::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'Kejuaraan Karate Test 2026',
                'poster' => null,
                'bank_name' => 'Bank BCA',
                'account_number' => '1234567890',
                'account_holder' => 'Panitia Karate',
                'event_date' => now()->addDays(10)->format('Y-m-d'),
                'registration_deadline' => now()->addDays(5),
                'coach_fee' => 100000,
                'event_fee' => 50000,
                'status' => 'registration_open',
            ]
        );

        // 2. Create Event Category
        $eventCategory = EventCategory::firstOrCreate(
            ['id' => 1],
            [
                'event_id' => $event->id,
                'type' => 'Open',
                'class_name' => 'Senior',
                'discount_type' => 'fixed',
                'discount_value' => 0,
                'min_birth_date' => '2000-01-01',
                'max_birth_date' => '2005-12-31',
            ]
        );

        // 3. Create Sub Category
        $subCategory = SubCategory::firstOrCreate(
            ['id' => 1],
            [
                'event_category_id' => $eventCategory->id,
                'name' => 'Kata Perorangan Senior Putra',
                'category_type' => 'individu',
                'discipline' => 'kata',
                'gender' => 'M',
                'price' => 150000,
                'min_participants' => 1,
                'max_participants' => 1,
                'max_teams' => 10,
            ]
        );

        // 4. Get existing contingent
        $contingent = Contingent::first();
        if (!$contingent) {
            $contingent = Contingent::create([
                'name' => 'Kontingen Test',
                'short_name' => 'TEST',
                'province' => 'DKI Jakarta',
                'photo' => null,
            ]);
        }

        // 5. Create Participants
        $participants = [];
        for ($i = 1; $i <= 3; $i++) {
            $participant = Participant::firstOrCreate(
                ['id' => $i],
                [
                    'contingent_id' => $contingent->id,
                    'name' => "Atlet Test {$i}",
                    'nik' => "12345678901234" . $i,
                    'birth_date' => '2000-01-01',
                    'gender' => 'M',
                    'type' => 'athlete',
                    'photo' => 'participants/default.jpg',
                    'sport_id' => 1, // Karate
                    'is_verified' => false,
                ]
            );
            $participants[] = $participant;
        }

        // 6. Create Payments with different statuses
        $payments = [
            [
                'id' => 1,
                'status' => PaymentStatus::Pending,
                'amount' => 450000, // 3 participants x 150000
                'created_at' => now()->subDays(2),
            ],
            [
                'id' => 2,
                'status' => PaymentStatus::Verified,
                'amount' => 300000, // 2 participants x 150000
                'created_at' => now()->subDays(5),
                'verified_at' => now()->subDays(4),
                'verified_by' => 1, // admin
            ],
            [
                'id' => 3,
                'status' => PaymentStatus::Rejected,
                'amount' => 150000, // 1 participant x 150000
                'created_at' => now()->subDays(3),
                'rejection_reason' => 'Bukti transfer tidak lengkap',
            ],
        ];

        foreach ($payments as $paymentData) {
            $payment = Payment::firstOrCreate(
                ['id' => $paymentData['id']],
                [
                    'contingent_id' => $contingent->id,
                    'event_id' => $event->id,
                    'total_amount' => $paymentData['amount'],
                    'status' => $paymentData['status'],
                    'transfer_proof' => null,
                    'verified_at' => $paymentData['verified_at'] ?? null,
                    'verified_by' => $paymentData['verified_by'] ?? null,
                    'rejection_reason' => $paymentData['rejection_reason'] ?? null,
                    'created_at' => $paymentData['created_at'],
                    'updated_at' => now(),
                ]
            );

            // Create registrations for each payment
            $participantCount = $paymentData['amount'] / 150000;
            for ($i = 0; $i < $participantCount; $i++) {
                $participantIndex = ($paymentData['id'] - 1) + $i;
                if (isset($participants[$participantIndex])) {
                    Registration::firstOrCreate(
                        [
                            'payment_id' => $payment->id,
                            'participant_id' => $participants[$participantIndex]->id,
                            'sub_category_id' => $subCategory->id,
                        ],
                        [
                            'status_berkas' => $paymentData['status'] === PaymentStatus::Verified
                                ? 'verified'
                                : 'unsubmitted',
                            'verified_at' => $paymentData['verified_at'] ?? null,
                            'verified_by' => $paymentData['verified_by'] ?? null,
                        ]
                    );
                }
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->command->info('Test data created successfully!');
        $this->command->info('Created:');
        $this->command->info('- 1 Event (Kejuaraan Karate Test 2026)');
        $this->command->info('- 1 Event Category (Kata Senior)');
        $this->command->info('- 1 Sub Category (Kata Perorangan Senior Putra)');
        $this->command->info('- 1 Contingent (Kontingen Test)');
        $this->command->info('- 3 Participants');
        $this->command->info('- 3 Payments (1 Pending, 1 Verified, 1 Rejected)');
    }
}