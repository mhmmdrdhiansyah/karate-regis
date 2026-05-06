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
        $event = Event::updateOrCreate(
            ['name' => 'Piala Karate Indonesia 2026'],
            [
                'event_date' => '2026-08-01',
                'registration_deadline' => '2026-07-15 23:59:59',
                'coach_fee' => 500000,
                'event_fee' => 250000,
                'status' => 'registration_open',
            ]
        );

        $classes = [
            // Usia di tahun 2026
            'JUNIOR' => ['min' => '2009-01-01', 'max' => '2010-12-31'],
            'U21' => ['min' => '2006-01-01', 'max' => '2008-12-31'],
            'DEWASA' => ['min' => '1996-01-01', 'max' => '2005-12-31'],
        ];

        $types = ['Open', 'Festival'];

        $subCategories = [
            ['name' => 'KATA Individu Putra', 'gender' => 'M', 'price' => 250000, 'min' => 1, 'max' => 1],
            ['name' => 'KATA Individu Putri', 'gender' => 'F', 'price' => 250000, 'min' => 1, 'max' => 1],
            ['name' => 'KATA Beregu Putra', 'gender' => 'M', 'price' => 500000, 'min' => 3, 'max' => 3],
            ['name' => 'KATA Beregu Putri', 'gender' => 'F', 'price' => 500000, 'min' => 3, 'max' => 3],
            ['name' => 'KUMITE Individu Putra', 'gender' => 'M', 'price' => 250000, 'min' => 1, 'max' => 1],
            ['name' => 'KUMITE Individu Putri', 'gender' => 'F', 'price' => 250000, 'min' => 1, 'max' => 1],
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
                            'gender' => $subCategory['gender'],
                            'price' => $subCategory['price'],
                            'min_participants' => $subCategory['min'],
                            'max_participants' => $subCategory['max'],
                        ]
                    );
                }
            }
        }

        $this->command->info('EventSeeder: Event, Categories (Open/Festival), and SubCategories created.');
    }
}
