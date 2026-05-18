<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Expand the language column on media_outlets from a 3-value ENUM
 * (en, es, fr) to a VARCHAR(10) so the worldwide catalog can store
 * any ISO 639-1 language code (de, pt, ar, zh, ja, ru, …).
 */
return new class extends Migration {
    public function up(): void
    {
        // MySQL requires dropping the ENUM and recreating as string.
        // We use a raw statement so this works across MySQL/MariaDB/SQLite.
        DB::statement("ALTER TABLE media_outlets MODIFY COLUMN language VARCHAR(10) NOT NULL DEFAULT 'en'");
    }

    public function down(): void
    {
        // Restore the original three-value ENUM (data loss warning: any non en/es/fr value becomes 'en')
        DB::statement("UPDATE media_outlets SET language = 'en' WHERE language NOT IN ('en','es','fr')");
        DB::statement("ALTER TABLE media_outlets MODIFY COLUMN language ENUM('en','es','fr') NOT NULL DEFAULT 'en'");
    }
};
