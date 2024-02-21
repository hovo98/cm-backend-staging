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
        Schema::table('deals', function (Blueprint $table) {
            $table->dropColumn('street_address');
            $table->dropColumn('street_address_2');
            $table->dropColumn('city');
            $table->dropColumn('state');
            $table->dropColumn('zip_code');
            $table->dropColumn('block');
            $table->dropColumn('lot');
            $table->dropColumn('sponsor_name');
            $table->dropColumn('add_another_sponsor');
            $table->dropColumn('sponsor_ownership');
            $table->dropColumn('sponsor_ownership_1');
            $table->dropColumn('years_experience');
            $table->dropColumn('family_experience');
            $table->dropColumn('upload_pfs');
            $table->dropColumn('sponsor_annual_income');
            $table->dropColumn('sponsor_annual_expenses');
            $table->dropColumn('sponsor_liabilities');
            $table->dropColumn('sponsor_assets_real_estate');
            $table->dropColumn('sponsor_assets_companies');
            $table->dropColumn('sponsor_assets_other');
            $table->dropColumn('sponsor_assets_liquid');
            $table->dropColumn('loan_type');
            $table->dropColumn('status');
            $table->dropColumn('step_status');
            $table->dropColumn('page_url');
            $table->dropColumn('filters');
            $table->boolean('finished')->default(false);
            $table->jsonb('data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
