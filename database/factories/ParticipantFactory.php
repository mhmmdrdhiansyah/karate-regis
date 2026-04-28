<?php

namespace Database\Factories;

use App\Enums\ParticipantGender;
use App\Enums\ParticipantType;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParticipantFactory extends Factory
{
    protected $model = Participant::class;

    public function definition(): array
    {
        return [
            'contingent_id' => 1,
            'type' => ParticipantType::Athlete,
            'nik' => fake()->unique()->numerify('################'),
            'name' => fake()->name(),
            'birth_date' => fake()->date(),
            'gender' => fake()->randomElement([ParticipantGender::Male, ParticipantGender::Female]),
            'provinsi' => fake()->state(),
            'institusi' => fake()->company(),
            'photo' => null,
            'document' => null,
            'is_verified' => false,
        ];
    }
}
