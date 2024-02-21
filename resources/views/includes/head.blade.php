<meta charset="UTF-8">
<meta name="viewport"
      content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">

<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>Admin Dashboard - {{ config('app.name', 'Laravel') }}</title>

<!-- Bootstrap core JavaScript-->
<script defer src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script defer src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

<!-- Core plugin JavaScript-->
<script defer src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>

<!-- Custom scripts for all pages-->
<script defer src="{{ asset('js/app.js') }}"></script>

<link
    href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
    rel="stylesheet">

<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.13.0/css/all.css">

<!-- Styles -->
<link href="{{ asset('css/app.css') }}" rel="stylesheet">

@yield('scripts')
