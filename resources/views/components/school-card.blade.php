@props(['school'])

@php
    $curricula = $school->curricula ?? collect();
    $activities = $school->activities ?? collect();
    $languages = $school->languages ?? collect();

    $hasMin = isset($school->feesMin) && $school->feesMin !== null;
    $hasMax = isset($school->feesMax) && $school->feesMax !== null;
    $currency = $school->currency ?? '';
    $period = $school->feePeriod ?? 'year';
@endphp

<a href="{{ route('schools.show', $school) }}"
   class="glass-card rounded-2xl p-6 flex flex-col gap-5 transition-card card-glow group text-slate-200"
   aria-label="View {{ $school->name }}">

    <!-- Top row: name + arrow -->
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <h2 class="font-display font-semibold text-lg text-slate-50 leading-snug group-hover:gradient-text transition-all duration-300 truncate">
                {{ $school->name }}
            </h2>
            <p class="flex items-center gap-1.5 mt-1 text-sm text-slate-300">
                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>{{ $school->city }}@if($school->country), {{ $school->country }}@endif</span>
            </p>
        </div>
        <div class="w-8 h-8 rounded-lg bg-slate-800/70 border border-slate-600/60 flex items-center justify-center flex-shrink-0 group-hover:bg-indigo-500/25 group-hover:border-indigo-500/60 transition-all duration-300">
            <svg class="w-4 h-4 text-slate-300 group-hover:text-indigo-300 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </div>
    </div>

    <!-- Description snippet -->
    @if($school->description)
        <p class="text-sm text-slate-300 leading-relaxed line-clamp-2">{{ $school->description }}</p>
    @endif

    <!-- Fee info -->
    <div class="flex items-center gap-2">
        <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        @if($hasMin || $hasMax)
            <span class="text-sm text-slate-300">
                @if($hasMin && $hasMax)
                    {{ $currency }} {{ number_format($school->feesMin) }} – {{ number_format($school->feesMax) }}
                @elseif($hasMin)
                    From {{ $currency }} {{ number_format($school->feesMin) }}
                @else
                    Up to {{ $currency }} {{ number_format($school->feesMax) }}
                @endif
                <span class="text-slate-400">/ {{ $period }}</span>
            </span>
        @else
                <span class="text-sm text-slate-400 italic">Fees not provided yet</span>
        @endif
    </div>

    <!-- Actions row -->
    <div class="flex items-center gap-2 mt-2 flex-wrap">
        @if($school->websiteUrl)
            <a href="{{ $school->websiteUrl }}" target="_blank" rel="noopener noreferrer"
               class="btn-gradient text-white text-xs font-medium px-3 py-1 rounded-full inline-flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 3h7v7m0 0L10 21l-7-7 11-11z"/>
                </svg>
                Website
            </a>
        @endif

        @if(!$school->contactEmail && !$school->contactPhone && !$school->contactPageUrl)
            <span class="text-xs italic text-slate-400">Contact not provided yet</span>
        @endif
    </div>

    <!-- Divider -->
    @if($curricula->count() || $activities->count() || $languages->count())
        <div class="border-t border-slate-700/60"></div>

        <!-- Badges -->
        <div class="flex flex-wrap gap-1.5">
            @foreach($curricula->take(2) as $c)
                <x-badge color="indigo">{{ $c->name ?? $c }}</x-badge>
            @endforeach
            @foreach($activities->take(2) as $a)
                <x-badge color="emerald">{{ $a->name ?? $a }}</x-badge>
            @endforeach
            @foreach($languages->take(2) as $l)
                <x-badge color="purple">{{ $l->name ?? $l }}</x-badge>
            @endforeach
            @php
                $remaining = max(0, $curricula->count() - 2) + max(0, $activities->count() - 2) + max(0, $languages->count() - 2);
            @endphp
            @if($remaining > 0)
                <x-badge color="slate">+{{ $remaining }} more</x-badge>
            @endif
        </div>
    @endif
</a>
