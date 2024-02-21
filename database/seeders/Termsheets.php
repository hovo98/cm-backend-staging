<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Termsheets extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //        DB::statement("TRUNCATE TABLE termsheets RESTART IDENTITY CASCADE");
        //        DB::table('termsheets')->truncate();

        \App\Termsheet::create([
            'id' => 1,
            'title' => 'Open',
        ]);

        \App\Termsheet::create([
            'id' => 2,
            'title' => 'Term sheet',
        ]);

        \App\Termsheet::create([
            'id' => 3,
            'title' => 'Underwriting',
        ]);

        \App\Termsheet::create([
            'id' => 4,
            'title' => 'Approved',
        ]);

        \App\Termsheet::create([
            'id' => 5,
            'title' => 'Closed',
        ]);
    }
}
