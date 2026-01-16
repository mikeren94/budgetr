<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Schema;

class RecurringRulesMigrationTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_recurring_rules_table_has_expected_columns()
    {
        $this->assertTrue(Schema::hasTable('recurring_rules'));

        $expectedColumns = [
            'id',
            'user_id',
            'category_id',
            'amount',
            'start_date',
            'frequency',
            'interval',
            'months',
            'next_occurrence',
            'active',
            'created_at',
            'updated_at',
        ];

        foreach ($expectedColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('recurring_rules', $column),
                "Failed asserting that column '{$column}' exists in recurring_rules table."
            );
        }
    }
}
