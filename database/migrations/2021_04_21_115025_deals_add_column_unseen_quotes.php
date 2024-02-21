<?php

use App\Deal;
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
        // Add the column
        Schema::table('deals', function (Blueprint $table) {
            $table->boolean('unseen_quotes')->default(false);
        });

        // Populate it with real data
        Deal::where('finished', true)
            ->cursor()
            ->each(function ($deal) {
                if ($deal->unseenQuotes()->count() > 0) {
                    $deal->unseen_quotes = true;
                    $deal->save();
                }
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropColumn('unseen_quotes');
        });
    }
};
