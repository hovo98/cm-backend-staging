<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class QuotesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Quote::factory()->count(1000)->create();
    }
}
