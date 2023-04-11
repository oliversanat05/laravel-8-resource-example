<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('name', 65);
            $table->integer('role_id')->default(2);
            $table->string('user_name',16)->default(0);
            $table->string('email',65)->nullable();
            $table->string('password',85);
            $table->string('description',80)->nullable();
            $table->string('user_image',45)->nullable();
            $table->integer('default_profile')->nullable();
            $table->boolean('status')->nullable();
            $table->string('dialNumber',45)->nullable();
            $table->string('accessCode',45)->nullable();
            $table->string('meetingLink',255)->nullable();
            $table->boolean('isCompleted')->default(1);
            $table->rememberToken();
            $table->dateTime('lastLoginDate')->nullable();
            $table->string('auth',256)->nullable();
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
        Schema::dropIfExists('users');
    }
}
