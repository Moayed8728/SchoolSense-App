@php
    $navIcon = function (string $name): string {
        return match ($name) {
            'home' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 11.25 12 4.5l8.25 6.75M5.25 10.5v8.25h4.5V13.5h4.5v5.25h4.5V10.5" />',
            'search' => '<path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.25-5.25m1.5-5.25a6.75 6.75 0 1 1-13.5 0 6.75 6.75 0 0 1 13.5 0Z" />',
            'compare' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h12m0 0-3-3m3 3-3 3M16.5 16.5h-12m0 0 3 3m-3-3 3-3" />',
            'ai' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.75 8.4 7.65 4.5 9l3.9 1.35 1.35 3.9 1.35-3.9L15 9l-3.9-1.35-1.35-3.9ZM17.25 10.5l-.9 2.55-2.55.9 2.55.9.9 2.55.9-2.55 2.55-.9-2.55-.9-.9-2.55ZM9 16.5l-.6 1.65-1.65.6 1.65.6L9 21l.6-1.65 1.65-.6-1.65-.6L9 16.5Z" />',
            'dashboard' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 5.25h6v6h-6v-6Zm9 0h6v3.75h-6V5.25Zm0 6.75h6v6.75h-6V12Zm-9 2.25h6v4.5h-6v-4.5Z" />',
            'verify' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m5.25 2.25A8.25 8.25 0 1 1 3.75 12a8.25 8.25 0 0 1 16.5 0Z" />',
            'favorite' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25s-7.5-4.35-7.5-10.125A4.125 4.125 0 0 1 12 7.875a4.125 4.125 0 0 1 7.5 2.25C19.5 15.9 12 20.25 12 20.25Z" />',
            'profile' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 19.5a7.5 7.5 0 0 1 15 0" />',
            'login' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 8.25 15.75 12 12 15.75M15.75 12H21" />',
            'logout' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M18 15.75 21.75 12 18 8.25M21.75 12H10.5" />',
            'apply' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 4.5h9l3 3v12H6v-15Zm8.25 0V8.25H18M8.25 12h7.5M8.25 15h7.5" />',
            'register' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM3.75 20.25a7.5 7.5 0 0 1 15 0M18 8.25v4.5m2.25-2.25h-4.5" />',
            default => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15" />',
        };
    };

    $navItem = function (string $label, string $route, string $pattern, string $icon) use ($navIcon) {
        $active = request()->routeIs($pattern);
        $class = $active
            ? 'border-cyan-300/35 bg-cyan-300/12 text-cyan-100 shadow-sm shadow-cyan-950/20'
            : 'border-transparent text-slate-300 hover:border-slate-600/60 hover:bg-slate-700/35 hover:text-slate-50';

        return '<a href="' . e(route($route)) . '"' . ($active ? ' aria-current="page"' : '') . ' title="' . e($label) . '" class="group/nav flex min-h-10 items-center gap-3 rounded-lg border px-2 py-2 text-sm font-semibold transition duration-200 ' . $class . '">'
            . '<span class="grid h-6 w-6 shrink-0 place-items-center">'
            . '<svg xmlns="http://www.w3.org/2000/svg" class="h-[1.05rem] w-[1.05rem] shrink-0 ' . ($active ? 'text-cyan-200' : 'text-slate-500 group-hover/nav:text-cyan-200') . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">'
            . $navIcon($icon)
            . '</svg>'
            . '</span>'
            . '<span class="sidebar-label truncate">' . e($label) . '</span>'
            . '</a>';
    };

    $roleLabel = auth()->check()
        ? str(auth()->user()->role)->replace('_', ' ')->title()
        : null;
@endphp

<aside
    class="group/sidebar fixed inset-y-0 left-0 z-40 flex h-screen w-16 flex-col overflow-hidden border-r border-slate-700/80 bg-slate-900/95 px-2.5 py-3 shadow-2xl shadow-slate-950/30 backdrop-blur transition-[width,box-shadow,border-color] duration-300 ease-out hover:w-56 hover:border-cyan-300/30 hover:shadow-cyan-950/20 focus-within:w-56"
>
    <div>
        <a href="{{ url('/') }}" class="group/logo flex items-center gap-3 rounded-xl px-0.5 py-1.5 transition duration-200 hover:bg-slate-800/55" title="SchoolSense">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-cyan-300 text-sm font-black text-slate-950 shadow-lg shadow-cyan-950/20">SS</span>
            <span class="sidebar-label min-w-0">
                <span class="block font-display text-base font-bold leading-5 text-slate-50">SchoolSense</span>
                <span class="mt-0.5 block text-xs font-medium text-slate-500">School discovery</span>
            </span>
        </a>
    </div>

    <nav class="mt-5 flex flex-1 flex-col gap-4">
        <div>
            <p class="sidebar-label mb-2 px-2 text-[0.68rem] font-bold uppercase tracking-[0.18em] text-slate-500">Explore</p>
            <div class="grid gap-1">
                {!! $navItem('Home', 'schools.index', 'schools.*', 'home') !!}
                {!! $navItem('AI Search', 'search.index', 'search.*', 'search') !!}
                {!! $navItem('Compare', 'compare.index', 'compare.*', 'compare') !!}
            </div>
        </div>

        @auth
            @if(auth()->user()->role === 'admin')
                <div>
                    <p class="sidebar-label mb-2 px-2 text-[0.68rem] font-bold uppercase tracking-[0.18em] text-slate-500">Admin</p>
                    <div class="grid gap-1">
                        {!! $navItem('Dashboard', 'admin.dashboard', 'admin.dashboard', 'dashboard') !!}
                        {!! $navItem('AI Diagnostics', 'admin.ai-diagnostics', 'admin.ai-diagnostics', 'ai') !!}
                        {!! $navItem('School Verification', 'admin.school-verification.index', 'admin.school-verification.*', 'verify') !!}
                    </div>
                </div>
            @elseif(auth()->user()->role === 'school_manager')
                <div>
                    <p class="sidebar-label mb-2 px-2 text-[0.68rem] font-bold uppercase tracking-[0.18em] text-slate-500">Management</p>
                    <div class="grid gap-1">
                        {!! $navItem('Manager Dashboard', 'school-manager.dashboard', 'school-manager.*', 'dashboard') !!}
                    </div>
                </div>
            @endif

            <div>
                <p class="sidebar-label mb-2 px-2 text-[0.68rem] font-bold uppercase tracking-[0.18em] text-slate-500">Personal</p>
                <div class="grid gap-1">
                    {!! $navItem('Favorites', 'favorites.index', 'favorites.*', 'favorite') !!}
                    {!! $navItem('Profile', 'profile.edit', 'profile.*', 'profile') !!}
                </div>
            </div>
        @else
            <div>
                <p class="sidebar-label mb-2 px-2 text-[0.68rem] font-bold uppercase tracking-[0.18em] text-slate-500">Account</p>
                <div class="grid gap-1">
                    {!! $navItem('Login', 'login', 'login', 'login') !!}
                    {!! $navItem('Apply as Manager', 'school-manager-applications.create', 'school-manager-applications.*', 'apply') !!}
                    {!! $navItem('Register', 'register', 'register', 'register') !!}
                </div>
            </div>
        @endauth
    </nav>

    @auth
        <div class="mt-5 border-t border-slate-700/80 pt-3">
            <div class="rounded-xl border border-slate-700/70 bg-slate-800/65 p-2 transition duration-200 group-hover/sidebar:bg-slate-800/80 group-focus-within/sidebar:bg-slate-800/80">
                <div class="flex items-center gap-3">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-700 text-xs font-bold text-cyan-100">
                        {{ str(auth()->user()->name)->substr(0, 1)->upper() }}
                    </span>
                    <div class="sidebar-label min-w-0">
                        <p class="truncate text-sm font-semibold text-slate-100">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-slate-500">{{ $roleLabel }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" title="Logout" class="group/logout flex min-h-9 w-full items-center gap-3 rounded-lg border border-slate-700/80 bg-slate-900/45 px-2 py-1.5 text-sm font-semibold text-slate-300 transition duration-200 hover:border-rose-300/35 hover:bg-rose-500/10 hover:text-rose-100">
                        <span class="grid h-5 w-5 shrink-0 place-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-[1.05rem] w-[1.05rem] text-rose-200/80 transition group-hover/logout:text-rose-100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                {!! $navIcon('logout') !!}
                            </svg>
                        </span>
                        <span class="sidebar-label truncate">Logout</span>
                    </button>
                </form>
            </div>
        </div>
    @endauth
</aside>
