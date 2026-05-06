<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventCategory;
use App\Enums\EventCategoryType;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventCategoryFactory extends Factory
{
    protected $model = EventCategory::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'type' => EventCategoryType::Open,
            'class_name' => 'Usia Dini',
            'min_birth_date' => now()->subYears(10),
            'max_birth_date' => now()->subYears(8),
        ];
    }
}
