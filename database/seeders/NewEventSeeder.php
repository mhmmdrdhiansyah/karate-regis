<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;

class NewEventSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
            [
                'name' => 'Kejuaraan Karate Piala Presiden 2026',
                'poster' => 'events/posters/1.png',
                'bank_name' => 'BCA',
                'account_number' => '1234567890',
                'account_holder' => 'Pengurus Pusat Karate Indonesia',
                'event_date' => '2026-09-15',
                'registration_deadline' => '2026-08-20 23:59:59',
                'coach_fee' => 750000,
                'event_fee' => 350000,
                'status' => 'registration_open',
            ],
            [
                'name' => 'Jakarta Karate Open Championship 2026',
                'poster' => 'events/posters/2.png',
                'bank_name' => 'Mandiri',
                'account_number' => '0987654321',
                'account_holder' => 'Jakarta Karate Association',
                'event_date' => '2026-10-08',
                'registration_deadline' => '2026-09-10 23:59:59',
                'coach_fee' => 600000,
                'event_fee' => 300000,
                'status' => 'registration_open',
            ],
            [
                'name' => 'Kejuaraan Nasional Karate Yunior 2026',
                'poster' => 'events/posters/3.png',
                'bank_name' => 'BNI',
                'account_number' => '555566667777',
                'account_holder' => 'Pengurus Nasional Karate',
                'event_date' => '2026-11-12',
                'registration_deadline' => '2026-10-15 23:59:59',
                'coach_fee' => 500000,
                'event_fee' => 250000,
                'status' => 'registration_open',
            ],
            [
                'name' => 'Indonesia Karate Festival 2026',
                'poster' => 'events/posters/4.png',
                'bank_name' => 'BRI',
                'account_number' => '888899994444',
                'account_holder' => 'Festival Karate Indonesia',
                'event_date' => '2026-12-05',
                'registration_deadline' => '2026-11-10 23:59:59',
                'coach_fee' => 550000,
                'event_fee' => 275000,
                'status' => 'registration_open',
            ],
            [
                'name' => 'Piala KONI Karate Championship 2026',
                'poster' => 'events/posters/5.png',
                'bank_name' => 'BSI',
                'account_number' => '7000123456',
                'account_holder' => 'KONIN Karate Division',
                'event_date' => '2027-01-20',
                'registration_deadline' => '2026-12-25 23:59:59',
                'coach_fee' => 800000,
                'event_fee' => 400000,
                'status' => 'registration_open',
            ],
        ];

        foreach ($events as $eventData) {
            $event = Event::updateOrCreate(
                ['name' => $eventData['name']],
                $eventData
            );

            // Tiap event juga mendapat kategori & sub-kategori (sama struktur-nya
            // dengan EventSeeder) supaya semua event siap untuk pendaftaran.
            EventSeeder::seedCategoriesForEvent($event);
        }

        $this->command->info('NewEventSeeder: ' . count($events) . ' events created with posters, categories, and subcategories.');
    }
}
