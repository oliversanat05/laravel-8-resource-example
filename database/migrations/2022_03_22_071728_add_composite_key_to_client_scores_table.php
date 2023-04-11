<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompositeKeyToClientScoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_scores', function (Blueprint $table) {
            $table->unique(["client_id", "metric_area_id","listing_id"], 'client_scores_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_scores', function (Blueprint $table) {
            $table->dropUnique('client_scores_unique');
        });
    }
}
