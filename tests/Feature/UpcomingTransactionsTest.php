<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Category;

class UpcomingTransactionsTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_it_returns_upcoming_unpaid_transactions()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::factory()->expense()->create(['user_id' => $user->id]);

        // Future unpaid transaction
        $t1 = Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'date' => now()->addDays(3),
            'paid' => false,
        ]);

        // Past transaction (should NOT appear)
        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'date' => now()->subDays(2),
            'paid' => false,
        ]);

        // Paid future transaction (should NOT appear)
        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'date' => now()->addDays(5),
            'paid' => true,
        ]);

        $response = $this->getJson('/api/transactions/upcoming');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id' => $t1->id]);
    }

    public function test_it_defaults_to_end_of_month_range()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::factory()->expense()->create(['user_id' => $user->id]);

        // Inside this month
        $inside = Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'date' => now()->addDays(5),
            'paid' => false,
        ]);

        // Next month (should NOT appear)
        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'date' => now()->addMonth()->startOfMonth()->addDays(2),
            'paid' => false,
        ]);

        $response = $this->getJson('/api/transactions/upcoming');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id' => $inside->id]);
    }

    public function test_it_respects_range_parameter()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::factory()->expense()->create(['user_id' => $user->id]);

        $inside = Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'date' => now()->addDays(3),
            'paid' => false,
        ]);

        // Outside 7 days
        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'date' => now()->addDays(10),
            'paid' => false,
        ]);

        $response = $this->getJson('/api/transactions/upcoming?range=7');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id' => $inside->id]);
    }

    public function test_it_respects_end_date_parameter()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::factory()->expense()->create(['user_id' => $user->id]);

        $inside = Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'date' => '2026-01-10',
            'paid' => false,
        ]);

        // After end_date
        Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'date' => '2026-01-20',
            'paid' => false,
        ]);

        $response = $this->getJson('/api/transactions/upcoming?end_date=2026-01-15');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id' => $inside->id]);
    }

    public function test_it_sorts_transactions_by_date()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::factory()->expense()->create(['user_id' => $user->id]);

        $t1 = Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'date' => now()->addDays(5),
            'paid' => false,
        ]);

        $t2 = Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'date' => now()->addDays(2),
            'paid' => false,
        ]);

        $response = $this->getJson('/api/transactions/upcoming');

        $response->assertStatus(200);

        $this->assertEquals(
            [$t2->id, $t1->id],
            array_column($response->json('data'), 'id') // 👈 key change
        );
    }
}
