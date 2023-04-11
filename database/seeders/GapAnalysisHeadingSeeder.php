<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\wowModels\GapAnalysisHeading;

class GapAnalysisHeadingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        GapAnalysisHeading::insert(config('constants.defaultGapAnalyisHeadings'));
    }
}
