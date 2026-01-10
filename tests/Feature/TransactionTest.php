<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Transaction;

class TransactionTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_user_can_create_transaction()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->postJson('/api/transactions', [
            'amount' => 42.50,
            'date' => '2026-01-05',
            'category_id' => $category->id,
            'description' => 'Lunch at café',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('transactions', [
            'amount' => 42.50,
            'category_id' => $category->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_list_all_transactions()
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create();

        // Create 10 transactions for this user
        $transactions = Transaction::factory()
            ->count(10)
            ->for($user)
            ->for($category)
            ->create();

        // Create some transactions for another user (should NOT appear)
        Transaction::factory()
            ->count(5)
            ->create(); // different user + category

        $response = $this->actingAs($user)->getJson('/api/transactions');

        $response->assertStatus(200);

        // Should return exactly the 10 belonging to this user
        $response->assertJsonCount(10, 'data');

        // Assert at least one known transaction is included
        $response->assertJsonFragment([
            'id' => $transactions->first()->id,
        ]);
    }

    public function test_user_can_list_transactions_by_month()
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create();

        // Transactions inside January 2026
        $t1 = Transaction::factory()->for($user)->for($category)->create([
            'date' => '2026-01-05',
        ]);

        $t2 = Transaction::factory()->for($user)->for($category)->create([
            'date' => '2026-01-20',
        ]);

        // Transaction outside the month
        $t3 = Transaction::factory()->for($user)->for($category)->create([
            'date' => '2026-02-01',
        ]);

        $response = $this->actingAs($user)->getJson('/api/transactions?month=2026-01');

        $response->assertStatus(200);

        // Should contain the two January transactions
        $response->assertJsonCount(2, 'data');

        $response->assertJsonFragment(['id' => $t1->id]);
        $response->assertJsonFragment(['id' => $t2->id]);

        // Should NOT contain the February transaction
        $response->assertJsonMissing(['id' => $t3->id]);

    }

    public function test_it_creates_a_transaction_with_paid_flag()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $payload = [
            'amount' => 100,
            'category_id' => Category::factory()->expense()->create(['user_id' => $user->id])->id,
            'date' => now()->toDateString(),
            'description' => 'Test',
            'paid' => true,
        ];

        $response = $this->postJson('/api/transactions', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'paid' => true,
        ]);
    }

    public function test_it_updates_the_paid_flag()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'paid' => false,
        ]);

        $response = $this->putJson("/api/transactions/{$transaction->id}", [
            'paid' => true,
            'amount' => $transaction->amount,
            'category_id' => $transaction->category_id,
            'date' => $transaction->date,
            'description' => $transaction->description,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'paid' => true,
        ]);
    }

    public function test_paid_defaults_to_true_when_not_provided()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::factory()->expense()->create(['user_id' => $user->id]);

        $response = $this->postJson('/api/transactions', [
            'amount' => 50,
            'category_id' => $category->id,
            'date' => now()->toDateString(),
            'description' => 'Test',
            // no paid flag
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'paid' => true,
        ]);
    }
}
