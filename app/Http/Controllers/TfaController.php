<?php

namespace App\Http\Controllers;

use App\User;

class TfaController extends Controller
{
    public function updateUsersTfa()
    {
        $users = User::where('role', 'admin')->update(['tfa' => true]);
        echo 'Admins are updated';
    }
}
