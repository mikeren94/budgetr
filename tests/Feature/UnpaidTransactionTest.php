<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Category;

class UnpaidTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_only_unpaid_transactions_for_the_user()
    {
        $user = User::factory()->create();

        $category = Category::factory()->create([
            'type' => 'expense',
        ]);

        $unpaid = Transaction::factory()->create([
            'user_id' => $user->id,
            'paid' => false,
            'date' => now()->subDay(),
            'category_id' => $category->id,
        ]);

        $paid = Transaction::factory()->create([
            'user_id' => $user->id,
            'paid' => true,
            'date' => now()->subDay(),
            'category_id' => $category->id,
        ]);

        $otherUsers = Transaction::factory()->create([
            'paid' => false,
            'date' => now()->subDay(),
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->get('/api/transactions/unpaid');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment([
            'id' => $unpaid->id,
        ]);
    }

    public function test_it_does_not_return_future_unpaid_transactions()
    {
        $user = User::factory()->create();

        $category = Category::factory()->create([
            'type' => 'expense',
        ]);

        // Due in the future — should NOT appear
        $future = Transaction::factory()->create([
            'user_id' => $user->id,
            'paid' => false,
            'date' => now()->addDays(3),
            'category_id' => $category->id,
        ]);

        // Due today — should appear
        $today = Transaction::factory()->create([
            'user_id' => $user->id,
            'paid' => false,
            'date' => now(),
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->get('/api/transactions/unpaid');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment([
            'id' => $today->id,
        ]);
    }

    public function test_results_are_ordered_by_date_ascending()
    {
        $user = User::factory()->create();

        $category = Category::factory()->create([
            'type' => 'expense',
        ]);

        $older = Transaction::factory()->create([
            'user_id' => $user->id,
            'paid' => false,
            'date' => now()->subDays(5),
            'category_id' => $category->id,
        ]);

        $newer = Transaction::factory()->create([
            'user_id' => $user->id,
            'paid' => false,
            'date' => now()->subDays(1),
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->get('/api/transactions/unpaid');

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            $older->id,
            $newer->id,
        ]);
    }

    public function test_it_requires_authentication()
    {
        $response = $this->get('/api/transactions/unpaid');

        $response->assertStatus(302); // redirect to login
    }
}