<?php

namespace Database\Seeders;

use App\Broker;
use App\Lender;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    private $user;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // factory(Lender::class, 1000)
        //     ->create();

        //        factory(Broker::class, 25)
        //            ->create();
        //
        // Add testing Admin
        $admin = new User([
            'first_name' => 'Admin',
            'last_name' => 'Adminovski',
            'email' => 'admin@adminovski.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);
        $admin->save();
        //
        // Add testing Lender
        $lender = new User([
            'first_name' => 'Lender',
            'last_name' => 'Lenderovski',
            'email' => 'lender@lenderovski.com',
            'password' => Hash::make('lender123'),
            'role' => 'lender',
        ]);
        $lender->save();
        //
        // Add testing Broker
        $broker = new User([
            'first_name' => 'Broker',
            'last_name' => 'Brokerovski',
            'email' => 'broker@brokerovski.com',
            'password' => Hash::make('broker123'),
            'role' => 'broker',
        ]);
        $broker->save();
    }
}
