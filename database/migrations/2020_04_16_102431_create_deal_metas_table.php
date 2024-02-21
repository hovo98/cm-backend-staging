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
        Schema::create('deal_metas', function (Blueprint $table) {
            $table->id();
            $table->string('metable_type');
            $table->string('key');
            $table->text('value');
            $table->foreignId('metable_id');
            $table->foreign('metable_id')
                  ->references('id')
                  ->on('deals')
                  ->onDelete('cascade');
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
        Schema::dropIfExists('deal_metas');
    }
};
