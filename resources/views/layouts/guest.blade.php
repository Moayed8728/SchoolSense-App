<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SchoolSense') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
        <body class="auth-shell font-sans text-slate-100 antialiased">
        <div class="flex min-h-screen flex-col items-center px-4 py-8 sm:justify-center">
            <div class="mb-5 text-center">
                <a href="/" class="inline-flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-300 font-black text-slate-950 shadow-lg shadow-cyan-950/20">SS</span>
                    <span class="text-left">
                        <span class="block font-display text-lg font-bold leading-5 text-slate-50">SchoolSense</span>
                        <span class="text-xs text-slate-500">AI school discovery</span>
                    </span>
                </a>
            </div>

            <div class="auth-card w-full {{ request()->routeIs('school-manager-applications.create') ? 'max-w-4xl' : 'sm:max-w-md' }}">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
