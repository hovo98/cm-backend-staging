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
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->string('street_address');
            $table->string('street_address_2');
            $table->string('city');
            $table->string('state');
            $table->string('zip_code');
            $table->string('block');
            $table->string('lot');
            $table->string('sponsor_name');
            $table->boolean('add_another_sponsor');
            $table->integer('sponsor_ownership');
            $table->integer('sponsor_ownership_1');
            $table->integer('years_experience');
            $table->integer('family_experience');
            $table->string('upload_pfs');
            $table->integer('sponsor_annual_income');
            $table->integer('sponsor_annual_expenses');
            $table->integer('sponsor_liabilities');
            $table->string('sponsor_assets_real_estate');
            $table->string('sponsor_assets_companies');
            $table->string('sponsor_assets_other');
            $table->string('sponsor_assets_liquid');
            $table->string('loan_type');
            $table->string('status')->nullable();
            $table->foreignId('user_id');
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
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
        Schema::dropIfExists('deals');
    }
};
