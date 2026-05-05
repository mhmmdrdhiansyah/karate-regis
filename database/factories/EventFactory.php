<?php

namespace Database\Factories;

use App\Enums\EventStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'poster' => null,
            'event_date' => now()->addMonth(),
            'registration_deadline' => now()->addWeeks(2),
            'coach_fee' => 100000,
            'event_fee' => 250000,
            'status' => EventStatus::RegistrationOpen,
        ];
    }
}
