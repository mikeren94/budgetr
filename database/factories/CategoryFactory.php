<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(), // creates a user if not provided
            'name' => $this->faker->unique()->word(),
            'type' => $this->faker->randomElement(['income', 'expense']),
            'color' => $this->faker->hexColor(),
            'description' => $this->faker->optional()->sentence(),
        ];
    }
}