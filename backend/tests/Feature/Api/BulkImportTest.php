<?php

use App\Models\City;
use App\Models\Country;
use App\Models\MediaOutlet;
use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

beforeEach(function () {
    $country  = Country::factory()->create(['code' => 'US', 'slug' => 'us']);
    $region   = Region::factory()->create(['country_id' => $country->id, 'slug' => 'ny']);
    $this->city = City::factory()->create(['region_id' => $region->id, 'slug' => 'nyc']);
    $this->admin = User::factory()->create(['role' => 'admin']);
});

it('imports valid CSV rows', function () {
    $csv = "name,city_id,url,type,language\n" .
           "Test Outlet,{$this->city->id},https://test.com,digital,en\n" .
           "Second Outlet,{$this->city->id},https://second.com,newspaper,es";

    $file = UploadedFile::fake()->createWithContent('import.csv', $csv);

    $response = $this->actingAs($this->admin, 'sanctum')
         ->postJson('/api/v1/admin/media-outlets/bulk-import', ['file' => $file])
         ->assertOk();

    expect($response->json('data.imported'))->toBe(2);
    expect($response->json('data.errors'))->toBe(0);
    expect(MediaOutlet::count())->toBe(2);
});

it('reports per-row validation errors without stopping import', function () {
    $csv = "name,city_id,url,type,language\n" .
           "Valid Outlet,{$this->city->id},https://valid.com,digital,en\n" .
           "Bad Outlet,99999,not-a-url,unknown,xx";

    $file = UploadedFile::fake()->createWithContent('import.csv', $csv);

    $response = $this->actingAs($this->admin, 'sanctum')
         ->postJson('/api/v1/admin/media-outlets/bulk-import', ['file' => $file])
         ->assertOk();

    expect($response->json('data.imported'))->toBe(1);
    expect($response->json('data.errors'))->toBe(1);
});

it('rejects import by unauthenticated user', function () {
    $file = UploadedFile::fake()->createWithContent('import.csv', "name,city_id,url,type,language\n");
    $this->postJson('/api/v1/admin/media-outlets/bulk-import', ['file' => $file])
         ->assertUnauthorized();
});

it('rejects non-CSV file', function () {
    $file = UploadedFile::fake()->create('import.exe', 100);
    $this->actingAs($this->admin, 'sanctum')
         ->postJson('/api/v1/admin/media-outlets/bulk-import', ['file' => $file])
         ->assertUnprocessable();
});
