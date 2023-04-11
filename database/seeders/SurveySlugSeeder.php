<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class SurveySlugSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('survey')->where('surveyId', 1)->update(
            [
                'slug' => 'coaching-readiness',
            ]
        );
        DB::table('survey')->where('surveyId', 2)->update(
            [
                'slug' => 'sweet-spot-analysis',
            ]
        );
        DB::table('survey')->where('surveyId', 3)->update(
            [
                'slug' => 'core-disciplines',
            ]
        );
        DB::table('survey')->where('surveyId', 4)->update(
            [
                'slug' => 'ideal-client-profile',
            ]
        );

        // dd($surveyArray);
    }
}
