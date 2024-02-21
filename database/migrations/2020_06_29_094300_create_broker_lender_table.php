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
        Schema::create('broker_lender', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broker_id');
            $table->foreign('broker_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            $table->foreignId('lender_id');
            $table->foreign('lender_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            $table->boolean('is_blocked')->default(0);
            $table->softDeletes();
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
        Schema::dropIfExists('broker_lender');
    }
};
