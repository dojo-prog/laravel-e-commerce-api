<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->warn('Demo seed data is restricted to local and testing environments.');

            return;
        }

        $this->call([
            AdminUserSeeder::class,
            CatalogSeeder::class,
            CouponSeeder::class,
            DemoCustomerSeeder::class,
            DemoOrderSeeder::class,
        ]);
    }
}
