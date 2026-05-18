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
            // North America (CA / US / MX)
            GeographySeeder::class,
            MediaOutletSeeder::class,

            // Worldwide — run after NA geography so city slugs are all loaded
            WorldGeographySeeder::class,
            WorldMediaOutletSeeder::class,
        ]);
    }
}
