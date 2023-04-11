<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRelationalGridsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('relational_grids', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tier_id');
            $table->foreign('tier_id')->references('id')->on('tiers');
          /*   $table->string('responsible_person')->nullable(); */
            $table->foreignId('responsible_person_id')->references('id')->on('responsible_people')->onDelete('cascade')->nullable();
            $table->unsignedBigInteger('idea_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('user_id')->on('users');
            $table->foreign('idea_id')->references('id')->on('ideas')->onDelete('cascade');
            $table->boolean('status')->default(1)->comment("1: Yes | 0: No");
            $table->unique(["tier_id", "idea_id","user_id"], 'unique_grid_key');
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
        Schema::dropIfExists('relational_grids');
    }
}
