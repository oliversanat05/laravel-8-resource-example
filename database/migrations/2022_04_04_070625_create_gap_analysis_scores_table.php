<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGapAnalysisScoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gap_analysis_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->references('id')->on('clients')->onDelete('cascade')->nullable();
            $table->foreignId('listing_id')->references('id')->on('statement_listings')->onDelete('cascade')->nullable();
            $table->foreignId('gap_analysis_heading_id')->references('id')->on('gap_analysis_headings')->nullable();
            $table->integer('score');
            $table->unique(["client_id", "listing_id","gap_analysis_heading_id"], 'gap_analysis_unique');
            $table->timestamps();
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
        Schema::dropIfExists('gap_analysis_scores');
    }
}
