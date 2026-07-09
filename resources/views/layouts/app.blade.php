<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    {{-- head --}}
    <link href="{{ asset('favicon.ico') }}" rel="shortcut icon" type="image/x-icon" />
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0369a1', // Adjust this to match your brand blue
                        secondary: '#075985',
                    },
                    container: {
                        center: true,
                        padding: '1rem',
                        screens: {
                            xl: '1200px',
                        },
                    }
                }
            }
        }
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/pe-icon-7-stroke.css') }}" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />

    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-600 antialiased">

    {{-- header --}}
    @include('layouts.partials.header')

    <!-- Main Content Start -->

    {{ $slot }}

    <!-- Main Content End -->

    {{-- footer --}}
    @include('layouts.partials.footer')

    {{-- script --}}
    @include('layouts.partials.script')

</body>
</html>
