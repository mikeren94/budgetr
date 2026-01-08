<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Transaction;
use App\Models\RecurringRule;
use App\Models\Category;

class CoverageEndDateTest extends TestCase
{
    use RefreshDatabase;

    public function test_coverage_end_date_is_not_set_without_recurring_rule()
    {
        $transaction = Transaction::factory()
            ->income()
            ->create([
                'date' => '2026-01-26',
                'recurring_rule_id' => null,
            ]);

        $transaction->refresh();

        $this->assertNull($transaction->coverage_end_date);
    }


   public function test_coverage_end_date_is_set_for_monthly_recurring_income()
    {
        $category = Category::factory()->create([
            'type' => 'income',
        ]);

        $rule = RecurringRule::factory()->monthly()->create([
            'interval' => 1,
        ]);

        $transaction = Transaction::factory()->create([
            'category_id' => $category->id,
            'date' => '2026-01-26',
            'recurring_rule_id' => $rule->id,
        ]);

        $transaction->refresh();
        
        $this->assertEquals('2026-02-26', $transaction->coverage_end_date->toDateString());
    }

    public function test_coverage_end_date_is_set_for_biweekly_recurring_income()
    {
        $rule = RecurringRule::factory()->weekly()->create([
            'interval' => 2,
        ]);

        $transaction = Transaction::factory()->income()->create([
            'date' => '2026-02-01',
            'recurring_rule_id' => $rule->id,
        ]);

        $this->assertEquals('2026-02-15', $transaction->coverage_end_date->toDateString());
    }
}
