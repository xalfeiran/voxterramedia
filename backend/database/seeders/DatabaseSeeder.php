<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::create([
            'name'     => 'Admin',
            'email'    => 'admin@voxterra.media',
            'password' => bcrypt('secret'),
            'role'     => 'admin',
        ]);

        $this->call([
            // All ~195 countries first — so every ISO code is resolvable
            AllCountriesSeeder::class,

            // North America (CA / US / MX) — regions & cities
            GeographySeeder::class,
            MediaOutletSeeder::class,

            // Worldwide regions & cities — run after NA geography
            WorldGeographySeeder::class,
            WorldMediaOutletSeeder::class,
        ]);
    }
}
