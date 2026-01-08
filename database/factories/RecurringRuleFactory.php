<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\RecurringRule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecurringRuleFactory extends Factory
{
    protected $model = RecurringRule::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),

            'amount' => $this->faker->randomFloat(2, 5, 5000),

            'start_date' => $this->faker->date(),

            'frequency' => 'monthly', // sensible default
            'interval' => 1,

            'months' => null, // only used for custom schedules

            'next_occurrence' => now()->addMonth()->toDateString(),

            'active' => true,
        ];
    }

    /**
     * Monthly recurrence
     */
    public function monthly(int $interval = 1): static
    {
        return $this->state(fn () => [
            'frequency' => 'monthly',
            'interval' => $interval,
            'months' => null,
        ]);
    }

    /**
     * Yearly recurrence
     */
    public function yearly(int $interval = 1): static
    {
        return $this->state(fn () => [
            'frequency' => 'yearly',
            'interval' => $interval,
            'months' => null,
        ]);
    }

    /**
     * Custom month pattern (e.g. council tax)
     */
    public function custom(array $months): static
    {
        return $this->state(fn () => [
            'frequency' => 'custom',
            'interval' => 1,
            'months' => $months,
        ]);
    }

    /**
     * Inactive rule
     */
    public function inactive(): static
    {
        return $this->state(fn () => [
            'active' => false,
        ]);
    }

    public function weekly(int $interval = 1)
    {
        return $this->state(fn () => [
            'frequency' => 'weekly',
            'interval' => $interval,
            'months' => null,
        ]);
    }
}