<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;
use App\Models\RecurringRule;

class DeleteTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_delete_their_own_transaction()
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->for($user)->create();

        $this->actingAs($user)
            ->deleteJson("/api/transactions/{$transaction->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->id,
        ]);
    }

    public function test_a_user_cannot_delete_someone_elses_transaction()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $transaction = Transaction::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->deleteJson("/api/transactions/{$transaction->id}")
            ->assertStatus(403);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
        ]);
    }

    public function test_deleting_a_transaction_does_not_delete_its_recurring_rule()
    {
        $user = User::factory()->create();

        $rule = RecurringRule::factory()->for($user)->create();

        $transaction = Transaction::factory()
            ->for($user)
            ->for($rule)
            ->create();

        $this->actingAs($user)
            ->deleteJson("/api/transactions/{$transaction->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->id,
        ]);

        $this->assertDatabaseHas('recurring_rules', [
            'id' => $rule->id,
        ]);
    }

    public function test_deleting_a_non_existent_transaction_returns_404()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/api/transactions/999999")
            ->assertStatus(404);
    }

}
