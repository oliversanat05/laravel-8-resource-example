<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMetricHeadingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('metric_headings', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('metric_type')->default(1)->comment('1: DEMOGRAPHIC | 2:PSYCHOGRAPHIC');
            $table->string('heading');
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
        Schema::dropIfExists('metric_headings');
    }
}
