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
    <div
        x-data="{
            sidebarOpen: localStorage.getItem('schoolSenseSidebarOpen') === null
                ? window.innerWidth >= 768
                : localStorage.getItem('schoolSenseSidebarOpen') === 'true',
            setSidebar(open) {
                this.sidebarOpen = open
                localStorage.setItem('schoolSenseSidebarOpen', open)
            }
        }"
        class="min-h-screen"
        @keydown.escape.window="setSidebar(false)"
    >
        <button
            type="button"
            class="fixed top-4 z-50 inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-600/80 bg-slate-900/90 text-slate-100 shadow-lg shadow-slate-950/20 backdrop-blur transition-all duration-200 hover:border-cyan-300/60 hover:bg-slate-800"
            :class="sidebarOpen ? 'left-4 md:left-72' : 'left-4'"
            :aria-label="sidebarOpen ? 'Close sidebar' : 'Open sidebar'"
            :title="sidebarOpen ? 'Close sidebar' : 'Open sidebar'"
            @click="setSidebar(! sidebarOpen)"
        >
            <svg x-show="! sidebarOpen" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg x-show="sidebarOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>

        <div
            x-show="sidebarOpen"
            x-cloak
            class="fixed inset-0 z-30 bg-slate-950/60 backdrop-blur-sm md:hidden"
            @click="setSidebar(false)"
        ></div>

        @include('layouts.sidebar')
        <div class="min-h-screen min-w-0 transition-[padding] duration-200" :class="sidebarOpen ? 'md:pl-64' : 'pl-16 md:pl-16'">
            @if(session('success'))
                <div data-toast-message="{{ session('success') }}" data-toast-type="success"></div>
            @endif
            @if(session('error'))
                <div data-toast-message="{{ session('error') }}" data-toast-type="error"></div>
            @endif
            <main class="px-6 pb-10 pt-20">
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
                toast.textContent = source.dataset.toastMessage
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
