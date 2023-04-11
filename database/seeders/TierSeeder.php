<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\wowModels\Tier;

class TierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Tier::insert(config('constants.defaultTiers'));
    }
}
