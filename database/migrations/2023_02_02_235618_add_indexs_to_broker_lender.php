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
        Schema::table('broker_lender', function (Blueprint $table) {
            $table->index('broker_id');
            $table->index('lender_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('broker_lender', function (Blueprint $table) {
            $table->dropIndex(['broker_id']);
            $table->dropIndex(['lender_id']);
        });
    }
};
