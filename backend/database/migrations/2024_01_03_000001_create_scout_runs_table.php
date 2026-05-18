<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tracks every run of the VoxTerra AI Scout Bot.
 *
 * Each row represents one search job: a country was chosen, a set of
 * queries was fired, and the results were enriched + saved to media_outlets.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('scout_runs', function (Blueprint $table) {
            $table->id();

            // The country targeted in this run
            $table->foreignId('country_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('country_name', 100);   // denormalised for fast display

            // The first query string used (representative of the run)
            $table->string('query', 255);

            // Outcome counters
            $table->smallInteger('urls_found')->default(0);
            $table->smallInteger('urls_saved')->default(0);
            $table->smallInteger('urls_skipped')->default(0);

            // Timing
            $table->dateTime('started_at');
            $table->dateTime('finished_at')->nullable();

            // Free-text notes / error summary from the scout
            $table->text('notes')->nullable();

            $table->index('country_id');
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scout_runs');
    }
};
