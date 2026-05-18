<?php

use App\Models\Country;
use App\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->ca = Country::factory()->create([
        'code' => 'CA', 'name' => 'Canada', 'name_es' => 'Canadá', 'slug' => 'canada', 'flag_emoji' => '🇨🇦',
    ]);
    $this->us = Country::factory()->create([
        'code' => 'US', 'name' => 'United States', 'name_es' => 'Estados Unidos', 'slug' => 'united-states', 'flag_emoji' => '🇺🇸',
    ]);
});

it('lists all countries', function () {
    $this->getJson('/api/v1/countries')
         ->assertOk()
         ->assertJsonStructure(['data' => [['code', 'name', 'name_es', 'slug', 'flag_emoji']]])
         ->assertJsonCount(2, 'data');
});

it('returns a country by code', function () {
    $this->getJson('/api/v1/countries/CA')
         ->assertOk()
         ->assertJsonPath('data.code', 'CA')
         ->assertJsonPath('data.name', 'Canada');
});

it('returns 404 for unknown country code', function () {
    $this->getJson('/api/v1/countries/ZZ')
         ->assertNotFound();
});

it('country detail includes regions', function () {
    Region::factory()->create(['country_id' => $this->ca->id, 'name' => 'Ontario', 'slug' => 'ontario']);

    $this->getJson('/api/v1/countries/CA')
         ->assertOk()
         ->assertJsonStructure(['data' => ['regions']]);
});
