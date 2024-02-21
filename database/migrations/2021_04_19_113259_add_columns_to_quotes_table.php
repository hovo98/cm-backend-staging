<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->integer('dollar_amount')->nullable();
            $table->integer('interest_rate')->nullable();
            $table->string('rate_term')->nullable();
            $table->integer('origination_fee')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn('dollar_amount');
            $table->dropColumn('interest_rate');
            $table->dropColumn('rate_term');
            $table->dropColumn('origination_fee');
        });
    }
};
