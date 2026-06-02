<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Converts cities.airport_code from a UNIQUE index to a plain index.
 *
 * A metro/airport code can legitimately span several cities
 * (DFW = Dallas + Fort Worth, AUH covers Abu Dhabi metro), so uniqueness
 * is wrong here. Safe to run whether or not the unique index still exists.
 */
return new class extends Migration {
    public function up(): void
    {
        // Pre-check, then queue the command — Laravel runs the ALTER only after
        // the closure returns, so a try/catch *inside* the closure wouldn't catch
        // an execution-time error. Branch on existence instead.
        if ($this->indexExists('cities', 'cities_airport_code_unique')) {
            Schema::table('cities', fn (Blueprint $table) => $table->dropUnique('cities_airport_code_unique'));
        }

        if (!$this->indexExists('cities', 'cities_airport_code_index')) {
            Schema::table('cities', fn (Blueprint $table) => $table->index('airport_code'));
        }
    }

    public function down(): void
    {
        if ($this->indexExists('cities', 'cities_airport_code_index')) {
            Schema::table('cities', fn (Blueprint $table) => $table->dropIndex('cities_airport_code_index'));
        }

        if (!$this->indexExists('cities', 'cities_airport_code_unique')) {
            Schema::table('cities', fn (Blueprint $table) => $table->unique('airport_code'));
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $database = Schema::getConnection()->getDatabaseName();

        return (bool) Schema::getConnection()->selectOne(
            'SELECT 1 FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
            [$database, $table, $index]
        );
    }
};
