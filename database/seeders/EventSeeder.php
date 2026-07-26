<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\SubCategory;
use App\Enums\SubCategoryGender;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
            [
                'name' => 'Piala Karate Indonesia 2026',
                'bank_name' => 'BCA',
                'account_number' => '1234567890',
                'account_holder' => 'Pengurus Pusat Karate Indonesia',
                'event_date' => '2026-08-01',
                'registration_deadline' => '2026-07-15 23:59:59',
                'coach_fee' => 500000,
                'event_fee' => 250000,
                'status' => 'registration_open',
            ],
            [
                'name' => 'Kejuaraan Nasional Karate 2026',
                'bank_name' => 'Mandiri',
                'account_number' => '0987654321',
                'account_holder' => 'Pengurus Nasional Karate',
                'event_date' => '2026-10-12',
                'registration_deadline' => '2026-09-15 23:59:59',
                'coach_fee' => 750000,
                'event_fee' => 350000,
                'status' => 'registration_open',
            ],
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
            ],
            [
                'name' => 'Karate Junior Cup 2026',
                'bank_name' => 'BRI',
                'account_number' => '888899994444',
                'account_holder' => 'Junior Karate Cup',
                'event_date' => '2026-12-05',
                'registration_deadline' => '2026-11-10 23:59:59',
                'coach_fee' => 400000,
                'event_fee' => 200000,
                'status' => 'registration_open',
            ],
        ];

        foreach ($events as $eventData) {
            $event = Event::updateOrCreate(
                ['name' => $eventData['name']],
                $eventData
            );

            self::seedCategoriesForEvent($event);
        }

        $this->command->info('EventSeeder: ' . count($events) . ' events created, each with Categories (Open/Festival) and SubCategories.');
    }

    /**
     * Buat kategori (Open/Festival × kelas usia) beserta sub-kategori
     * (KATA & KUMITE, individu & beregu) untuk sebuah event. Idempoten.
     * Dipakai juga oleh NewEventSeeder agar tiap event punya kategori.
     */
    public static function seedCategoriesForEvent(Event $event): void
    {
        $classes = [
            // Usia di tahun 2026
            'JUNIOR' => ['min' => '2009-01-01', 'max' => '2010-12-31'],
            'U21' => ['min' => '2006-01-01', 'max' => '2008-12-31'],
            'DEWASA' => ['min' => '1996-01-01', 'max' => '2005-12-31'],
        ];

        $types = ['Open', 'Festival'];

        $subCategories = [
            ['name' => 'KATA Individu Putra', 'category_type' => 'individu', 'gender' => 'M', 'price' => 250000, 'min' => 1, 'max' => 1, 'max_teams' => 1],
            ['name' => 'KATA Individu Putri', 'category_type' => 'individu', 'gender' => 'F', 'price' => 250000, 'min' => 1, 'max' => 1, 'max_teams' => 1],
            ['name' => 'KATA Beregu Putra', 'category_type' => 'beregu', 'gender' => 'M', 'price' => 500000, 'min' => 3, 'max' => 3, 'max_teams' => 2],
            ['name' => 'KATA Beregu Putri', 'category_type' => 'beregu', 'gender' => 'F', 'price' => 500000, 'min' => 3, 'max' => 3, 'max_teams' => 2],
            ['name' => 'KUMITE Individu Putra', 'category_type' => 'individu', 'gender' => 'M', 'price' => 250000, 'min' => 1, 'max' => 1, 'max_teams' => 1],
            ['name' => 'KUMITE Individu Putri', 'category_type' => 'individu', 'gender' => 'F', 'price' => 250000, 'min' => 1, 'max' => 1, 'max_teams' => 1],
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
