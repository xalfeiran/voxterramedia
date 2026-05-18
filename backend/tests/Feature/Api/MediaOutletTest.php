<?php

use App\Models\City;
use App\Models\Country;
use App\Models\MediaOutlet;
use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeGeo(): array
{
    $country = Country::factory()->create(['code' => 'US', 'slug' => 'us']);
    $region  = Region::factory()->create(['country_id' => $country->id, 'slug' => 'new-york']);
    $city    = City::factory()->create(['region_id' => $region->id, 'slug' => 'new-york-city']);
    return compact('country', 'region', 'city');
}

// ── Public index ────────────────────────────────────────────────────────────

it('lists media outlets with pagination', function () {
    ['city' => $city] = makeGeo();
    MediaOutlet::factory()->count(5)->create(['city_id' => $city->id, 'is_active' => true]);

    $this->getJson('/api/v1/media-outlets')
         ->assertOk()
         ->assertJsonStructure([
             'data' => [['id', 'name', 'slug', 'url', 'type', 'language', 'city', 'region', 'country']],
             'meta' => ['total', 'current_page', 'per_page'],
         ]);
});

it('filters outlets by country code', function () {
    $us = Country::factory()->create(['code' => 'US', 'slug' => 'us']);
    $mx = Country::factory()->create(['code' => 'MX', 'slug' => 'mx']);

    $usRegion  = Region::factory()->create(['country_id' => $us->id, 'slug' => 'us-region']);
    $mxRegion  = Region::factory()->create(['country_id' => $mx->id, 'slug' => 'mx-region']);
    $usCity    = City::factory()->create(['region_id' => $usRegion->id, 'slug' => 'us-city']);
    $mxCity    = City::factory()->create(['region_id' => $mxRegion->id, 'slug' => 'mx-city']);

    MediaOutlet::factory()->create(['city_id' => $usCity->id, 'is_active' => true, 'name' => 'US Outlet', 'slug' => 'us-outlet']);
    MediaOutlet::factory()->create(['city_id' => $mxCity->id, 'is_active' => true, 'name' => 'MX Outlet', 'slug' => 'mx-outlet']);

    $response = $this->getJson('/api/v1/media-outlets?country=US')->assertOk();
    expect($response->json('meta.total'))->toBe(1);
    expect($response->json('data.0.country.code'))->toBe('US');
});

it('filters outlets by type', function () {
    ['city' => $city] = makeGeo();
    MediaOutlet::factory()->create(['city_id' => $city->id, 'is_active' => true, 'type' => 'national', 'slug' => 'national-one']);
    MediaOutlet::factory()->create(['city_id' => $city->id, 'is_active' => true, 'type' => 'digital',  'slug' => 'digital-one']);

    $response = $this->getJson('/api/v1/media-outlets?type=national')->assertOk();
    expect($response->json('meta.total'))->toBe(1);
});

it('searches outlets by name', function () {
    ['city' => $city] = makeGeo();
    MediaOutlet::factory()->create(['city_id' => $city->id, 'is_active' => true, 'name' => 'The Guardian', 'slug' => 'the-guardian']);
    MediaOutlet::factory()->create(['city_id' => $city->id, 'is_active' => true, 'name' => 'Local Post',   'slug' => 'local-post']);

    $response = $this->getJson('/api/v1/media-outlets?search=guardian')->assertOk();
    expect($response->json('meta.total'))->toBe(1);
    expect($response->json('data.0.name'))->toBe('The Guardian');
});

it('returns single outlet by slug', function () {
    ['city' => $city] = makeGeo();
    MediaOutlet::factory()->create(['city_id' => $city->id, 'is_active' => true, 'name' => 'NY Times', 'slug' => 'ny-times']);

    $this->getJson('/api/v1/media-outlets/ny-times')
         ->assertOk()
         ->assertJsonPath('data.slug', 'ny-times');
});

it('returns 404 for unknown slug', function () {
    $this->getJson('/api/v1/media-outlets/does-not-exist')->assertNotFound();
});

// ── Map endpoint ────────────────────────────────────────────────────────────

it('returns lightweight map markers', function () {
    ['city' => $city] = makeGeo();
    MediaOutlet::factory()->count(3)->create(['city_id' => $city->id, 'is_active' => true]);

    $this->getJson('/api/v1/media-outlets/map')
         ->assertOk()
         ->assertJsonStructure(['data' => [['id', 'name', 'lat', 'lon', 'type', 'country']]]);
});

// ── Admin CRUD (auth required) ──────────────────────────────────────────────

it('rejects unauthenticated create', function () {
    ['city' => $city] = makeGeo();
    $this->postJson('/api/v1/admin/media-outlets', [
        'name' => 'Test', 'city_id' => $city->id, 'url' => 'https://x.com',
        'type' => 'digital', 'language' => 'en',
    ])->assertUnauthorized();
});

it('allows admin to create an outlet', function () {
    ['city' => $city] = makeGeo();
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin, 'sanctum')
         ->postJson('/api/v1/admin/media-outlets', [
             'name'     => 'Brand New Times',
             'city_id'  => $city->id,
             'url'      => 'https://brandnewtimes.com',
             'type'     => 'digital',
             'language' => 'en',
         ])
         ->assertCreated()
         ->assertJsonPath('data.name', 'Brand New Times');

    expect(MediaOutlet::where('slug', 'brand-new-times')->exists())->toBeTrue();
});

it('validates required fields on create', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin, 'sanctum')
         ->postJson('/api/v1/admin/media-outlets', [])
         ->assertUnprocessable()
         ->assertJsonValidationErrors(['name', 'city_id', 'url', 'type', 'language']);
});

it('allows admin to soft-delete an outlet', function () {
    ['city' => $city] = makeGeo();
    $admin  = User::factory()->create(['role' => 'admin']);
    $outlet = MediaOutlet::factory()->create(['city_id' => $city->id, 'is_active' => true]);

    $this->actingAs($admin, 'sanctum')
         ->deleteJson("/api/v1/admin/media-outlets/{$outlet->id}")
         ->assertOk();

    expect(MediaOutlet::find($outlet->id))->toBeNull();
    expect(MediaOutlet::withTrashed()->find($outlet->id))->not->toBeNull();
});

it('allows admin to toggle featured', function () {
    ['city' => $city] = makeGeo();
    $admin  = User::factory()->create(['role' => 'admin']);
    $outlet = MediaOutlet::factory()->create(['city_id' => $city->id, 'is_active' => true, 'is_featured' => false]);

    $this->actingAs($admin, 'sanctum')
         ->postJson("/api/v1/admin/media-outlets/{$outlet->id}/toggle-featured")
         ->assertOk()
         ->assertJsonPath('data.is_featured', true);

    $outlet->refresh();
    expect($outlet->is_featured)->toBeTrue();
});

it('rejects viewer from creating outlets', function () {
    ['city' => $city] = makeGeo();
    $viewer = User::factory()->create(['role' => 'viewer']);

    $this->actingAs($viewer, 'sanctum')
         ->postJson('/api/v1/admin/media-outlets', [
             'name' => 'Viewer Outlet', 'city_id' => $city->id,
             'url'  => 'https://x.com', 'type' => 'digital', 'language' => 'en',
         ])
         ->assertForbidden();
});
