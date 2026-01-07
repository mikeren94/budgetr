<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;

class UpdateTransactionTest extends TestCase
{
   use RefreshDatabase;

   public function test_user_can_update_their_transaction()
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->for($user)->create([
            'amount' => 10.00,
            'description' => 'Old description',
        ]);

        $payload = [
            'amount' => 25.50,
            'category_id' => $transaction->category_id,
            'date' => '2024-01-10',
            'description' => 'Updated description',
        ];

        $response = $this->actingAs($user)->putJson("/api/transactions/{$transaction->id}", $payload);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $transaction->id,
                    'amount' => 25.50,
                    'description' => 'Updated description',
                ]
            ]);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'amount' => 25.50,
            'description' => 'Updated description',
        ]);
    }

    public function test_user_cannot_update_someone_elses_transaction()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $transaction = Transaction::factory()->for($otherUser)->create();

        $payload = [
            'amount' => 99.99,
            'category_id' => $transaction->category_id,
            'date' => '2024-01-10',
            'description' => 'Hacked',
        ];

        $response = $this->actingAs($user)->putJson("/api/transactions/{$transaction->id}", $payload);

        $response->assertStatus(403);
    }

    public function test_guest_cannot_update_transaction()
    {
        $transaction = Transaction::factory()->create();

        $response = $this->putJson("/api/transactions/{$transaction->id}", []);

        $response->assertStatus(401);
    }

    public function test_update_requires_valid_data()
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->for($user)->create();

        $response = $this->actingAs($user)->putJson("/api/transactions/{$transaction->id}", [
            'amount' => null,
            'category_id' => null,
            'date' => null,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount', 'category_id', 'date']);
    }
}
