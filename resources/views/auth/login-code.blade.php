@extends('layouts.simple')
<style>
    .form {
        display: flex;
    }

    .form-control {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    .form-control:focus {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
       box-shadow: 0 0 0 0.09rem rgb(5 93 100 / 25%) !important;
    }

    .btn {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    .btn-block {
        width: 50% !important;
    }

    .form-resend-code {
        display: flex;
        justify-content: center;
    }

    .resend-btn {
        border: none !important;
        background: none !important;
        color: #055d64 !important;
        text-decoration: underline !important;
    }
</style>
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

                <div class="col-xl-6 col-lg-12 col-md-9">
                    <div class="card o-hidden border-0 shadow-lg my-5">
                        <div class="card-body p-0 d-flex justify-content-center">
                            <div class="row">
                                <div class="justify-content-center">
                                    <div class="p-5">
                                        <div class="text-center">
                                            <h1 class="h4 text-gray-900 mb-4">Enter authentication code that you received in your email</h1>
                                        </div>
                                        <div>
                                            <form class="form" method="POST" action="{{ $action_login }}">
                                                @csrf
                                                <input type="hidden" name="email" value="{{ $email }}" />
                                                    <input id="2fa" class="form-control form-control-user" placeholder="Enter code here..." type="text" name="2fa" value="{{ old('2fa') }}" required autocomplete="off" autofocus>
                                                    <button class="btn btn-primary btn-user btn-block" type="submit">
                                                        Submit
                                                    </button>
                                            </form>
                                        @error('2fa')
                                            <span role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                        </div>
                                        <form class="form-resend-code" method="POST" action="{{ $action_login_resend }}">
                                            @csrf
                                            <input type="hidden" name="email" value="{{ $email }}" />
                                                <button class="resend-btn" type="submit">
                                                    Resend code
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
