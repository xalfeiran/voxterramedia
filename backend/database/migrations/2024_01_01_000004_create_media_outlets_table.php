<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('media_outlets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique()->index();
            $table->string('url');
            $table->enum('type', ['national', 'newspaper', 'digital', 'tv', 'radio', 'magazine'])
                  ->default('newspaper')->index();
            $table->enum('language', ['en', 'es', 'fr'])->default('en')->index();
            $table->text('description')->nullable();
            $table->string('logo_url')->nullable();
            $table->unsignedSmallInteger('founded_year')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->decimal('latitude', 10, 6)->nullable();
            $table->decimal('longitude', 10, 6)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_outlets');
    }
};
