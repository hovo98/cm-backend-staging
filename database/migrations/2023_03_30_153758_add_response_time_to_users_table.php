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
        if (!Schema::hasColumn('users', 'chat_response_time')) {
            Schema::table('users', function (Blueprint $table) {
                $table->float('chat_response_time')->nullable();
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
        if (Schema::hasColumn('users', 'chat_response_time')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('chat_response_time');
            });
        }
    }
};
