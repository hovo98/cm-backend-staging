<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChangeTermsheetValueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('termsheets')->where('title', '=', 'Closed')->update(['title' => 'Quote Accepted']);
    }
}
