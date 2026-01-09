<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use App\Models\Category;

class CategoryIsBillTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_adds_the_is_bill_column_to_categories_table()
    {
        $this->assertTrue(
            Schema::hasColumn('categories', 'is_bill'),
            'The categories table does not have the is_bill column.'
        );
    }

    public function test_new_categories_default_to_not_bill()
    {
        $category = Category::factory()->create();

        $this->assertFalse($category->is_bill);
    }

    public function test_it_can_mark_a_category_as_a_bill()
    {
        $category = Category::factory()->create([
            'is_bill' => true,
        ]);

        $this->assertTrue($category->is_bill);
    }

    public function test_it_can_filter_bill_categories()
    {
        $bill = Category::factory()->create(['is_bill' => true]);
        $normal = Category::factory()->create(['is_bill' => false]);

        $bills = Category::where('is_bill', true)->get();

        $this->assertTrue($bills->contains($bill));
        $this->assertFalse($bills->contains($normal));
    }

    public function test_is_bill_is_cast_to_boolean()
    {
        $category = Category::factory()->create(['is_bill' => 1]);

        $this->assertIsBool($category->is_bill);
        $this->assertTrue($category->is_bill);
    }
}
