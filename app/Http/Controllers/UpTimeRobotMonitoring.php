<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\User;

class UpTimeRobotMonitoring extends Controller
{
    public function index()
    {
        $user = User::first();

        return response()->json('mode')->header('Content-Type', 'text/plain')->header('Access-Control-Allow-Origin', '*');
    }
}
