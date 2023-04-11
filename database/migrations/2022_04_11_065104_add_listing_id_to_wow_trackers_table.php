<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddListingIdToWowTrackersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wow_trackers', function (Blueprint $table) {
            $table->foreignId('listing_id')->references('id')->on('statement_listings')->onDelete('cascade')->nullable();
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
          /*   $table->dropColumn('listing_id'); */
        });
    }
}
