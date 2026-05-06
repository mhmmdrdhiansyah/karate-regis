<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContingentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->company(),
            'official_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
        ];
    }
}
