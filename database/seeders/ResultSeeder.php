<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\SubCategory;
use App\Models\Contingent;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\Result;
use App\Models\Payment;
use App\Enums\MedalType;
use App\Enums\RegistrationStatus;

class ResultSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan minimal ada 1 pendaftar di sistem agar bisa jalan
        if (Registration::count() < 4) {
            $event = Event::first() ?? Event::factory()->create();
            $category = EventCategory::where('event_id', $event->id)->first() ?? EventCategory::factory()->create(['event_id' => $event->id]);
            $subCategory = SubCategory::where('event_category_id', $category->id)->first() ?? SubCategory::factory()->create(['event_category_id' => $category->id]);

            $needed = 4 - Registration::count();
            for ($i = 0; $i < $needed; $i++) {
                $contingent = Contingent::factory()->create();
                $participant = Participant::factory()->create(['contingent_id' => $contingent->id]);
                
                $payment = Payment::create([
                    'contingent_id' => $contingent->id,
                    'event_id' => $event->id,
                    'total_amount' => 100000,
                    'status' => \App\Enums\PaymentStatus::Verified->value,
                ]);

                Registration::create([
                    'participant_id' => $participant->id,
                    'sub_category_id' => $subCategory->id,
                    'payment_id' => $payment->id,
                    'status_berkas' => RegistrationStatus::Verified->value,
                ]);
            }
        }

        // 1. Pilih satu event yang punya registrasi
        $event = Event::whereHas('categories.subCategories.registrations')->first();

        if (!$event) {
            $this->command->warn('ResultSeeder: Tidak ada event dengan data registrasi.');
            return;
        }

        // 2. Ambil semua sub categories dari event itu
        $categories = $event->categories()->with('subCategories.registrations')->get();

        foreach ($categories as $category) {
            foreach ($category->subCategories as $sub) {
                // 3. Untuk setiap sub_category, assign medal
                $regs = $sub->registrations;

                if ($regs->count() > 0) {
                    if (isset($regs[0])) {
                        Result::updateOrCreate(
                            ['registration_id' => $regs[0]->id],
                            ['medal_type' => MedalType::Gold, 'rank_name' => 'Juara 1']
                        );
                    }
                    if (isset($regs[1])) {
                        Result::updateOrCreate(
                            ['registration_id' => $regs[1]->id],
                            ['medal_type' => MedalType::Silver, 'rank_name' => 'Juara 2']
                        );
                    }
                    if (isset($regs[2])) {
                        Result::updateOrCreate(
                            ['registration_id' => $regs[2]->id],
                            ['medal_type' => MedalType::Bronze, 'rank_name' => 'Juara 3']
                        );
                    }
                    if (isset($regs[3])) {
                        Result::updateOrCreate(
                            ['registration_id' => $regs[3]->id],
                            ['medal_type' => MedalType::Bronze, 'rank_name' => 'Juara 3 Bersama']
                        );
                    }
                }
            }
        }

        $this->command->info('ResultSeeder: Data medali (demo) berhasil di-generate.');
    }
}
