<?php

namespace App\Http\Controllers;

class MaintenanceController extends Controller
{
    public function index()
    {
        return response()->json('mode')->header('Content-Type', 'text/plain')->header('Access-Control-Allow-Origin', '*');
    }
}
