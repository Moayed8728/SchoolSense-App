<nav class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex justify-between h-16 items-center">

            <!-- Logo -->
            <div class="flex items-center">
                <a href="{{ url('/') }}"
                   class="font-display font-bold text-lg text-slate-900 dark:text-slate-100">
                    SchoolSense
                    
                </a>
                <!--small space between schoolsense and nav links-->
                @auth
                    @php($user = auth()->user())

                    @if($user->role !== 'school_manager')
                        <a href="{{ route('favorites.index') }}"
                           class="px-6 py-2 text-sm text-slate-400 hover:text-slate-200 rounded-lg hover:bg-slate-800/60 transition-all duration-200">
                            Favorites
                        </a>
                    @endif
                @endauth

                
            </div>

            

            <!-- Right Side -->
            <div class="flex items-center gap-6">

                @auth
                    <!-- If Logged In -->
                    <div class="flex items-center gap-4">
                        @if(auth()->user()->role === 'school_manager')
                            <a href="{{ route('school-manager.dashboard') }}"
                               class="text-sm text-slate-600 dark:text-slate-300 hover:underline">
                                Manager Dashboard
                            </a>
                        @endif

                        <span class="text-sm text-slate-600 dark:text-slate-300">
                            {{ auth()->user()->name }}
                        </span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="text-sm text-slate-600 dark:text-slate-300 hover:underline">
                            Logout
                        </button>
                    </form>
                @else
                    <!-- If NOT Logged In -->
                    <a href="{{ route('login') }}"
                       class="text-sm text-slate-600 dark:text-slate-300 hover:underline">
                        Login
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