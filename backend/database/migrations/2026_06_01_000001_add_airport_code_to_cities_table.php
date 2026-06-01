<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            // IATA airport / metro code, e.g. DFW for Dallas–Fort Worth.
            // Indexed (not unique): a metro code can span several cities
            // (DFW = Dallas + Fort Worth), and the news endpoint aggregates them.
            $table->string('airport_code', 3)->nullable()->index()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropIndex(['airport_code']);
            $table->dropColumn('airport_code');
        });
    }
};
