<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('issues a sanctum token on valid login', function () {
    User::factory()->create(['email' => 'admin@test.com', 'password' => bcrypt('secret'), 'role' => 'admin']);

    $this->postJson('/api/v1/auth/login', ['email' => 'admin@test.com', 'password' => 'secret'])
         ->assertOk()
         ->assertJsonStructure(['data' => ['user', 'token']]);
});

it('rejects login with wrong password', function () {
    User::factory()->create(['email' => 'admin@test.com', 'password' => bcrypt('secret'), 'role' => 'admin']);

    $this->postJson('/api/v1/auth/login', ['email' => 'admin@test.com', 'password' => 'wrongpass'])
         ->assertUnprocessable()
         ->assertJsonValidationErrors(['email']);
});

it('validates login fields', function () {
    $this->postJson('/api/v1/auth/login', [])
         ->assertUnprocessable()
         ->assertJsonValidationErrors(['email', 'password']);
});

it('returns the authenticated user on /me', function () {
    $user = User::factory()->create(['role' => 'editor']);

    $this->actingAs($user, 'sanctum')
         ->getJson('/api/v1/auth/me')
         ->assertOk()
         ->assertJsonPath('data.email', $user->email);
});

it('logs out and revokes the token', function () {
    $user = User::factory()->create(['role' => 'admin']);

    $this->actingAs($user, 'sanctum')
         ->postJson('/api/v1/auth/logout')
         ->assertOk();
});

it('denies /me to unauthenticated requests', function () {
    $this->getJson('/api/v1/auth/me')->assertUnauthorized();
});
