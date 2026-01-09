<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'amount' => $this->faker->randomFloat(2, 1, 500), // 1.00–500.00
            'date' => $this->faker->date(),
            'description' => $this->faker->optional()->sentence(),
            'recurring_rule_id' => null,
            'coverage_end_date' => fake()->optional()->date(),
        ];
    }

    public function income()
    {
        return $this->state(fn () => [
            'category_id' => Category::factory()->state(['type' => 'income']),
        ]);
    }

}