<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;

class MarkTransactionPaidTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_a_user_can_mark_their_transaction_as_paid()
    {
        $user = User::factory()->create();

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'paid' => false,
        ]);

        $response = $this->actingAs($user)
            ->put("/api/transactions/{$transaction->id}/mark-paid");

        $response->assertOk();

        $this->assertTrue($transaction->fresh()->paid);
    }

    public function test_a_user_cannot_mark_someone_elses_transaction_as_paid()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $transaction = Transaction::factory()->create([
            'user_id' => $otherUser->id,
            'paid' => false,
        ]);

        $response = $this->actingAs($user)
            ->put("/api/transactions/{$transaction->id}/mark-paid");

        $response->assertForbidden();

        $this->assertFalse($transaction->fresh()->paid);
    }

    public function test_it_returns_a_success_message()
    {
        $user = User::factory()->create();

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'paid' => false,
        ]);

        $response = $this->actingAs($user)
            ->put("/api/transactions/{$transaction->id}/mark-paid");

        $response->assertJson([
            'message' => 'Transaction marked as paid',
        ]);
    }

}
