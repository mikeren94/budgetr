<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\RecurringRule;

class TransactionsRecurringRulesTest extends TestCase
{
    use RefreshDatabase;
    public function test_a_transaction_can_belong_to_a_recurring_rule()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $rule = RecurringRule::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'recurring_rule_id' => $rule->id,
        ]);

        $this->assertEquals($rule->id, $transaction->recurring_rule_id);
        $this->assertTrue($transaction->recurringRule->is($rule));
    }

    public function test_a_transaction_may_exist_without_a_recurring_rule()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'recurring_rule_id' => null,
        ]);

        $this->assertNull($transaction->recurring_rule_id);
        $this->assertNull($transaction->recurringRule);
    }

    // TODO: Create delete recurring rules endpointS
    // public function test_deleting_a_recurring_rule_deletes_its_transactions()
    // {
    //     $user = User::factory()->create();

    //     $rule = RecurringRule::factory()->for($user)->create();

    //     $transactions = Transaction::factory()
    //         ->count(3)
    //         ->for($user)
    //         ->for($rule)
    //         ->create();

    //     $this->actingAs($user)
    //         ->deleteJson("/api/recurring-rules/{$rule->id}")
    //         ->assertStatus(204);

    //     foreach ($transactions as $transaction) {
    //         $this->assertDatabaseMissing('transactions', [
    //             'id' => $transaction->id,
    //         ]);
    //     }

    //     $this->assertDatabaseMissing('recurring_rules', [
    //         'id' => $rule->id,
    //     ]);
    // }

}
