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
            $table->float('interest_rate', 4, 2)->nullable();
            $table->float('origination_fee_spread', 4, 2)->nullable();
            $table->float('interest_rate_spread', 4, 2)->nullable();
            $table->float('interest_rate_float', 4, 2)->nullable();
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
            $table->dropColumn(['interest_rate', 'origination_fee_spread', 'interest_rate_spread', 'interest_rate_float']);
        });
    }
};
