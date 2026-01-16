<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\RecurringRule;
use App\Models\Transaction;
use App\Models\Category;

class ReccuringRuleNextOccuranceTest extends TestCase
{
    use RefreshDatabase;
   
    public function test_monthly_rule_calculates_next_occurrence()
    {
        Carbon::setTestNow('2024-01-15');

        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $rule = RecurringRule::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'frequency' => 'monthly',
            'interval' => 1,
            'start_date' => '2024-01-15',
            'next_occurrence' => '2024-01-15',
        ]);

        $next = $rule->calculateNextOccurrence();

        $this->assertEquals('2024-02-15', $next->toDateString());
    }

    public function test_yearly_rule_calculates_next_occurrence()
    {
        Carbon::setTestNow('2024-04-01');

        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $rule = RecurringRule::factory()->yearly()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'start_date' => '2024-04-01',
            'next_occurrence' => '2024-04-01',
        ]);

        $next = $rule->calculateNextOccurrence();

        $this->assertEquals('2025-04-01', $next->toDateString());
    }

    
    public function test_custom_month_rule_skips_unselected_months()
    {
        Carbon::setTestNow('2024-01-15');

        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        // Pay in Jan, Apr, Jul, Oct
        $rule = RecurringRule::factory()->custom([1, 4, 7, 10])->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'start_date' => '2024-01-15',
            'next_occurrence' => '2024-01-15',
        ]);

        $next = $rule->calculateNextOccurrence();

        // Should skip Feb + Mar → next is April
        $this->assertEquals('2024-04-15', $next->toDateString());
    }

    public function test_inactive_rules_do_not_generate_transactions()
    {
        Carbon::setTestNow('2024-01-15');

        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $rule = RecurringRule::factory()->inactive()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'next_occurrence' => '2024-01-15',
        ]);

        $transaction = $rule->generateTransaction();

        $this->assertNull($transaction);
    }

    public function atest_a_recurring_rule_can_generate_a_transaction()
    {
        Carbon::setTestNow('2024-01-15');

        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $rule = RecurringRule::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'amount' => 100,
            'next_occurrence' => '2024-01-15',
        ]);

        $transaction = $rule->generateTransaction();

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertEquals(100, $transaction->amount);
        $this->assertEquals('2024-01-15', $transaction->date->toDateString());
        $this->assertEquals($rule->id, $transaction->recurring_rule_id);
    }

    public function test_generating_a_transaction_advances_the_rule()
    {
        Carbon::setTestNow('2024-01-15');

        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $rule = RecurringRule::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'frequency' => 'monthly',
            'interval' => 1,
            'next_occurrence' => '2024-01-15',
        ]);
        
        $rule->generateTransaction();

        $this->assertEquals('2024-02-15', $rule->next_occurrence);
    }

}
