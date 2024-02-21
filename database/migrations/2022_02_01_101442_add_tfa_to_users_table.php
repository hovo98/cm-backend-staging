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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('tfa')->default(true)->after('password');
            $table->string('tfa_token')->nullable()->after('tfa');
            $table->datetime('tfa_at')->nullable()->after('tfa_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['tfa', 'tfa_token', 'tfa_at']);
        });
    }
};
