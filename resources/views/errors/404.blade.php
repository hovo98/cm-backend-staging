@extends('layouts.simple')

@section('content')
    <body class="bg-gradient-primary">

    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center">

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body pt-5 pb-5">
                        <!-- 404 Error Text -->
                        <div class="text-center ">
                            <div class="error mx-auto" data-text="404">404</div>
                            <p class="lead text-gray-800 mb-5">Page Not Found</p>
                            <p class="text-gray-500 mb-0">It looks like you found a glitch in the matrix...</p>
                            <a href="{{ route('home') }}">&larr; Back to Dashboard</a>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    </body>
@endsection
