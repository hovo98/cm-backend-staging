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
        Schema::create('deal_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id');
            $table->foreign('deal_id')
                ->references('id')
                ->on('deals')
                ->onDelete('cascade');
            $table->string('email');
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
        Schema::dropIfExists('deal_emails');
    }
};
