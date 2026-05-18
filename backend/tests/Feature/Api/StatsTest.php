<?php

use App\Models\City;
use App\Models\Country;
use App\Models\MediaOutlet;
use App\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns aggregate stats', function () {
    $country = Country::factory()->create(['code' => 'US', 'slug' => 'us']);
    $region  = Region::factory()->create(['country_id' => $country->id, 'slug' => 'ny']);
    $city    = City::factory()->create(['region_id' => $region->id, 'slug' => 'nyc']);

    MediaOutlet::factory()->count(3)->create(['city_id' => $city->id, 'is_active' => true, 'type' => 'newspaper', 'language' => 'en']);
    MediaOutlet::factory()->create(['city_id' => $city->id, 'is_active' => true, 'type' => 'digital', 'language' => 'es', 'slug' => 'digital-one']);

    $response = $this->getJson('/api/v1/stats')->assertOk();

    expect($response->json('data.total'))->toBe(4);
    expect($response->json('data.by_type'))->toHaveKey('newspaper');
    expect($response->json('data.by_language'))->toHaveKey('en');
    expect($response->json('data.by_country'))->toBeArray();
});
