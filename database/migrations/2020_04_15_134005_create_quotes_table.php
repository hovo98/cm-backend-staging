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
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_construction');
            $table->integer('loan_to_value_ratio');
            $table->integer('loan_to_cost_ratio');
            $table->integer('hard_costs');
            $table->integer('soft_costs');
            $table->integer('land_costs');
            $table->integer('closing_costs');
            $table->integer('carrying_costs');
            $table->string('interest_rate');
            $table->string('status')->nullable();
            $table->foreignId('user_id');
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            $table->foreignId('deal_id');
            $table->foreign('deal_id')
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
        Schema::dropIfExists('quotes');
    }
};
