<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AssetTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\AssetTypes::create([
            'title' => 'Retail',
        ]);

        \App\AssetTypes::create([
            'title' => 'Office',
        ]);

        \App\AssetTypes::create([
            'title' => 'Industrial',
        ]);

        \App\AssetTypes::create([
            'title' => 'Mixed use',
        ]);

        \App\AssetTypes::create([
            'title' => 'Construction',
        ]);

        \App\AssetTypes::create([
            'title' => 'Owner occupied',
        ]);

        \App\AssetTypes::create([
            'title' => 'Land',
        ]);

        \App\AssetTypes::create([
            'title' => 'Multifamily',
        ]);
    }
}
