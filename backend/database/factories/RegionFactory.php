<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RegionFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->state();
        return [
            'country_id' => Country::factory(),
            'code'       => strtoupper(Str::random(2)),
            'name'       => $name,
            'name_es'    => $name,
            'slug'       => Str::slug($name) . '-' . Str::random(4),
            'latitude'   => $this->faker->latitude(),
            'longitude'  => $this->faker->longitude(),
        ];
    }
}
