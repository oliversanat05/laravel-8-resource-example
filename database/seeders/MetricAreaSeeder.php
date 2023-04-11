<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\wowModels\MetricArea;

class MetricAreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        MetricArea::insert(config('constants.defaultMetricAreas'));
    }
}
