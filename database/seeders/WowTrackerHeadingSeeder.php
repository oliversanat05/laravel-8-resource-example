<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\wowModels\WowTrackerHeading;

class WowTrackerHeadingSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        WowTrackerHeading::insert(config('constants.wowTrackerHeadings'));
    }
}
