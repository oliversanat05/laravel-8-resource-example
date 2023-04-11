<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\MetricHeadingSeeder;
use Database\Seeders\MetricAreaSeeder;
use Database\Seeders\TierSeeder;
// use Database\Seeders\WowIdeasSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            MetricHeadingSeeder::class,
            GapAnalysisHeadingSeeder::class,
            WowTrackerHeadingSeeder::class,
            MetricAreaSeeder::class,
            TierSeeder::class,
            // WowIdeasSeeder::class,
        ]);
    }
}
