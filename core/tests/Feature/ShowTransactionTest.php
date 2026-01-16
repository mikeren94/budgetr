<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Transaction;
use App\Models\User;

class ShowTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_fetch_their_transaction()
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->for($user)->create();

        $response = $this->actingAs($user)->getJson("/api/transactions/{$transaction->id}");

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'category_id' => $transaction->category_id,
                    'date' => $transaction->date->toDateString(),
                ]
            ]);
    }

    public function test_user_cannot_fetch_someone_elses_transaction()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $transaction = Transaction::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->getJson("/api/transactions/{$transaction->id}");

        $response->assertStatus(403);
    }

    public function test_guest_cannot_fetch_transaction()
    {
        $transaction = Transaction::factory()->create();

        $response = $this->getJson("/api/transactions/{$transaction->id}");

        $response->assertStatus(401);
    }

}
