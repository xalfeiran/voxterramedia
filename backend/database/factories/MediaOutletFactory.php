<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\MediaOutlet;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MediaOutletFactory extends Factory
{
    protected $model = MediaOutlet::class;

    public function definition(): array
    {
        $name = $this->faker->company();
        return [
            'city_id'      => City::factory(),
            'name'         => $name,
            'slug'         => Str::slug($name) . '-' . Str::random(4),
            'url'          => $this->faker->url(),
            'type'         => $this->faker->randomElement(MediaOutlet::TYPES),
            'language'     => $this->faker->randomElement(MediaOutlet::LANGUAGES),
            'description'  => $this->faker->optional()->sentence(),
            'logo_url'     => null,
            'founded_year' => $this->faker->optional()->numberBetween(1850, 2023),
            'is_active'    => true,
            'is_featured'  => false,
            'latitude'     => null,
            'longitude'    => null,
        ];
    }
}
