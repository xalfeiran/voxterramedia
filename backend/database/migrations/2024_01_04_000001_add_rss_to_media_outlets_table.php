<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_outlets', function (Blueprint $table) {
            $table->string('rss_url', 512)->nullable()->after('url');
            $table->boolean('has_rss')->default(false)->after('rss_url')->index();
        });
    }

    public function down(): void
    {
        Schema::table('media_outlets', function (Blueprint $table) {
            $table->dropIndex(['has_rss']);
            $table->dropColumn(['rss_url', 'has_rss']);
        });
    }
};
