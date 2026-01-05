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
            'name' => 'Groceries',
            'type' => 'expense',
            'description' => 'You know what groceries are',
            'color' => '#FF5733',
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Groceries',
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
            'name' => 'Groceries',
            'type' => 'expense',
            'color' => '#FF5733',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->postJson('/api/categories', [
            'name' => 'Groceries',
            'type' => 'expense',
            'color' => '#00FF00',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }
}