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
        Schema::table('email_notifications', function (Blueprint $table) {
            $table->after('mailable', function ($table) {
                $table->text('params')->nullable();
                $table->text('failed_reason')->nullable();
                $table->unsignedInteger('retries')->default(0);
                $table->timestamp('failed_at')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('email_notifications', function (Blueprint $table) {
            $table->dropColumn('params');
            $table->dropColumn('failed_reason');
            $table->dropColumn('retries');
            $table->dropColumn('failed_at');
        });
    }
};
