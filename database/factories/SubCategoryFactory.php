<?php

namespace Database\Factories;

use App\Models\EventCategory;
use App\Models\SubCategory;
use App\Enums\SubCategoryGender;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubCategoryFactory extends Factory
{
    protected $model = SubCategory::class;

    public function definition(): array
    {
        return [
            'event_category_id' => EventCategory::factory(),
            'name' => 'Kata Perorangan',
            'category_type' => 'individu',
            'gender' => SubCategoryGender::Male,
            'price' => 150000,
            'min_participants' => 1,
            'max_participants' => 1,
            'max_teams' => 1,
        ];
    }

    public function beregu(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Kata Beregu',
            'category_type' => 'beregu',
            'min_participants' => 3,
            'max_participants' => 3,
            'max_teams' => 2,
        ]);
    }
}
