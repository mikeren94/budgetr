<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\RecurringRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class MonthlyTransactionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Category $incomeCategory;
    protected Category $expenseCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->incomeCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'income',
        ]);

        $this->expenseCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense',
        ]);
    }

    public function test_it_returns_real_transactions_for_the_month()
    {
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'date' => '2026-02-10',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/transactions/monthly?month=2026-02-01');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_it_includes_income_that_covers_the_month()
    {
        $rule = RecurringRule::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->incomeCategory->id,
            'frequency' => 'monthly',
            'interval' => 1,
        ]);

        $t = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->incomeCategory->id,
            'date' => '2026-01-31',
            'recurring_rule_id' => $rule->id,
            'coverage_end_date' => '2026-02-28',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/transactions/monthly?month=2026-02-01');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_it_generates_virtual_transactions_when_no_real_coverage_exists()
    {
        RecurringRule::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->incomeCategory->id,
            'amount' => 1000,
            'start_date' => '2026-01-01',
            'frequency' => 'monthly',
            'interval' => 1,
            'active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/transactions/monthly?month=2026-02-01');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_it_does_not_duplicate_virtual_transactions_if_real_coverage_exists()
    {
        $rule = RecurringRule::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->incomeCategory->id,
            'frequency' => 'monthly',
            'interval' => 1,
            'active' => true,
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->incomeCategory->id,
            'date' => '2026-01-31',
            'recurring_rule_id' => $rule->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/transactions/monthly?month=2026-02-01');

        // Should only include the real one
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_it_sorts_transactions_by_date_desc()
    {
        // IMPORTANT: both must have categories or they will be filtered out
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'date' => '2026-02-01',
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'date' => '2026-02-15',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/transactions/monthly?month=2026-02-01');

        $response->assertStatus(200);

        $dates = array_column($response->json('data'), 'formatted_date');

        $this->assertEquals(['2026-02-15', '2026-02-01'], $dates);
    }
}
