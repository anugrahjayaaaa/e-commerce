<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    {{-- head admin --}}
    @include('layouts.admin.partials.head')

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">

        {{-- sidebar admin --}}
        @include('layouts.admin.partials.sidebar')

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            {{-- header admin --}}
            @include('layouts.admin.partials.header')

            <!-- Main Content Start -->

            {{ $slot }}

            <!-- Main Content End -->

        </div>
    </div>

    {{-- script admin --}}
    @include('layouts.admin.partials.script')

</body>

</html>
