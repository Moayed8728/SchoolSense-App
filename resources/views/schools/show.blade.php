@extends('layouts.app')

@section('title', $school->name)
@section('meta_description', Str::limit($school->description, 160))

@section('content')
    @php
        $curricula  = $school->curricula  ?? collect();
        $activities = $school->activities ?? collect();
        $languages  = $school->languages  ?? collect();
        $hasMin = isset($school->feesMin) && $school->feesMin !== null;
        $hasMax = isset($school->feesMax) && $school->feesMax !== null;
        $currency = $school->currency ?? '';
        $period = $school->feePeriod ?? 'year';
        $isAdmin = auth()->check() && auth()->user()->role === 'admin';
        $contactStatus = $school->contactEmail && $school->contactPhone
            ? 'Complete'
            : (($school->contactEmail || $school->contactPhone) ? 'Partial' : 'Missing');
        $contactStatusClass = [
            'Complete' => 'status-approved',
            'Partial' => 'status-pending',
            'Missing' => 'status-rejected',
        ][$contactStatus];
        $latestContactExtraction = $isAdmin
            ? $school->contactExtractions()->latest()->first()
            : null;
    @endphp

    <!-- Back button + breadcrumb -->
    <div class="pt-16 pb-0 px-6">
        <div class="max-w-6xl mx-auto">
            <a href="{{ route('schools.index') }}"
               class="btn-secondary group mb-8">
                <svg class="w-4 h-4 text-cyan-300 group-hover:-translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Schools
            </a>
        </div>
    </div>

    <!-- Hero header -->
    <section class="px-6 pb-12">
        <div class="max-w-6xl mx-auto">
            <div class="panel-raised rounded-3xl p-8 md:p-10 relative overflow-hidden">
                <div class="relative grid gap-8 lg:grid-cols-[minmax(0,1fr)_18rem] lg:items-start">
                    <div class="flex items-start gap-5 min-w-0">
                        <!-- School avatar/initial -->
                        <div class="w-16 h-16 rounded-2xl bg-cyan-300 flex items-center justify-center text-slate-950 font-display font-bold text-2xl flex-shrink-0 shadow-lg shadow-cyan-950/20">
                            {{ strtoupper(substr($school->name, 0, 1)) }}
                        </div>

                        <div class="min-w-0">
                            <div class="flex items-center gap-3 mb-1 flex-wrap">
                                <h1 class="font-display font-bold text-2xl md:text-3xl text-slate-100 leading-snug">
                                    {{ $school->name }}
                                </h1>
                                <span class="status-chip status-approved">Verified</span>
                            </div>

                            <p class="flex items-center gap-1.5 text-slate-400 text-sm mb-3">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ $school->city }}@if($school->country), {{ $school->country }}@endif
                            </p>

                            <!-- Quick badges row -->
                            <div class="flex flex-wrap gap-1.5 mb-5">
                                @foreach($curricula->take(3) as $c)
                                    <x-badge color="indigo">{{ $c->name ?? $c }}</x-badge>
                                @endforeach
                                @foreach($languages->take(2) as $l)
                                    <x-badge color="purple">{{ $l->name ?? $l }}</x-badge>
                                @endforeach
                            </div>

                            <p class="max-w-2xl text-sm leading-relaxed text-slate-300">
                                {{ Str::limit($school->description ?? 'Detailed information is still being verified by school representatives and admins.', 180) }}
                            </p>
                        </div>
                    </div>

                    <!-- CTA buttons -->
                    <div class="rail-card rounded-2xl p-4">
                        <p class="mb-3 text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">School Actions</p>
                        <div class="grid gap-3">
                        @auth
                            @if($school->websiteUrl)
                                <a href="{{ $school->websiteUrl }}" target="_blank" rel="noopener noreferrer"
                                   class="btn-secondary w-full py-3">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                    Visit Website
                                </a>
                            @endif
                            @if($school->contactPageUrl)
                                <a href="{{ $school->contactPageUrl }}" target="_blank" rel="noopener noreferrer"
                                   class="btn-secondary w-full py-3">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                    Contact School
                                </a>
                            @endif

                            @if(auth()->user()->role !== 'school_manager')
                                @if($isFavorited)
                                    <form method="POST" action="{{ route('favorites.destroy', $school) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl border border-amber-300/40 bg-amber-400/10 px-4 py-3 text-sm font-semibold text-amber-200 transition-all duration-200 hover:border-amber-300/70 hover:bg-amber-400/15">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.75.75 0 011.04 0l2.12 2.024 2.94.427a.75.75 0 01.416 1.279l-2.127 2.19.502 3.09a.75.75 0 01-1.088.79L12 12.347l-2.263 1.182a.75.75 0 01-1.088-.79l.502-3.09-2.127-2.19a.75.75 0 01.416-1.28l2.94-.426 2.12-2.024z"/>
                                            </svg>
                                            Saved
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('favorites.store', $school) }}">
                                        @csrf
                                        <button type="submit"
                                                class="btn-primary w-full py-3">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.75.75 0 011.04 0l2.12 2.024 2.94.427a.75.75 0 01.416 1.279l-2.127 2.19.502 3.09a.75.75 0 01-1.088.79L12 12.347l-2.263 1.182a.75.75 0 01-1.088-.79l.502-3.09-2.127-2.19a.75.75 0 01.416-1.28l2.94-.426 2.12-2.024z"/>
                                            </svg>
                                            Save to Favorites
                                        </button>
                                    </form>
                                @endif
                            @endif
                        @else
                            <a href="{{ route('login') }}"
                               class="btn-secondary w-full py-3">
                                Sign in to Save
                            </a>
                        @endauth
                        </div>
                    </div>
                </div>

                <!-- Gradient divider -->
                <div class="mt-8 h-px w-full bg-slate-700/70"></div>

                <!-- Quick stats row -->
                <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="metric-card rounded-2xl">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Annual Fees</p>
                        <p class="text-base font-bold text-slate-100 leading-snug">
                            @if($hasMin || $hasMax)
                                @if($hasMin && $hasMax)
                                    {{ $currency }} {{ number_format($school->feesMin) }} - {{ number_format($school->feesMax) }}
                                @elseif($hasMin)
                                    From {{ $currency }} {{ number_format($school->feesMin) }}
                                @else
                                    Up to {{ $currency }} {{ number_format($school->feesMax) }}
                                @endif
                            @else
                                <span class="text-slate-500 font-normal italic">Not provided yet</span>
                            @endif
                        </p>
                        <p class="mt-1 text-xs text-slate-500">Per {{ $period }}</p>
                    </div>

                    <div class="metric-card rounded-2xl">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Curricula</p>
                        <p class="text-2xl font-bold text-slate-100">{{ $curricula->count() ?: '—' }}</p>
                        <p class="mt-1 text-xs text-slate-500">Programs listed</p>
                    </div>

                    <div class="metric-card rounded-2xl">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Languages</p>
                        <p class="text-2xl font-bold text-slate-100">{{ $languages->count() ?: '—' }}</p>
                        <p class="mt-1 text-xs text-slate-500">Teaching languages</p>
                    </div>

                    <div class="metric-card rounded-2xl">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Activities</p>
                        <p class="text-2xl font-bold text-slate-100">{{ $activities->count() ?: '—' }}</p>
                        <p class="mt-1 text-xs text-slate-500">Available options</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content: two column layout -->
    <section class="px-6 pb-20">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- LEFT COLUMN (2/3) -->
                <div class="lg:col-span-2 flex flex-col gap-6">

                    <!-- About / Description -->
                    @if($school->description)
                    <x-glass-card title="About This School">
                        <x-slot name="icon">
                            <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </x-slot>
                        <div class="prose prose-sm prose-invert max-w-none">
                            <p class="text-slate-300 leading-relaxed text-[0.9rem]">{{ $school->description }}</p>
                        </div>
                    </x-glass-card>
                    @endif

                    <!-- Curricula & Programs -->
                    @if($curricula->count())
                    <x-glass-card title="Curricula & Programs">
                        <x-slot name="icon">
                            <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </x-slot>
                        <div class="flex flex-wrap gap-2">
                            @foreach($curricula as $curriculum)
                                <div class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-sm text-indigo-300 font-medium">
                                    <svg class="w-3.5 h-3.5 text-indigo-400/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    {{ $curriculum->name ?? $curriculum }}
                                </div>
                            @endforeach
                        </div>
                    </x-glass-card>
                    @endif

                    <!-- Activities -->
                    @if($activities->count())
                    <x-glass-card title="Extracurricular Activities">
                        <x-slot name="icon">
                            <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </x-slot>
                        <div class="flex flex-wrap gap-2">
                            @foreach($activities as $activity)
                                <div class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-sm text-emerald-300 font-medium">
                                    {{ $activity->name ?? $activity }}
                                </div>
                            @endforeach
                        </div>
                    </x-glass-card>
                    @endif

                    <!-- Fee Breakdown Table -->
                    <x-glass-card title="Fee Breakdown">
                        <x-slot name="icon">
                            <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </x-slot>

                        @if($hasMin || $hasMax || ($school->feeBands ?? collect())->count())
                            <div class="overflow-hidden rounded-xl border border-slate-700/60">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b border-slate-700/60">
                                            <th class="text-left px-4 py-3 text-xs text-slate-500 uppercase tracking-wider font-medium bg-slate-800/40">Type</th>
                                            <th class="text-left px-4 py-3 text-xs text-slate-500 uppercase tracking-wider font-medium bg-slate-800/40">Amount</th>
                                            <th class="text-left px-4 py-3 text-xs text-slate-500 uppercase tracking-wider font-medium bg-slate-800/40">Period</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-700/40">
                                        @if($hasMin)
                                            <tr class="hover:bg-slate-700/20 transition-colors">
                                                <td class="px-4 py-3 text-slate-300">Minimum Fees</td>
                                                <td class="px-4 py-3">
                                                    <span class="font-semibold text-slate-100">{{ $currency }} {{ number_format($school->feesMin) }}</span>
                                                </td>
                                                <td class="px-4 py-3 text-slate-400 capitalize">{{ $period }}</td>
                                            </tr>
                                        @endif
                                        @if($hasMax)
                                            <tr class="hover:bg-slate-700/20 transition-colors">
                                                <td class="px-4 py-3 text-slate-300">Maximum Fees</td>
                                                <td class="px-4 py-3">
                                                    <span class="font-semibold text-slate-100">{{ $currency }} {{ number_format($school->feesMax) }}</span>
                                                </td>
                                                <td class="px-4 py-3 text-slate-400 capitalize">{{ $period }}</td>
                                            </tr>
                                        @endif

                                        @if(isset($school->feeBands) && $school->feeBands->count())
                                            @foreach($school->feeBands as $band)
                                                <tr class="hover:bg-slate-700/20 transition-colors">
                                                    <td class="px-4 py-3 text-slate-300">{{ $band->label ?? 'Fee Band' }}</td>
                                                    <td class="px-4 py-3">
                                                        <span class="font-semibold text-slate-100">{{ $currency }} {{ number_format($band->amount ?? 0) }}</span>
                                                    </td>
                                                    <td class="px-4 py-3 text-slate-400 capitalize">{{ $band->period ?? $period }}</td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            <p class="mt-3 text-xs text-slate-500">* Fees are indicative and may vary. Contact the school for exact figures.</p>
                        @else
                            <div class="flex flex-col items-center justify-center py-10 text-center">
                                <svg class="w-10 h-10 text-slate-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-sm text-slate-500">Fee information not available</p>
                                @if($school->websiteUrl)
                                    <a href="{{ $school->websiteUrl }}" target="_blank" rel="noopener noreferrer"
                                       class="mt-3 text-xs text-indigo-400 hover:text-indigo-300 transition-colors">
                                        Check school website →
                                    </a>
                                @endif
                            </div>
                        @endif
                    </x-glass-card>

                </div>

                <!-- RIGHT COLUMN (1/3) -->
                <div class="flex flex-col gap-6">

                    <!-- Contact Information -->
                    <x-glass-card title="Contact Information">
                        <x-slot name="icon">
                            <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </x-slot>
                        <div class="space-y-4">
                            @if($school->contactEmail)
                                <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-700/30 border border-slate-700/50">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-500/15 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs text-slate-500 mb-0.5">Email</p>
                                        <a href="mailto:{{ $school->contactEmail }}"
                                           class="text-sm text-indigo-400 hover:text-indigo-300 transition-colors truncate block">
                                            {{ $school->contactEmail }}
                                        </a>
                                    </div>
                                </div>
                            @endif

                            @if($school->contactPhone)
                                <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-700/30 border border-slate-700/50">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-500/15 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs text-slate-500 mb-0.5">Phone</p>
                                        <a href="tel:{{ $school->contactPhone }}"
                                           class="text-sm text-emerald-400 hover:text-emerald-300 transition-colors block">
                                            {{ $school->contactPhone }}
                                        </a>
                                    </div>
                                </div>
                            @endif

                            @if($school->websiteUrl)
                                <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-700/30 border border-slate-700/50">
                                    <div class="w-8 h-8 rounded-lg bg-purple-500/15 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs text-slate-500 mb-0.5">Website</p>
                                        <a href="{{ $school->websiteUrl }}" target="_blank" rel="noopener noreferrer"
                                           class="text-sm text-purple-400 hover:text-purple-300 transition-colors truncate block">
                                            {{ parse_url($school->websiteUrl, PHP_URL_HOST) ?? $school->websiteUrl }}
                                        </a>
                                    </div>
                                </div>
                            @endif

                            @if(!$school->contactEmail && !$school->contactPhone && !$school->websiteUrl)
                                <p class="text-sm text-slate-500 text-center py-4 italic">Not provided yet.</p>
                            @endif
                        </div>
                    </x-glass-card>

                    @if($isAdmin)
                        <section class="rounded-2xl border border-cyan-300/30 bg-slate-900/80 p-6 shadow-xl shadow-cyan-950/10">
                            <div class="mb-6 flex items-start gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-cyan-300/35 bg-cyan-400/10">
                                    <svg class="h-5 w-5 text-cyan-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="page-kicker">Admin tool</p>
                                    <h3 class="mt-2 font-display text-xl font-semibold text-slate-50">Contact enrichment</h3>
                                    <p class="mt-2 text-sm leading-6 text-slate-400">Fetch contact details from the school website and save the best email, phone, and contact page found.</p>
                                </div>
                            </div>

                            @if(session('contact_fetch_result') || session('contact_fetch_error'))
                                <div class="mb-5 rounded-xl border px-4 py-3 text-sm leading-6 {{ session('contact_fetch_error') ? 'border-rose-300/35 bg-rose-500/10 text-rose-100' : 'border-emerald-300/35 bg-emerald-500/10 text-emerald-100' }}">
                                    {!! nl2br(e(session('contact_fetch_error') ?? session('contact_fetch_result'))) !!}
                                </div>
                            @endif

                            <div class="grid gap-4">
                                <div class="rounded-xl border border-slate-700/70 bg-slate-950/30 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Contact status</p>
                                    <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                                        <span class="status-chip {{ $contactStatusClass }}">{{ $contactStatus }}</span>
                                        <form method="POST" action="{{ route('admin.schools.fetch-contacts', $school) }}" x-data="{ loading: false }" @submit="loading = true">
                                            @csrf
                                            <button type="submit" class="btn-primary min-w-44" :disabled="loading" :class="loading ? 'cursor-wait opacity-75' : ''">
                                                <span x-show="! loading">Fetch Contact Info</span>
                                                <span x-show="loading" x-cloak class="inline-flex items-center gap-2">
                                                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                                    </svg>
                                                    Fetching...
                                                </span>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-slate-700/70 bg-slate-950/30 p-4 text-sm text-slate-300">
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Latest extraction</p>
                                    @if($latestContactExtraction)
                                        <p class="mt-3"><span class="text-slate-500">Emails:</span> {{ !empty($latestContactExtraction->foundEmails) ? implode(', ', $latestContactExtraction->foundEmails) : 'email not found' }}</p>
                                        <p class="mt-2"><span class="text-slate-500">Phones:</span> {{ !empty($latestContactExtraction->foundPhones) ? implode(', ', $latestContactExtraction->foundPhones) : 'phone number not found' }}</p>
                                        <p class="mt-2"><span class="text-slate-500">Created:</span> {{ $latestContactExtraction->created_at?->toDayDateTimeString() }}</p>
                                    @else
                                        <p class="mt-3 text-slate-500">No contact extraction has been run yet.</p>
                                    @endif
                                </div>
                            </div>
                        </section>
                    @endif

                    <!-- Languages -->
                    @if($languages->count())
                    <x-glass-card title="Languages">
                        <x-slot name="icon">
                            <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                            </svg>
                        </x-slot>
                        <div class="flex flex-wrap gap-2">
                            @foreach($languages as $language)
                                <div class="px-3 py-2 rounded-xl bg-purple-500/10 border border-purple-500/20 text-sm text-purple-300 font-medium">
                                    {{ $language->name ?? $language }}
                                </div>
                            @endforeach
                        </div>
                    </x-glass-card>
                    @endif

                    <!-- CTA card -->
                    <div class="glass-card rounded-xl p-6 relative overflow-hidden"
                         style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.15) 0%, rgba(124, 58, 237, 0.15) 100%); border-color: rgba(99, 102, 241, 0.3);">
                        <div class="absolute -top-6 -right-6 w-24 h-24 rounded-full"
                             style="background: radial-gradient(circle, rgba(99,102,241,0.3) 0%, transparent 70%); filter: blur(15px);"></div>
                        <div class="relative">
                            <div class="w-10 h-10 rounded-xl btn-gradient flex items-center justify-center mb-4">
                                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h4 class="font-display font-semibold text-slate-100 mb-2 text-sm">Have a question?</h4>
                            <p class="text-xs text-slate-400 leading-relaxed mb-4">Get in touch with {{ $school->name }} directly for admissions, fees, or any enquiries.</p>
                            @if($school->contactPageUrl)
                                <a href="{{ $school->contactPageUrl }}" target="_blank" rel="noopener noreferrer"
                                   class="btn-gradient text-white text-xs font-medium px-4 py-2.5 rounded-lg inline-flex items-center gap-1.5 w-full justify-center">
                                    Contact Admissions
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            @elseif($school->contactEmail)
                                <a href="mailto:{{ $school->contactEmail }}"
                                   class="btn-gradient text-white text-xs font-medium px-4 py-2.5 rounded-lg inline-flex items-center gap-1.5 w-full justify-center">
                                    Send Email
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

@endsection
