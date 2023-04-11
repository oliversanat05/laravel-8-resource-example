<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\wowModels\MetricHeading;

class MetricHeadingSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        MetricHeading::insert(config('constants.defaultMetricheadings'));
    }
}
