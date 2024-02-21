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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->nullable();
            $table->string('domain');
            $table->string('company_address')->nullable();
            $table->string('company_city')->nullable();
            $table->string('company_state')->nullable();
            $table->string('company_zip_code')->nullable();
            $table->string('company_phone')->nullable();
            $table->boolean('is_approved')->default(0);
            $table->string('company_logo')->nullable();
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
        Schema::dropIfExists('companies');
    }
};
