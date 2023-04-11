<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMetricAreasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('metric_areas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('user_id')->on('users')->constrained('users')->onDelete('cascade');
            $table->tinyInteger('metric_type')->default(1)->comment('1: DEMOGRAPHIC | 2:PSYCHOGRAPHIC');
            $table->boolean('is_default')->default(1)->comment('1 : global | 0 : local');
            $table->string('title', 145)->default('');
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
        Schema::dropIfExists('metric_areas');
    }
}
