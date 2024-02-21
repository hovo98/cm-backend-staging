<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\TfaLogin;
use App\Providers\RouteServiceProvider;
use App\User;
use Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Session;

/**
 * Class LoginController
 *
 * @author Boris Djemrovski <boris@forwardslashny.com>
 */
class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    private function sendCode($email)
    {
        if (Session::get('tfa_token') && Session::get('tfa_token') === $email) {
            $user = User::where('email', $email)->first();
            $token = $user->tfa_token;
        } else {
            $token = $this->generateCode();
            $user = User::where('email', $email)->first();
            // add to database
            $user->tfa_token = $token;
            $user->save();

            Mail::send(new TfaLogin($user, $token));
            Session::put('tfa_token', $email);
        }

        return view('auth.login-code', [
            'action_login' => route('login.2fa'),
            'action_login_resend' => route('login.2fa.resend'),
            'email' => $email,
            'token' => $token,
        ]);
    }

    private function generateCode()
    {
        $chars = 'ABCDEFGHIJKLMNOPRSTQUVWXYZ0123456789';
        $token = '';
        for ($i = 0; $i < rand(6, 10); $i++) {
            $c = rand(0, 34);
            $token .= $chars[$c];
        }

        return $token;
    }

    public function login(Request $request)
    {
        $user = User::where('email', $request->input('email'))->first();
        if ($user) {
            if ($user->role === 'admin') {
                return $this->checkCode($request);

                return $this->sendFailedLoginResponse($request);
            } else {
                return redirect()->back();
            }
        }

        return redirect()->back()->withErrors(['email' => 'No account found for this email address'])->withInput($request->only('email'));
    }

    protected function checkCode(Request $request)
    {
        $check_user = User::where('email', $request->input('email'))->first();
        if (! $check_user) {
            return redirect()->back()->with('error', 'Invalid access')->withInput($request->only('email'));
        }
        $password = $request->input('password');
        if (Hash::check($password, $check_user->password)) {
            if (intval($check_user->tfa)) {
                return $this->sendCode($request->input('email'));
            } else {
                Auth::login($check_user);

                return redirect(RouteServiceProvider::HOME);
            }
        }

        return redirect()->back()->with('error', 'Invalid access')->withInput($request->only('email'));
    }

    public function resendCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        Session::forget('tfa_token');

        return $this->sendCode($request->input('email'));
    }

    public function verification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            '2fa' => 'required|min:3',
        ]);
        $email = $request->input('email');
        $token = $request->input('2fa');
        if ($email && $token) {
            $check_user = User::where('email', $email)->where('tfa_token', $token)->first();
            if ($check_user) {
                Auth::login($check_user);
                $check_user->tfa_token = null;
                $check_user->save();
                Session::forget('tfa_token');

                return redirect(RouteServiceProvider::HOME);
            }
            $validator->errors()->add('2fa', 'Invalid code');

            return view('auth.login-code', [
                'email' => $email,
                'token' => $token,
                'action_login' => route('login.2fa'),
                'action_login_resend' => route('login.2fa.resend'),
            ])->withErrors($validator);
        }

        return redirect()->back()->with([
            'action_login' => route('login.2fa'),
            'action_login_resend' => route('login.2fa.resend'),
        ]);
    }
}
