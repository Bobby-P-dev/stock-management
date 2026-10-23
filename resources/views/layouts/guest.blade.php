<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#F8FAFC]">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name', 'Berkah Mandiri Inventory') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
        <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-[#F8FAFC] text-slate-800 font-sans antialiased selection:bg-blue-600 selection:text-white relative flex flex-col justify-center items-center p-4 sm:p-6 lg:p-10 overflow-x-hidden">
        {{-- Soft Atmospheric Background Accents --}}
        <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
            <div class="absolute -top-32 -left-32 w-96 sm:w-[500px] h-96 sm:h-[500px] bg-blue-100/40 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-32 -right-32 w-96 sm:w-[500px] h-96 sm:h-[500px] bg-slate-200/40 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 left-1/3 -translate-y-1/2 w-80 h-80 bg-indigo-50/30 rounded-full blur-2xl"></div>
        </div>

        <div class="relative z-10 w-full flex flex-col items-center justify-center">
            {{ $slot }}
        </div>

        @livewireScripts
    </body>
</html>
