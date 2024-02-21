@extends('layouts.simple')

@section('content')
    <body class="bg-gradient-primary">
    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center">

            @isset( $loginMessage )
                <div class="col-xl-10 col-lg-12 col-md-9">
                    <div class="card o-hidden border-0 shadow-lg my-5">
                        <p>{{ $loginMessage }}</p>
                    </div>
                </div>
            @endisset

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
                                    </div>
                                    <form class="user" method="POST" action="{{ route('login') }}">
                                        @csrf

                                        <div class="form-group">
                                            <input type="email" name="email"
                                                   class="form-control form-control-user @error('email') is-invalid @enderror"
                                                   id="email" aria-describedby="emailHelp"
                                                   placeholder="Enter Email Address..." value="{{ old('email') }}"
                                                   required autocomplete="email" autofocus>
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
                                                   autocomplete="current-password">
                                            @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox small">
                                                <input type="checkbox" name="remember" id="remember"
                                                       class="custom-control-input">
                                                <label class="custom-control-label" for="remember">Remember
                                                    Me</label>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            Login
                                        </button>
                                    </form>
                                    @if (Route::has('password.request'))
                                    <hr>
                                    <div class="text-center">
                                        <a class="small" href="{{ route('password.request') }}">Forgot Password?</a>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>
    <script>
        let email = document.querySelector('input[type="email"]');
        email.addEventListener('input', function(e) {
            document.querySelector('.invalid-feedback').style.display = 'none';
            email.classList.remove('is-invalid');
        });
    </script>
    </body>
@endsection
