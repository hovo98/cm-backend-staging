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
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn('is_construction');
            $table->dropColumn('loan_to_value_ratio');
            $table->dropColumn('loan_to_cost_ratio');
            $table->dropColumn('hard_costs');
            $table->dropColumn('soft_costs');
            $table->dropColumn('land_costs');
            $table->dropColumn('closing_costs');
            $table->dropColumn('carrying_costs');
            $table->dropColumn('interest_rate');
            $table->dropColumn('status');

            $table->boolean('finished')->default(false);
            $table->jsonb('data')->nullable();
            $table->text('lastStepStatus')->nullable();
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
