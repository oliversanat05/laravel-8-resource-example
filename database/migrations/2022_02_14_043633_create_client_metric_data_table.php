<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientMetricDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_metric_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('metric_id')->references('id')->on('metric_areas')->onDelete('cascade')->nullable();
            $table->foreignId('listing_id')->references('id')->on('statement_listings')->onDelete('cascade')->nullable();
            $table->foreignId('metric_heading_id')->references('id')->on('metric_headings')->onDelete('cascade')->nullable();
            $table->string('metric_value')->nullable();
            $table->timeStamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_metric_data');
    }
}
