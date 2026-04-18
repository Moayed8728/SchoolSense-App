@props([
    'school',
    'favoriteControls' => false,
])

@php
    $curricula = $school->curricula ?? collect();
    $activities = $school->activities ?? collect();
    $languages = $school->languages ?? collect();

    $hasMin = isset($school->feesMin) && $school->feesMin !== null;
    $hasMax = isset($school->feesMax) && $school->feesMax !== null;
    $currency = $school->currency ?? '';
    $period = $school->feePeriod ?? 'year';
@endphp

<article class="glass-card rounded-2xl p-6 flex h-full flex-col gap-5 transition-card card-glow group text-slate-200">

    <!-- Top row: name + arrow -->
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <a href="{{ route('schools.show', $school) }}"
               class="block font-display font-semibold text-lg text-slate-50 leading-snug group-hover:gradient-text transition-all duration-300 line-clamp-2"
               aria-label="View {{ $school->name }}">
                {{ $school->name }}
            </a>
            <p class="flex items-center gap-1.5 mt-1 text-sm text-slate-300">
                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>{{ $school->city }}@if($school->country), {{ $school->country }}@endif</span>
            </p>
        </div>
        <a href="{{ route('schools.show', $school) }}"
           class="w-8 h-8 rounded-lg bg-slate-800/70 border border-slate-600/60 flex items-center justify-center flex-shrink-0 group-hover:bg-indigo-500/25 group-hover:border-indigo-500/60 transition-all duration-300"
           aria-label="View {{ $school->name }}">
            <svg class="w-4 h-4 text-slate-300 group-hover:text-indigo-300 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    <!-- Description snippet -->
    @if($school->description)
        <p class="text-sm text-slate-300 leading-relaxed line-clamp-3">{{ $school->description }}</p>
    @endif

    <div class="grid gap-3 sm:grid-cols-2">
        <div class="rounded-xl border border-slate-600/60 bg-slate-900/45 p-3">
            <div class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-slate-500">
                <svg class="w-3.5 h-3.5 text-slate-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Fees
            </div>
            <div class="mt-2 text-sm text-slate-200">
                @if($hasMin || $hasMax)
                    @if($hasMin && $hasMax)
                        {{ $currency }} {{ number_format($school->feesMin) }} - {{ number_format($school->feesMax) }}
                    @elseif($hasMin)
                        From {{ $currency }} {{ number_format($school->feesMin) }}
                    @else
                        Up to {{ $currency }} {{ number_format($school->feesMax) }}
                    @endif
                    <span class="text-slate-400">/ {{ $period }}</span>
                @else
                    <span class="text-slate-400 italic">Not provided yet</span>
                @endif
            </div>
        </div>

        <div class="rounded-xl border border-slate-600/60 bg-slate-900/45 p-3">
            <div class="flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-slate-500">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 3h7v7m0 0L10 21l-7-7 11-11z"/>
                </svg>
                Contact
            </div>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                @if($school->websiteUrl)
                    <a href="{{ $school->websiteUrl }}" target="_blank" rel="noopener noreferrer"
                       class="text-sm font-medium text-indigo-300 hover:text-indigo-200">
                        Website
                    </a>
                @endif

                @if($school->contactEmail)
                    <a href="mailto:{{ $school->contactEmail }}" class="text-sm font-medium text-indigo-300 hover:text-indigo-200">Email</a>
                @endif

                @if($school->contactPhone)
                    <a href="tel:{{ $school->contactPhone }}" class="text-sm font-medium text-indigo-300 hover:text-indigo-200">Phone</a>
                @endif

                @if($school->contactPageUrl)
                    <a href="{{ $school->contactPageUrl }}" target="_blank" rel="noopener noreferrer" class="text-sm font-medium text-indigo-300 hover:text-indigo-200">Contact page</a>
                @endif

                @if(!$school->websiteUrl && !$school->contactEmail && !$school->contactPhone && !$school->contactPageUrl)
                    <span class="text-sm italic text-slate-400">Not provided yet</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Divider -->
    @if($curricula->count() || $activities->count() || $languages->count())
        <div class="mt-auto space-y-3 border-t border-slate-700/60 pt-4">
            @if($curricula->count())
                <div>
                    <div class="mb-2 text-xs font-medium uppercase tracking-wider text-slate-500">Curricula</div>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($curricula->take(3) as $c)
                            <x-badge color="indigo">{{ $c->name ?? $c }}</x-badge>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($activities->count())
                <div>
                    <div class="mb-2 text-xs font-medium uppercase tracking-wider text-slate-500">Activities</div>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($activities->take(3) as $a)
                            <x-badge color="emerald">{{ $a->name ?? $a }}</x-badge>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($languages->count())
                <div>
                    <div class="mb-2 text-xs font-medium uppercase tracking-wider text-slate-500">Languages</div>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($languages->take(3) as $l)
                            <x-badge color="purple">{{ $l->name ?? $l }}</x-badge>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if($favoriteControls)
        <div class="{{ $curricula->count() || $activities->count() || $languages->count() ? '' : 'mt-auto ' }}flex flex-col gap-3 border-t border-slate-700/60 pt-4 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('schools.show', $school) }}"
               class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-600/70 bg-slate-900/45 px-4 py-2.5 text-sm font-semibold text-slate-100 transition-all duration-200 hover:border-indigo-400/70 hover:bg-slate-700/80">
                View details
                <svg class="w-4 h-4 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

            <form method="POST" action="{{ route('favorites.destroy', $school) }}">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-rose-300/35 bg-rose-400/10 px-4 py-2.5 text-sm font-semibold text-rose-200 transition-all duration-200 hover:border-rose-300/70 hover:bg-rose-400/15 sm:w-auto">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.75.75 0 011.04 0l2.12 2.024 2.94.427a.75.75 0 01.416 1.279l-2.127 2.19.502 3.09a.75.75 0 01-1.088.79L12 12.347l-2.263 1.182a.75.75 0 01-1.088-.79l.502-3.09-2.127-2.19a.75.75 0 01.416-1.28l2.94-.426 2.12-2.024z"/>
                    </svg>
                    Remove
                </button>
            </form>
        </div>
    @endif
</article>
