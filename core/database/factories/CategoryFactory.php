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
            'user_id' => User::factory(),
            'name' => $this->faker->unique()->word(),
            'type' => $this->faker->randomElement(['income', 'expense']),
            'color' => $this->faker->hexColor(),
            'description' => $this->faker->optional()->sentence(),
            'is_bill' => false,
        ];
    }

    public function income()
    {
        return $this->state(fn () => [
            'type' => 'income',
        ]);
    }

    public function expense()
    {
        return $this->state(fn () => [
            'type' => 'expense',
        ]);
    }

    public function bill()
    {
        return $this->state(fn () => [
            'is_bill' => true,
            'type' => 'expense', // bills are always expenses
        ]);
    }
}