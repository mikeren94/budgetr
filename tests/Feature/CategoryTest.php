<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Category;
use App\Models\User;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_can_be_created()
    {
        $user = User::factory()->create();

        $category = Category::create([
            'name' => 'Test Groceries',
            'type' => 'expense',
            'description' => 'You know what groceries are',
            'color' => '#FF5733',
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Test Groceries',
            'user_id' => $user->id,
        ]);
    }

    public function test_category_requires_valid_hex_color()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/categories', [
            'name' => 'Bad Color',
            'type' => 'expense',
            'color' => 'red', // invalid
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('color');
    }

    public function test_valid_form_request_can_be_created()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/categories', [
            'name' => 'Api Groceries',
            'type' => 'expense',
            'color' => '#FF5733',
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'Category created successfully.',
            'data' => [
                'name' => 'Api Groceries',
                'type' => 'expense',
                'color' => '#FF5733',
            ],
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Api Groceries',
            'user_id' => $user->id,
        ]);
    }

    public function test_category_requires_name_type_and_color()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/categories', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'type', 'color']);
    }

    public function test_category_type_must_be_income_or_expense()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/categories', [
            'name' => 'Weird Type',
            'type' => 'investment', // invalid
            'color' => '#123456',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('type');
    }

    public function test_category_name_must_be_unique()
    {
        $user = User::factory()->create();

        Category::create([
            'name' => 'Test Groceries',
            'type' => 'expense',
            'color' => '#FF5733',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->postJson('/api/categories', [
            'name' => 'Test Groceries',
            'type' => 'expense',
            'color' => '#00FF00',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    public function test_user_can_fetch_their_categories()
    {
        $defaultCategories = json_decode(
            file_get_contents(resource_path('data/default_categories.json')),
            true
        );

        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $myCategories = Category::factory()->count(3)->for($user)->create();
        Category::factory()->count(2)->for($otherUser)->create();

        $response = $this->actingAs($user)->getJson('/api/categories');

        $response->assertOk();

        // total = defaults + manually created
        $expectedCount = count($defaultCategories) + 3;

        $response->assertJsonCount($expectedCount, 'data');

        $response->assertJsonFragment(['id' => $myCategories[0]->id]);
    }

    public function test_default_categories_are_created_when_user_is_created()
    {
        $defaultCategories = json_decode(
            file_get_contents(resource_path('data/default_categories.json')),
            true
        );

        $user = User::factory()->create();

        // Assert: the correct number of categories were created for THIS user
        $this->assertDatabaseCount('categories', count($defaultCategories));

        foreach ($defaultCategories as $category) {
            $this->assertDatabaseHas('categories', [
                'user_id' => $user->id,
                'name' => $category['name'],
                'type' => $category['type'],
                'color' => $category['color'],
            ]);
        }
    }
}