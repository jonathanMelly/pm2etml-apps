<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{session('theme')??'light'}}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">

        <!-- Scripts -->
        <script src="{{ asset('js/app.js') }}" defer></script>

        @stack('custom-scripts')
    </head>
    <body class="font-sans antialiased">

        <div class="min-h-screen bg-base-100 flex flex-col">
            {{ $top??'' }}

            <main>
                {{ $slot }}
            </main>

            <div class="sm:mb-2 mb-1 sm:mt-3 mt-2">
                <footer class="sm:mx-6 footer-center bg-base-300 text-base-content rounded-box text-xs">
                    <div>
                        <p>Copyright © {{date('Y')}} - All right reserved by PM2ETML</p>
                    </div>
                </footer>
            </div>
        </div>

    </body>
</html>
