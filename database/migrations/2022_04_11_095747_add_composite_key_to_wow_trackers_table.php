<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompositeKeyToWowTrackersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wow_trackers', function (Blueprint $table) {
            $table->unique(["client_id", "idea_id","tracker_id","listing_id","deleted_at"], 'unique_tracker');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wow_trackers', function (Blueprint $table) {
            // $table->dropUnique('unique_tracker');
        });
    }
}
