<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTiersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tiers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();;
            $table->foreign('user_id')->references('user_id')->on('users')->constrained('users')->onDelete('cascade');
            $table->char('designator',1);
            $table->string('tier_type',145);
            $table->integer('min_value');
            $table->integer('max_value');
            $table->boolean('status')->default(1)->comment("1: Active | 0: Deactive");
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
        Schema::dropIfExists('tiers');
    }
}
