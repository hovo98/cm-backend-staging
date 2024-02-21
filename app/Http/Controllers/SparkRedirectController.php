<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class SparkRedirectController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        $user = User::find($request->get('user'));
        auth('web')->loginUsingId($user->id);

        return redirect()->to('/billing');
    }
}
