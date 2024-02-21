<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Class AdminProfile
 *
 * @author Boris Djemrovski <boris@forwardslashny.com>
 */
class AdminProfile extends Controller
{
    /**
     * Handle updating Admin's profile
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function editProfile(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'first_name' => ['required', 'max:50'],
            'last_name' => ['required', 'max:50'],
        ]);

        $user = Auth::user();

        $user->first_name = $validatedData['first_name'];
        $user->last_name = $validatedData['last_name'];
        $user->tfa = $request->has('tfa');

        $user->save();

        $request->session()->flash('status', 'Your profile has been updated');

        return redirect()->route('profile');
    }

    /**
     * Handle updating Admin's password
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function editPassword(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'password_old' => ['current_password'],
            'password' => [
                'required',
                'string',
                'confirmed',
                'min:10',             // must be at least 10 characters in length
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*#?&]/', // must contain a special character
            ],
        ]);

        $user = Auth::user();

        $user->password = Hash::make($validatedData['password']);

        $user->save();

        $request->session()->flash('status', 'Your password has been updated');

        return redirect()->route('profile');
    }
}
