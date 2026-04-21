<aside
    class="fixed inset-y-0 left-0 z-40 flex h-screen w-64 flex-col gap-8 overflow-y-auto border-r border-slate-700 bg-slate-800/95 p-6 shadow-2xl shadow-slate-950/30 transition-transform duration-200"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
>
    <div class="mb-8 pr-12">
        <a href="{{ url('/') }}" class="font-display font-bold text-2xl text-slate-100">SchoolSense</a>
    </div>
    @auth
        <nav class="flex flex-col gap-4">
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('schools.index') }}" class="text-slate-100 hover:text-indigo-400 {{ request()->routeIs('schools.*') ? 'font-bold' : '' }}">Home</a>
                <a href="{{ route('admin.dashboard') }}" class="text-slate-100 hover:text-indigo-400 {{ request()->routeIs('admin.*') ? 'font-bold' : '' }}">Admin Dashboard</a>
                <a href="{{ route('favorites.index') }}" class="text-slate-100 hover:text-indigo-400 {{ request()->routeIs('favorites.*') ? 'font-bold' : '' }}">Favorites</a>
            @elseif(auth()->user()->role === 'school_manager')
                <a href="{{ route('schools.index') }}" class="text-slate-100 hover:text-indigo-400 {{ request()->routeIs('schools.*') ? 'font-bold' : '' }}">Home</a>
                <a href="{{ route('school-manager.dashboard') }}" class="text-slate-100 hover:text-indigo-400 {{ request()->routeIs('school-manager.*') ? 'font-bold' : '' }}">Manager Dashboard</a>
                <a href="{{ route('favorites.index') }}" class="text-slate-100 hover:text-indigo-400 {{ request()->routeIs('favorites.*') ? 'font-bold' : '' }}">Favorites</a>
            @elseif(auth()->user()->role === 'parent')
                <a href="{{ route('schools.index') }}" class="text-slate-100 hover:text-indigo-400 {{ request()->routeIs('schools.*') ? 'font-bold' : '' }}">Home</a>
                <a href="{{ route('favorites.index') }}" class="text-slate-100 hover:text-indigo-400 {{ request()->routeIs('favorites.*') ? 'font-bold' : '' }}">Favorites</a>
            @endif
        </nav>
        <div class="sticky bottom-0 mt-auto flex flex-col gap-2 border-t border-slate-700/80 bg-slate-800/95 pt-4">
            <span class="text-sm text-slate-300">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-slate-300 hover:underline">Logout</button>
            </form>
        </div>
    @else
        <nav class="flex flex-col gap-4">
            <a href="{{ route('login') }}" class="text-slate-100 hover:text-indigo-400">Login</a>
            <a href="{{ route('school-manager-applications.create') }}" class="text-slate-100 hover:text-indigo-400">Apply as Manager</a>
            <a href="{{ route('register') }}" class="text-slate-100 hover:text-indigo-400">Register</a>
        </nav>
    @endauth
</aside>
