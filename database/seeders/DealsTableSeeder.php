<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DealsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Deal::factory()->count(1000)->create();
    }
}
