<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\wowModels\Ideas;

class WowIdeasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Ideas::insert(config('constants.defaultIdeas'));
    }
}
