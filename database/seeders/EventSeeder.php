<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
            // Registration Open
            [
                'name' => 'Sabuk Hitam Championship 2026',
                'bank_name' => 'BNI',
                'account_number' => '555566667777',
                'account_holder' => 'Sabuk Hitam Association',
                'event_date' => '2026-11-25',
                'registration_deadline' => '2026-10-30 23:59:59',
                'coach_fee' => 600000,
                'event_fee' => 300000,
                'status' => 'registration_open',
                'poster' => null,
            ],
            [
                'name' => 'Kejuaraan Karate Piala Presiden 2026',
                'bank_name' => 'BCA',
                'account_number' => '1234567890',
                'account_holder' => 'Pengurus Pusat Karate Indonesia',
                'event_date' => '2026-09-15',
                'registration_deadline' => '2026-08-20 23:59:59',
                'coach_fee' => 750000,
                'event_fee' => 350000,
                'status' => 'registration_open',
                'poster' => 'events/posters/1.png',
            ],
            [
                'name' => 'Jakarta Karate Open Championship 2026',
                'bank_name' => 'Mandiri',
                'account_number' => '0987654321',
                'account_holder' => 'Jakarta Karate Association',
                'event_date' => '2026-10-08',
                'registration_deadline' => '2026-09-10 23:59:59',
                'coach_fee' => 600000,
                'event_fee' => 300000,
                'status' => 'registration_open',
                'poster' => 'events/posters/2.png',
            ],
            [
                'name' => 'Bandung Karate Open 2026',
                'bank_name' => 'BJB',
                'account_number' => '111122223333',
                'account_holder' => 'Bandung Karate League',
                'event_date' => '2026-11-05',
                'registration_deadline' => '2026-10-10 23:59:59',
                'coach_fee' => 500000,
                'event_fee' => 250000,
                'status' => 'registration_open',
                'poster' => 'events/posters/6.png',
            ],
            [
                'name' => 'Surabaya Karate Cup 2026',
                'bank_name' => 'Jatim',
                'account_number' => '444455556666',
                'account_holder' => 'Surabaya Karate Association',
                'event_date' => '2026-12-15',
                'registration_deadline' => '2026-11-20 23:59:59',
                'coach_fee' => 600000,
                'event_fee' => 300000,
                'status' => 'registration_open',
                'poster' => 'events/posters/7.png',
            ],

            // Registration Closed
            [
                'name' => 'Sumatera Open Championship 2026',
                'bank_name' => 'Mandiri',
                'account_number' => '222233334444',
                'account_holder' => 'Sumatera Karate League',
                'event_date' => '2026-10-25',
                'registration_deadline' => '2026-10-01 23:59:59',
                'coach_fee' => 500000,
                'event_fee' => 250000,
                'status' => 'registration_closed',
                'poster' => null,
            ],
            [
                'name' => 'Sulawesi Karate Cup 2026',
                'bank_name' => 'BNI',
                'account_number' => '333344445555',
                'account_holder' => 'Sulawesi Karate League',
                'event_date' => '2026-11-02',
                'registration_deadline' => '2026-10-10 23:59:59',
                'coach_fee' => 450000,
                'event_fee' => 220000,
                'status' => 'registration_closed',
                'poster' => null,
            ],

            // Ongoing
            [
                'name' => 'Karate Junior Cup 2026',
                'bank_name' => 'BRI',
                'account_number' => '888899994444',
                'account_holder' => 'Junior Karate Cup',
                'event_date' => '2026-08-05',
                'registration_deadline' => '2026-07-10 23:59:59',
                'coach_fee' => 400000,
                'event_fee' => 200000,
                'status' => 'ongoing',
                'poster' => null,
            ],
            [
                'name' => 'National Dojo Battle 2026',
                'bank_name' => 'BCA',
                'account_number' => '999900001111',
                'account_holder' => 'Dojo Battle League',
                'event_date' => '2026-08-06',
                'registration_deadline' => '2026-07-20 23:59:59',
                'coach_fee' => 500000,
                'event_fee' => 250000,
                'status' => 'ongoing',
                'poster' => null,
            ],

            // Completed
            [
                'name' => 'Piala Karate Indonesia 2026',
                'bank_name' => 'BCA',
                'account_number' => '1234567890',
                'account_holder' => 'Pengurus Pusat Karate Indonesia',
                'event_date' => '2026-05-01',
                'registration_deadline' => '2026-04-15 23:59:59',
                'coach_fee' => 500000,
                'event_fee' => 250000,
                'status' => 'completed',
                'poster' => null,
            ],
            [
                'name' => 'Kejuaraan Nasional Karate 2026',
                'bank_name' => 'Mandiri',
                'account_number' => '0987654321',
                'account_holder' => 'Pengurus Nasional Karate',
                'event_date' => '2026-06-12',
                'registration_deadline' => '2026-05-15 23:59:59',
                'coach_fee' => 750000,
                'event_fee' => 350000,
                'status' => 'completed',
                'poster' => null,
            ],
            [
                'name' => 'Regional Dojo League 2026',
                'bank_name' => 'BCA',
                'account_number' => '444455556666',
                'account_holder' => 'Regional Dojo Committee',
                'event_date' => '2026-07-20',
                'registration_deadline' => '2026-07-01 23:59:59',
                'coach_fee' => 300000,
                'event_fee' => 150000,
                'status' => 'completed',
                'poster' => null,
            ],
            [
                'name' => 'Kejuaraan Nasional Karate Yunior 2026',
                'bank_name' => 'BNI',
                'account_number' => '555566667777',
                'account_holder' => 'Pengurus Nasional Karate',
                'event_date' => '2026-04-12',
                'registration_deadline' => '2026-03-15 23:59:59',
                'coach_fee' => 500000,
                'event_fee' => 250000,
                'status' => 'completed',
                'poster' => 'events/posters/3.png',
            ],
            [
                'name' => 'Indonesia Karate Festival 2026',
                'bank_name' => 'BRI',
                'account_number' => '888899994444',
                'account_holder' => 'Festival Karate Indonesia',
                'event_date' => '2026-03-05',
                'registration_deadline' => '2026-02-10 23:59:59',
                'coach_fee' => 550000,
                'event_fee' => 275000,
                'status' => 'completed',
                'poster' => 'events/posters/4.png',
            ],
            [
                'name' => 'Piala KONI Karate Championship 2026',
                'bank_name' => 'BSI',
                'account_number' => '7000123456',
                'account_holder' => 'KONIN Karate Division',
                'event_date' => '2026-02-20',
                'registration_deadline' => '2026-01-25 23:59:59',
                'coach_fee' => 800000,
                'event_fee' => 400000,
                'status' => 'completed',
                'poster' => 'events/posters/5.png',
            ],
            [
                'name' => 'Veteran Karate League 2026',
                'bank_name' => 'Mandiri',
                'account_number' => '999988887777',
                'account_holder' => 'Veteran Karate Indonesia',
                'event_date' => '2026-01-10',
                'registration_deadline' => '2025-12-15 23:59:59',
                'coach_fee' => 400000,
                'event_fee' => 200000,
                'status' => 'completed',
                'poster' => 'events/posters/8.png',
            ],
        ];

        foreach ($events as $eventData) {
            $event = Event::updateOrCreate(
                ['name' => $eventData['name']],
                $eventData
            );

            $this->seedCategoriesForEvent($event);
        }

        $this->seedPanitiaAssignments();

        $this->command->info('EventSeeder: ' . count($events) . ' events created, each with Categories and SubCategories.');
    }

    /**
     * Demo penugasan panitia (Tahap 10 event-scoping): panitia demo pertama
     * ditugaskan ke event demo pertama. Idempoten via syncWithoutDetaching.
     */
    private function seedPanitiaAssignments(): void
    {
        $event = Event::first();
        $panitia = User::role('panitia')->first();

        if ($event && $panitia) {
            $event->panitia()->syncWithoutDetaching([$panitia->id]);
        }
    }

    private function seedCategoriesForEvent(Event $event): void
    {
        $classes = [
            'JUNIOR' => ['min' => '2009-01-01', 'max' => '2010-12-31'],
            'U21' => ['min' => '2006-01-01', 'max' => '2008-12-31'],
            'DEWASA' => ['min' => '1996-01-01', 'max' => '2005-12-31'],
        ];

        $types = ['Open', 'Festival'];

        $subCategories = [
            ['name' => 'KATA Individu Putra', 'category_type' => 'individu', 'discipline' => 'kata', 'gender' => 'M', 'price' => 250000, 'min' => 1, 'max' => 1, 'max_teams' => 1],
            ['name' => 'KATA Individu Putri', 'category_type' => 'individu', 'discipline' => 'kata', 'gender' => 'F', 'price' => 250000, 'min' => 1, 'max' => 1, 'max_teams' => 1],
            ['name' => 'KATA Beregu Putra', 'category_type' => 'beregu', 'discipline' => 'kata', 'gender' => 'M', 'price' => 500000, 'min' => 3, 'max' => 3, 'max_teams' => 2],
            ['name' => 'KATA Beregu Putri', 'category_type' => 'beregu', 'discipline' => 'kata', 'gender' => 'F', 'price' => 500000, 'min' => 3, 'max' => 3, 'max_teams' => 2],
            ['name' => 'KUMITE Individu Putra', 'category_type' => 'individu', 'discipline' => 'kumite', 'gender' => 'M', 'price' => 250000, 'min' => 1, 'max' => 1, 'max_teams' => 1],
            ['name' => 'KUMITE Individu Putri', 'category_type' => 'individu', 'discipline' => 'kumite', 'gender' => 'F', 'price' => 250000, 'min' => 1, 'max' => 1, 'max_teams' => 1],
        ];

        foreach ($types as $type) {
            foreach ($classes as $className => $range) {
                $category = EventCategory::updateOrCreate(
                    [
                        'event_id' => $event->id,
                        'type' => $type,
                        'class_name' => $className,
                    ],
                    [
                        'min_birth_date' => $range['min'],
                        'max_birth_date' => $range['max'],
                    ]
                );

                foreach ($subCategories as $subCategory) {
                    SubCategory::updateOrCreate(
                        [
                            'event_category_id' => $category->id,
                            'name' => $subCategory['name'],
                        ],
                        [
                            'category_type' => $subCategory['category_type'],
                            'discipline' => $subCategory['discipline'],
                            'gender' => $subCategory['gender'],
                            'price' => $subCategory['price'],
                            'min_participants' => $subCategory['min'],
                            'max_participants' => $subCategory['max'],
                            'max_teams' => $subCategory['max_teams'] ?? 1,
                        ]
                    );
                }
            }
        }
    }
}
