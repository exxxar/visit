<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            DistrictSeeder::class,
            PlaceSeeder::class,
            SettingSeeder::class,
            DemoContentSeeder::class,
        ]);
    }
}
