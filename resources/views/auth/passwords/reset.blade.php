@extends('layouts.simple')

@section('content')
    <body class="bg-gradient-primary">

    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center">

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-password-image"></div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-2">Forgot Your Password?</h1>
                                    </div>

                                    <form class="user" method="POST" action="{{ route('password.update') }}">
                                        @csrf

                                        <input type="hidden" name="token" value="{{ $token }}">
                                        <div>
                                            <p>Please, define your new password. It should have at least 10 characters and contains:</p>
                                            <ul>
                                                <li>1 lowercase letter</li>
                                                <li>1 uppercase letter</li>
                                                <li>1 digit (0-9)</li>
                                                <li>1 special character</li>
                                            </ul>
                                        </div>
                                        <div class="form-group" class="mb-2">
                                        <input type="hidden" name="email"
                                               class="form-control form-control-user @error('email') is-invalid @enderror"
                                               id="exampleInputEmail" aria-describedby="emailHelp"
                                               placeholder="Enter Email Address..." value="{{ $email ?? old('email') }}"
                                               required autocomplete="email">

                                            @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <input type="password" name="password"
                                                   class="form-control form-control-user @error('password') is-invalid @enderror"
                                                   id="password" placeholder="Password" required
                                                   autocomplete="new-password" autofocus>

                                            @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <input type="password" name="password_confirmation"
                                                   class="form-control form-control-user @error('password') is-invalid @enderror"
                                                   id="password-confirm" placeholder="Confirm Password" required
                                                   autocomplete="new-password">
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            {{ __('Reset Password') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    </body>
@endsection
