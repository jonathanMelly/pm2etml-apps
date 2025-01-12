<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{session('theme')??'light'}}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

    @vite(['resources/css/app.css','resources/sass/app.scss', 'resources/js/app.js'])

    @stack('custom-scripts')
</head>
<body class="font-sans antialiased">

<div class="min-h-screen bg-base-100 flex flex-col">

    <main class="min-h-[calc(100vh-2.5rem)]">{{--2.5 matches footer h-5*2--}}
        {{ $top??'' }}
        {{ $slot }}
    </main>

    <div class="sm:mb-2 mb-1 sm:mt-3 mt-2 h-5">
        <footer class="sm:mx-6 bg-base-300 text-base-content rounded-box text-xs">
            <div class="flex flex-row justify-center content-center gap-1">
                <div>
                    Copyright © {{date('Y')}} - All right reserved by PM2ETML -
                </div>
                <div>
                    Version {!! $version !!}
                </div>
                @env('staging','production')
                    <div>|</div>
                    <a target="_blank" href="https://github.com/jonathanMelly/pm2etml-apps#readme"><i class="fa-brands fa-github"></i> </a>

                    <a href="https://github.com/jonathanMelly/pm2etml-apps/issues" target="_blank">
                        <img class="h-4"
                             src="https://img.shields.io/github/issues-raw/jonathanMelly/pm2etml-apps?style=plastic?cacheSeconds=54000">{{--15minutes cache--}}
                    </a>

                    <a href="https://wakatime.com/@bf7fcc14-d7d0-41c4-99cb-bbe8ecef41bf/projects/ctusfaxkkd" target="_blank">
                        <img class="h-4"
                             src="https://wakatime.com/badge/user/bf7fcc14-d7d0-41c4-99cb-bbe8ecef41bf/project/4fb00346-5e05-4e6b-a906-57e91c256d09.svg"
                             alt="wakatime">
                    </a>

                @endenv
            </div>
        </footer>
    </div>
</div>

</body>
</html>
