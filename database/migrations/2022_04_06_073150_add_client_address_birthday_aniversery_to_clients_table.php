<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClientAddressBirthdayAniverseryToClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->date('birthday')->nullable()->after('name');
            $table->date('aniversery')->nullable()->after('birthday');
            $table->string('address')->nullable()->after('aniversery');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('birthday');
            $table->dropColumn('aniversery');
            $table->dropColumn('address');
        });
    }
}
