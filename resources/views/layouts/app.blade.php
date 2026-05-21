<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth dark">
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

        * {
            transition: background-color .25s ease,
                        border-color .25s ease,
                        color .25s ease;
        }
    </style>
</head>

<body class="app-shell min-h-screen antialiased">
    <div id="app-intro" class="app-intro" aria-hidden="true">
        <div class="app-intro__frame">
            <div class="app-intro__name" aria-label="SchoolSense">
                <span style="--i: 0">S</span>
                <span style="--i: 1">c</span>
                <span style="--i: 2">h</span>
                <span style="--i: 3">o</span>
                <span style="--i: 4">o</span>
                <span style="--i: 5">l</span>
                <span style="--i: 6">S</span>
                <span style="--i: 7">e</span>
                <span style="--i: 8">n</span>
                <span style="--i: 9">s</span>
                <span style="--i: 10">e</span>
            </div>
            <div class="app-intro__line"></div>
            <p class="app-intro__tagline">Find the right school with clarity</p>
        </div>
    </div>

    <div class="min-h-screen">
        @include('layouts.sidebar')
        <div class="min-h-screen min-w-0 pl-16 transition-[padding] duration-300 md:pl-56">
            @if(session('success'))
                <div data-toast-message data-toast-type="success">{!! nl2br(e(session('success'))) !!}</div>
            @endif
            @if(session('error'))
                <div data-toast-message data-toast-type="error">{!! nl2br(e(session('error'))) !!}</div>
            @endif
            <main class="px-3 pb-6 pt-4 md:px-4">
                @yield('content')
            </main>
        </div>
    </div>
    <div id="toast-region" class="fixed right-6 top-6 z-50 flex w-[min(24rem,calc(100vw-3rem))] flex-col gap-3"></div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const region = document.getElementById('toast-region')
            const messages = document.querySelectorAll('[data-toast-message]')

            messages.forEach((source) => {
                const type = source.dataset.toastType || 'success'
                const toast = document.createElement('div')
                const isError = type === 'error'

                toast.className = [
                    'rounded-2xl border px-4 py-3 text-sm font-medium shadow-xl backdrop-blur transition-all duration-300',
                    isError
                        ? 'border-rose-300/35 bg-rose-500/15 text-rose-100 shadow-rose-950/20'
                        : 'border-emerald-300/35 bg-emerald-500/15 text-emerald-100 shadow-emerald-950/20'
                ].join(' ')
                toast.innerHTML = source.innerHTML
                region.appendChild(toast)

                window.setTimeout(() => {
                    toast.classList.add('opacity-0', '-translate-y-2')
                    window.setTimeout(() => toast.remove(), 300)
                }, 3000)
            })
        })
    </script>
</body>
</html>
