<x-guest-layout>
    <div class="mb-6">
        <a class="inline-flex items-center gap-2 text-sm font-medium text-slate-300 hover:text-slate-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 focus:ring-offset-slate-900"
           href="{{ route('register') }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            {{ __('Back to normal registration') }}
        </a>
    </div>

    <h1 class="font-display text-2xl font-bold text-slate-100">Apply as a School Manager</h1>
    <p class="mt-2 text-sm text-slate-400">Submit your account details and school information. Admin approval creates your manager account and school listing.</p>

    <form method="POST" action="{{ route('school-manager-applications.store') }}" class="mt-6 space-y-5">
        @csrf

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <x-input-label for="fullName" :value="__('Full name')" />
                <x-text-input id="fullName" class="block mt-1 w-full" name="fullName" :value="old('fullName')" required autofocus />
                <x-input-error :messages="$errors->get('fullName')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm password')" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required />
            </div>
        </div>

        <div class="border-t border-slate-700/70 pt-5">
            <h2 class="font-display text-lg font-semibold text-slate-100">School information</h2>
        </div>

        <div>
            <x-input-label for="schoolName" :value="__('School name')" />
            <x-text-input id="schoolName" class="block mt-1 w-full" name="schoolName" :value="old('schoolName')" required />
            <x-input-error :messages="$errors->get('schoolName')" class="mt-2" />
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <x-input-label for="country" :value="__('Country code')" />
                <x-text-input id="country" class="block mt-1 w-full uppercase" name="country" :value="old('country', 'SA')" maxlength="2" required />
                <x-input-error :messages="$errors->get('country')" class="mt-2" />
            </div>

            <div class="md:col-span-2">
                <x-input-label for="city" :value="__('City')" />
                <x-text-input id="city" class="block mt-1 w-full" name="city" :value="old('city')" required />
                <x-input-error :messages="$errors->get('city')" class="mt-2" />
            </div>
        </div>

        <div>
            <x-input-label for="websiteUrl" :value="__('Official school website')" />
            <x-text-input id="websiteUrl" class="block mt-1 w-full" type="url" name="websiteUrl" :value="old('websiteUrl')" required />
            <x-input-error :messages="$errors->get('websiteUrl')" class="mt-2" />
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <x-input-label for="contactEmail" :value="__('Contact email')" />
                <x-text-input id="contactEmail" class="block mt-1 w-full" type="email" name="contactEmail" :value="old('contactEmail')" />
                <x-input-error :messages="$errors->get('contactEmail')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="contactPhone" :value="__('Contact phone')" />
                <x-text-input id="contactPhone" class="block mt-1 w-full" name="contactPhone" :value="old('contactPhone')" />
                <x-input-error :messages="$errors->get('contactPhone')" class="mt-2" />
            </div>
        </div>

        <div>
            <x-input-label for="description" :value="__('Description')" />
            <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-md border-gray-700 bg-gray-900 text-gray-300 focus:border-indigo-600 focus:ring-indigo-600">{{ old('description') }}</textarea>
            <x-input-error :messages="$errors->get('description')" class="mt-2" />
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <x-input-label for="feesMin" :value="__('Fees min')" />
                <x-text-input id="feesMin" class="block mt-1 w-full" type="number" name="feesMin" :value="old('feesMin')" />
                <x-input-error :messages="$errors->get('feesMin')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="feesMax" :value="__('Fees max')" />
                <x-text-input id="feesMax" class="block mt-1 w-full" type="number" name="feesMax" :value="old('feesMax')" />
                <x-input-error :messages="$errors->get('feesMax')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="currency" :value="__('Currency')" />
                <x-text-input id="currency" class="block mt-1 w-full uppercase" name="currency" :value="old('currency', 'SAR')" maxlength="3" required />
                <x-input-error :messages="$errors->get('currency')" class="mt-2" />
            </div>
        </div>

        <div>
            <x-input-label for="feePeriod" :value="__('Fee period')" />
            <select id="feePeriod" name="feePeriod" class="mt-1 block w-full rounded-md border-gray-700 bg-gray-900 text-gray-300 focus:border-indigo-600 focus:ring-indigo-600">
                <option value="yearly" @selected(old('feePeriod', 'yearly') === 'yearly')>Yearly</option>
                <option value="semester" @selected(old('feePeriod') === 'semester')>Semester</option>
            </select>
            <x-input-error :messages="$errors->get('feePeriod')" class="mt-2" />
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <x-input-label for="curriculumIds" :value="__('Curricula')" />
                <div class="mt-1 max-h-56 space-y-2 overflow-y-auto rounded-2xl border border-slate-700/80 bg-slate-900/45 p-3">
                    @foreach($curricula as $curriculum)
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-200 transition hover:bg-slate-800">
                            <input
                                type="checkbox"
                                name="curriculumIds[]"
                                value="{{ $curriculum->id }}"
                                @checked(in_array($curriculum->id, old('curriculumIds', [])))
                                class="rounded border-slate-600 bg-slate-950 text-cyan-400 focus:ring-cyan-300/40"
                            >
                            <span>{{ $curriculum->name }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="mt-2 text-xs text-slate-500">Choose all curricula that apply.</p>
            </div>

            <div>
                <x-input-label for="activityIds" :value="__('Activities')" />
                <div class="mt-1 max-h-56 space-y-2 overflow-y-auto rounded-2xl border border-slate-700/80 bg-slate-900/45 p-3">
                    @foreach($activities as $activity)
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-200 transition hover:bg-slate-800">
                            <input
                                type="checkbox"
                                name="activityIds[]"
                                value="{{ $activity->id }}"
                                @checked(in_array($activity->id, old('activityIds', [])))
                                class="rounded border-slate-600 bg-slate-950 text-cyan-400 focus:ring-cyan-300/40"
                            >
                            <span>{{ $activity->name }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="mt-2 text-xs text-slate-500">You can select more than one.</p>
            </div>

            <div>
                <x-input-label for="languageIds" :value="__('Languages')" />
                <div class="mt-1 max-h-56 space-y-2 overflow-y-auto rounded-2xl border border-slate-700/80 bg-slate-900/45 p-3">
                    @foreach($languages as $language)
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-200 transition hover:bg-slate-800">
                            <input
                                type="checkbox"
                                name="languageIds[]"
                                value="{{ $language->id }}"
                                @checked(in_array($language->id, old('languageIds', [])))
                                class="rounded border-slate-600 bg-slate-950 text-cyan-400 focus:ring-cyan-300/40"
                            >
                            <span>{{ $language->name }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="mt-2 text-xs text-slate-500">Select every language offered.</p>
            </div>
        </div>

        <div>
            <x-input-label for="proofText" :value="__('Proof text')" />
            <textarea id="proofText" name="proofText" rows="5" class="mt-1 block w-full rounded-md border-gray-700 bg-gray-900 text-gray-300 focus:border-indigo-600 focus:ring-indigo-600" required>{{ old('proofText') }}</textarea>
            <x-input-error :messages="$errors->get('proofText')" class="mt-2" />
        </div>

        <div class="flex justify-end">
            <x-primary-button>{{ __('Submit application') }}</x-primary-button>
        </div>
    </form>
</x-guest-layout>
