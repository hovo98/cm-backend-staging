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
        Schema::create('rooms', function (Blueprint $table) {
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
            $table->foreignId('deal_id');
            $table->foreign('deal_id')
                ->references('id')
                ->on('deals')
                ->onDelete('cascade');
            $table->string('company');
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
        Schema::dropIfExists('rooms');
    }
};
