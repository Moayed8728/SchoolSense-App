<aside class="w-64 bg-slate-800/95 border-r border-slate-700 min-h-screen p-6 flex flex-col gap-8">
    <div class="mb-8">
        <a href="{{ url('/') }}" class="font-display font-bold text-2xl text-slate-100">SchoolSense</a>
    </div>
    @auth
        <nav class="flex flex-col gap-4">
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}" class="text-slate-100 hover:text-indigo-400 {{ request()->routeIs('admin.*') ? 'font-bold' : '' }}">Admin Dashboard</a>
                <a href="{{ route('favorites.index') }}" class="text-slate-100 hover:text-indigo-400 {{ request()->routeIs('favorites.*') ? 'font-bold' : '' }}">Favorites</a>
            @elseif(auth()->user()->role === 'school_manager')
                <a href="{{ route('school-manager.dashboard') }}" class="text-slate-100 hover:text-indigo-400 {{ request()->routeIs('school-manager.*') ? 'font-bold' : '' }}">Manager Dashboard</a>
                <a href="{{ route('favorites.index') }}" class="text-slate-100 hover:text-indigo-400 {{ request()->routeIs('favorites.*') ? 'font-bold' : '' }}">Favorites</a>
            @elseif(auth()->user()->role === 'parent')
                <a href="#" class="text-slate-100 hover:text-indigo-400">Parent Home</a>
                <a href="{{ route('favorites.index') }}" class="text-slate-100 hover:text-indigo-400 {{ request()->routeIs('favorites.*') ? 'font-bold' : '' }}">Favorites</a>
            @endif
        </nav>
        <div class="mt-auto flex flex-col gap-2">
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