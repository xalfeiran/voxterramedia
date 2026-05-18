<?php

namespace Database\Factories;

use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CityFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->city();
        return [
            'region_id' => Region::factory(),
            'name'      => $name,
            'slug'      => Str::slug($name) . '-' . Str::random(4),
            'latitude'  => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'population'=> $this->faker->optional()->numberBetween(50000, 5000000),
        ];
    }
}
