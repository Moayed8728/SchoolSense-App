<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SchoolSense')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --font-display: 'Syne', sans-serif;
            --font-body: 'DM Sans', sans-serif;
        }

        body { font-family: var(--font-body); }
        .font-display { font-family: var(--font-display); }

        .gradient-text {
            background: linear-gradient(135deg, #818cf8 0%, #a78bfa 50%, #c084fc 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .theme-transition * {
            transition: background-color .25s ease,
                        border-color .25s ease,
                        color .25s ease;
        }
    </style>
</head>

<body class="theme-transition min-h-screen antialiased
             bg-white text-slate-900
             dark:bg-slate-950 dark:text-slate-200">

    <!-- Theme Toggle -->
    <button id="themeToggle"
        class="fixed bottom-6 right-6 z-50 px-4 py-2 rounded-lg text-sm font-medium shadow-lg
               bg-slate-800 text-white
               dark:bg-white dark:text-black">
        Toggle Mode
    </button>

    @include('layouts.navigation')

    <main class="max-w-6xl mx-auto px-6 pt-10">
        @yield('content')
    </main>

</body>
</html>