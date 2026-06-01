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
        Schema::table('cities', function (Blueprint $table) {
            // Drop the unique index if it's present (named by Laravel convention).
            try {
                $table->dropUnique('cities_airport_code_unique');
            } catch (\Throwable $e) {
                // already dropped / never existed — ignore
            }
        });

        Schema::table('cities', function (Blueprint $table) {
            if (!$this->indexExists('cities', 'cities_airport_code_index')) {
                $table->index('airport_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            try {
                $table->dropIndex('cities_airport_code_index');
            } catch (\Throwable $e) {
                // ignore
            }
            $table->unique('airport_code');
        });
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
