<nav class="bg-slate-800/95 border-b border-slate-700">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex justify-between h-16 items-center">

            <!-- Logo -->
            <div class="flex items-center gap-8">
                <a href="{{ url('/') }}"
                   class="font-display font-bold text-lg text-slate-100">
                    SchoolSense
                    
                </a>
                <!--small space between schoolsense and nav links-->
                @auth
                    @if(auth()->user()->role !== 'school_manager')
                        <x-nav-link :href="route('favorites.index')" :active="request()->routeIs('favorites.*')">
                            {{ __('Favorites') }}
                        </x-nav-link>
                    @endif

                    @if(auth()->user()->role === 'admin')
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                            {{ __('Admin') }}
                        </x-nav-link>
                    @endif

                    @if(auth()->user()->role === 'school_manager')
                        <x-nav-link :href="route('school-manager.dashboard')" :active="request()->routeIs('school-manager.*')">
                            {{ __('Manager Dashboard') }}
                        </x-nav-link>
                    @endif
                @endauth
                
            </div>

            

            <!-- Right Side -->
            <div class="flex items-center gap-6">

                @auth
                    <!-- If Logged In -->
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-slate-300">
                            {{ auth()->user()->name }}
                        </span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="text-sm text-slate-300 hover:underline">
                            Logout
                        </button>
                    </form>
                @else
                    <!-- If NOT Logged In -->
                    <a href="{{ route('login') }}"
                       class="text-sm text-slate-300 hover:underline">
                        Login
                    </a>

                    <a href="{{ route('school-manager-applications.create') }}"
                       class="text-sm text-slate-300 hover:underline">
                        Apply as Manager
                    </a>

                    <a href="{{ route('register') }}"
                       class="text-sm px-4 py-1.5 rounded-md
                              bg-indigo-600 text-white hover:bg-indigo-700 transition">
                        Register
                    </a>
                @endauth

            </div>

        </div>
    </div>
</nav>
