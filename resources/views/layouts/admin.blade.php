<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ponce Miranda | Admin</title>

    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/ponce-skin.css') }}" rel="stylesheet">
</head>

<body id="page-top">

    <div id="wrapper">

        @include('partials.sidebar')

        <div id="content-wrapper" class="d-flex flex-column">

            <div id="content">

                @include('partials.topbar')

                <div class="container-fluid">
                    @yield('content')
                </div>

            </div>
            {{-- Footer Removed --}}

        </div>
    </div>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>

    {{-- SIDEBAR STATE PERSISTENCE --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const sidebar = document.querySelector('.sidebar');
            const body = document.querySelector('body');
            
            // 1. Restore State on Load
            if (localStorage.getItem('sidebar-toggled') === 'true') {
                body.classList.add('sidebar-toggled');
                if(sidebar) sidebar.classList.add('toggled');
            }

            // 2. Listen for Toggles
            const toggles = document.querySelectorAll('#sidebarToggle, #sidebarToggleTop');
            
            toggles.forEach(toggle => {
                toggle.addEventListener('click', function() {
                    // Wait for the transition to finish/class to be toggled by the theme JS
                    setTimeout(() => {
                        const isToggled = body.classList.contains('sidebar-toggled') || sidebar.classList.contains('toggled');
                        localStorage.setItem('sidebar-toggled', isToggled);
                    }, 250); 
                });
            });
        });
    </script>

    @stack('scripts')

</body>
</html>