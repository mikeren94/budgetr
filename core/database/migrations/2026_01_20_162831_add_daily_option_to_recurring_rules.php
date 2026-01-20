<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("
                ALTER TABLE recurring_rules
                MODIFY COLUMN frequency 
                ENUM('daily', 'weekly', 'monthly', 'yearly', 'custom')
                NOT NULL DEFAULT 'monthly'
            ");
        }

        if ($driver === 'sqlite') {
            Schema::table('recurring_rules', function (Blueprint $table) {
                $table->enum('frequency', ['daily', 'weekly', 'monthly', 'yearly', 'custom'])
                      ->default('monthly')
                      ->change();
            });
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("
                ALTER TABLE recurring_rules
                MODIFY COLUMN frequency 
                ENUM('weekly', 'monthly', 'yearly', 'custom')
                NOT NULL DEFAULT 'monthly'
            ");
        }

        if ($driver === 'sqlite') {
            Schema::table('recurring_rules', function (Blueprint $table) {
                $table->enum('frequency', ['weekly', 'monthly', 'yearly', 'custom'])
                      ->default('monthly')
                      ->change();
            });
        }
    }
};