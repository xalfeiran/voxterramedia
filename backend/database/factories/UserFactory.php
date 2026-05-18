<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name'              => $this->faker->name(),
            'email'             => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => bcrypt('password'),
            'role'              => 'viewer',
            'remember_token'    => null,
        ];
    }

    public function admin(): static
    {
        return $this->state(['role' => 'admin']);
    }

    public function editor(): static
    {
        return $this->state(['role' => 'editor']);
    }
}
