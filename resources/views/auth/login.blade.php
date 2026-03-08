<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded bg-slate-900 border-slate-600 text-indigo-400 shadow-sm focus:ring-indigo-500 focus:ring-offset-slate-900" name="remember">
                <span class="ms-2 text-sm text-slate-300">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="mt-6 flex flex-col gap-4">
            <div class="flex items-center justify-between">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-slate-300 hover:text-slate-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 focus:ring-offset-slate-900" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif

                <x-primary-button>
                    {{ __('Log in') }}
                </x-primary-button>
            </div>

            <div class="flex flex-col gap-2">
                @if (Route::has('register'))
                    <a href="{{ route('register') }}"
                       class="w-full inline-flex justify-center px-4 py-2 rounded-md border border-slate-700 text-sm font-medium text-slate-200 hover:bg-slate-800 transition">
                        {{ __("Don't have an account? Create one") }}
                    </a>
                @endif

                <a href="{{ route('schools.index') }}"
                   class="w-full inline-flex justify-center px-4 py-2 rounded-md text-sm font-medium text-slate-300 hover:text-slate-100 hover:bg-slate-900/40 transition">
                    {{ __('Continue as guest to browse schools') }}
                </a>
            </div>
        </div>
    </form>
</x-guest-layout>
