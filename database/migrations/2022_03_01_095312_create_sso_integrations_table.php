<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSsoIntegrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sso_integrations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->string('token_type');
            $table->text('access_token');
            $table->text('refresh_token');
            $table->datetime('expires_at');
            $table->string('related_email')->nullable();
            $table->string('related_username')->nullable();
            $table->string('related_user_id')->nullable();
            $table->text('related_user_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sso_integrations');
    }
}
