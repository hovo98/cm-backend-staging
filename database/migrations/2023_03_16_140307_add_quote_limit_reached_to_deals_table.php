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
        if (!Schema::hasColumn('deals', 'quote_limit_reached')) {
            Schema::table('deals', function (Blueprint $table) {
                $table->boolean('quote_limit_reached')->default(false);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('deals', 'quote_limit_reached')) {
            Schema::table('deals', function (Blueprint $table) {
                $table->dropColumn('quote_limit_reached');
            });
        }
    }
};
