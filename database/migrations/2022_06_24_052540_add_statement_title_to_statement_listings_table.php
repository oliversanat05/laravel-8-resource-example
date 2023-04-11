<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatementTitleToStatementListingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('statement_listings', function (Blueprint $table) {
            $table->string('statement_title')->nullable()->after('statement_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('statement_listings', function (Blueprint $table) {
            $table->dropColumn('statement_title');
        });
    }
}
