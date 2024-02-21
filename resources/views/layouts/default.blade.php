<!doctype html>
<html lang="en">
<head>
    @include('includes.head')
</head>
<body id="page-top">

<!-- Page Wrapper -->
<div id="wrapper">

    <!-- Sidebar -->
    @include('includes.sidebar')
    <!-- End of Sidebar -->

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

        <!-- Main Content -->
        <div id="content">

            <!-- Topbar -->
            @include('includes.topbar')
            <!-- End of Topbar -->

            <!-- Page Content -->
            <div class="container-fluid">
                @yield('content')
            </div>
            <!-- End of Page Content -->

        </div>
        <!-- End of Main Content -->

        <!-- Footer -->
        @include('includes.footer')
        <!-- End of Footer -->

    </div>
    <!-- End of Content Wrapper -->

</div>
<!-- End of Page Wrapper -->

<!-- Scroll to Top Button-->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>
@yield('footer-scripts')
</body>
</html>
