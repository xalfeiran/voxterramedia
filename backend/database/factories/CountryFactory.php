<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CountryFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->country();
        return [
            'code'         => strtoupper(Str::random(2)),
            'name'         => $name,
            'name_es'      => $name,
            'slug'         => Str::slug($name),
            'flag_emoji'   => '🏳',
            'latitude'     => $this->faker->latitude(),
            'longitude'    => $this->faker->longitude(),
            'default_zoom' => 4,
        ];
    }
}
