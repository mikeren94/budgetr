<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\RecurringRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class MonthlySummaryTest extends TestCase
{
    use RefreshDatabase;

    
    public function test_it_requires_authentication()
    {
        $this->getJson('/api/monthly-summary?month=2026-01')
            ->assertStatus(401);
    }

    public function test_it_returns_the_correct_structure()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('/api/monthly-summary?month=2026-01');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'month',
                'income',
                'expenses',
                'net',
                'transactions',
            ]);
    }

    public function test_it_includes_expenses_that_fall_inside_the_month()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $expenseCategory = Category::factory()->create([
            'user_id' => $user->id,
            'type' => 'expense',
        ]);

        $expense = Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $expenseCategory->id,
            'date' => '2026-01-10',
            'amount' => 50,
        ]);

        $response = $this->getJson('/api/monthly-summary?month=2026-01');

        $response->assertStatus(200)
            ->assertJsonFragment(['amount' => 50]);
    }

    public function test_it_excludes_expenses_outside_the_month()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $expenseCategory = Category::factory()->create([
            'user_id' => $user->id,
            'type' => 'expense',
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $expenseCategory->id,
            'date' => '2026-02-01',
            'amount' => 100,
        ]);

        $response = $this->getJson('/api/monthly-summary?month=2026-01');

        $response->assertStatus(200)
            ->assertJsonMissing(['amount' => 100]);
    }

    public function test_it_includes_income_if_coverage_window_overlaps_the_month()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $incomeCategory = Category::factory()->create([
            'user_id' => $user->id,
            'type' => 'income',
        ]);

        // Create a recurring rule that adds 1 month
        $rule = RecurringRule::factory()->create([
            'interval' => 1,
            'frequency' => 'monthly',
        ]);

        $income = Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $incomeCategory->id,
            'recurring_rule_id' => $rule->id,
            'date' => '2026-01-08',
            'amount' => 500,
        ]);

        // Now the observer will compute coverage_end_date = 2026-02-08

        $response = $this->getJson('/api/monthly-summary?month=2026-01');

        $response->assertStatus(200)
            ->assertJsonFragment(['amount' => 500]);
    }

    public function test_it_excludes_income_if_coverage_window_does_not_overlap()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $incomeCategory = Category::factory()->create([
            'user_id' => $user->id,
            'type' => 'income',
        ]);

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $incomeCategory->id,
            'date' => '2026-01-01',
            'coverage_end_date' => '2026-01-31',
            'amount' => 500,
        ]);

        $response = $this->getJson('/api/monthly-summary?month=2026-03');

        $response->assertStatus(200)
            ->assertJsonMissing(['amount' => 500]);
    }

    public function test_it_calculates_income_expenses_and_net_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $incomeCategory = Category::factory()->create([
            'user_id' => $user->id,
            'type' => 'income',
        ]);

        $expenseCategory = Category::factory()->create([
            'user_id' => $user->id,
            'type' => 'expense',
        ]);

        // Add a recurring rule so the observer sets coverage_end_date correctly
        $rule = RecurringRule::factory()->create([
            'interval' => 1,
            'frequency' => 'monthly', // or whatever your factory uses
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $incomeCategory->id,
            'recurring_rule_id' => $rule->id,
            'date' => '2026-01-05',
            'amount' => 1000,
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $expenseCategory->id,
            'date' => '2026-01-10',
            'amount' => 300,
        ]);

        $response = $this->getJson('/api/monthly-summary?month=2026-01');

        $response->assertStatus(200)
            ->assertJson([
                'income' => 1000,
                'expenses' => 300,
                'net' => 700,
            ]);
    }

    public function test_it_formats_the_month_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('/api/monthly-summary?month=2026-01');

        $response->assertStatus(200)
            ->assertJson(['month' => 'January 2026']);
    }

    public function test_it_returns_zero_totals_when_no_transactions_match()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('/api/monthly-summary?month=2026-01');

        $response->assertStatus(200)
            ->assertJson([
                'income' => 0,
                'expenses' => 0,
                'net' => 0,
                'transactions' => [],
            ]);
    }
}