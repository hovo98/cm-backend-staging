@extends('layouts.default')
<style>
 .switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

/* Hide default HTML checkbox */
.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}   
/* The slider */
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #eaecf4;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #055d64;
}

input:focus + .slider {
  box-shadow: 0 0 1px #033b40;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}
/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
</style>
@section('content')

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Profile</h1>
    </div>

    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <div class="row">

        <!-- Area Chart -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <!-- Card Header -->
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Profile Info</h6>
                </div>
                <!-- Card Body -->
                <div class="card-body">
                    <div class="row pb-3 pt-3">
                        <div class="col-sm-3">
                            <img class="rounded-circle float-right" src="{{ $currentUser->gravatar(120) }}" alt="">
                        </div>
                        <div class="col-sm-9 pt-4">
                            <p>You can set or update your profile image at Gravatar.</p>
                            <a class="btn btn-primary btn-sm" href="https://gravatar.com/emails/" target="_blank">Click here</a>
                        </div>
                    </div>

                    <form class="user" method="POST" action="{{ route('edit-profile') }}">
                        @csrf

                        <input type="hidden" name="type" value="profile">

                        <div class="form-group row">
                            <label for="email" class="col-sm-3 col-form-label mt-2 text-right text-right">Email Address:</label>
                            <div class="col-sm-9">
                                <input id="email" class="form-control form-control-user" type="email" disabled value="{{ $currentUser->email }}">
                                <p class="small text-muted mt-2 mb-1">Your email address cannot be changed. It is also used as username.</p>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="first-name" class="col-sm-3 col-form-label mt-2 text-right">First Name:</label>
                            <div class="col-sm-9">
                                <input id="first-name" name="first_name" class="form-control form-control-user @error('first_name') is-invalid @enderror" type="text" value="{{ $currentUser->first_name ?: old('first_name') }}" autocomplete="first-name" required>
                                @error('first_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="last-name" class="col-sm-3 col-form-label mt-2 text-right">Last Name:</label>
                            <div class="col-sm-9">
                                <input id="last-name" name="last_name" class="form-control form-control-user @error('last_name') is-invalid @enderror" type="text" value="{{ $currentUser->last_name ?: old('last_name') }}" required autocomplete="last-name" >
                                @error('last_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        {{-- <div class="form-group row">
                            <label for="tfa" class="col-sm-3 col-form-label mt-2 text-right">Two Factor Authentification</label>
                            <div class="col-sm-9 d-flex align-items-center">
                                <label class="switch">
                                    <input type="checkbox" name="tfa" {{ $currentUser->tfa ? 'checked' : '' }} value="{{ $currentUser->tfa ?: old('tfa') }}">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div> --}}
                        <div class="d-flex justify-content-center">
                            <button type="submit" class="btn btn-primary btn-user btn-block w-25">
                                {{ __('Update your profile') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Pie Chart -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <!-- Card Header -->
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Password Update</h6>
                </div>
                <!-- Card Body -->
                <div class="card-body">
                    <form class="user" method="POST" action="{{ route('edit-password') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="password-old" class="col-sm-3 col-form-label mt-2 text-right">Old Password:</label>
                            <div class="col-sm-9">
                                <input id="password-old" name="password_old" class="form-control form-control-user @error('password_old') is-invalid @enderror" type="password" required autocomplete="current-password">
                                @error('password_old')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-sm-3 col-form-label mt-2 text-right">New Password:</label>
                            <div class="col-sm-9">
                                <input id="password" name="password" class="form-control form-control-user @error('password') is-invalid @enderror" type="password" required>
                                <div class="text-muted small pt-3">
                                    <p class="m-0">Password criteria:</p>
                                    <ul class="m-0">
                                        <li>10 characters long</li>
                                        <li>1 lowercase letter</li>
                                        <li>1 uppercase letter</li>
                                        <li>1 digit</li>
                                        <li>1 special character (@$!%*#?&)</li>
                                    </ul>
                                </div>
                                @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password-confirmation" class="col-sm-3 col-form-label mt-2 text-right">Confirm:</label>
                            <div class="col-sm-9">
                                <input id="password-confirmation" name="password_confirmation" class="form-control form-control-user @error('password_confirmation') is-invalid @enderror" type="password" required>
                                @error('password_confirmation')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="d-flex justify-content-center">
                            <button type="submit" class="btn btn-primary btn-user btn-block w-25">
                                {{ __('Update Password') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
