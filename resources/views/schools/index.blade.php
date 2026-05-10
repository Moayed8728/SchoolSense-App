@extends('layouts.app')

@section('title', 'Discover Schools')

@section('content')
    @php
        $filters = $filters ?? [];
        $chipClass = 'px-4 py-1.5 text-xs font-medium rounded-full border transition-all duration-200';
        $activeChipClass = $chipClass . ' btn-gradient text-white border-transparent';
        $inactiveChipClass = $chipClass . ' glass-card text-slate-300 hover:text-slate-100 border-slate-700';
        $filterUrl = fn (array $overrides = [], array $remove = []) => route(
            'schools.index',
            array_filter(
                array_merge(request()->except(array_merge(['page'], $remove)), $overrides),
                fn ($value) => filled($value)
            )
        );
    @endphp

    <!-- Hero Section -->
    <section class="pt-10 pb-10 px-6">
        <div class="max-w-6xl mx-auto">
            <div class="glass-card rounded-3xl border border-slate-700/60 p-8 md:p-12">
                

                <!-- Heading -->
                <h1 class="font-display font-bold text-4xl md:text-5xl lg:text-6xl text-center leading-tight tracking-tight mb-6">
                    <span class="text-slate-500">Discover Schools</span><br>
                    <span class="gradient-text">near you</span>
                </h1>
                <p class="text-center text-slate-300 text-lg max-w-2xl mx-auto mb-10 leading-relaxed">
                    Explore schools with verified links. Detailed data (fees, programs, contact) will be added through school managers and admin verification.
                </p>

                <!-- Search bar -->
                <form method="GET" action="{{ route('schools.index') }}" class="max-w-2xl mx-auto">
                    @foreach(request()->except(['page', 'q']) as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <div class="relative">
                        <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>

                        <input
                            type="search"
                            name="q"
                            value="{{ $filters['q'] ?? '' }}"
                            placeholder="Search schools, curricula, languages…"
                            class="w-full glass-card rounded-xl pl-11 pr-28 py-3.5 text-sm text-slate-200 placeholder-slate-500 border-slate-700 focus:outline-none focus:border-indigo-500/60 focus:ring-1 focus:ring-indigo-500/30 transition-all duration-200"
                            aria-label="Search schools"
                        >
                        <button type="submit" class="absolute inset-y-1.5 right-1.5 rounded-lg btn-gradient px-4 text-sm font-semibold text-white">
                            Search
                        </button>
                    </div>
                    @error('q')
                        <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                    @enderror
                </form>

                <!-- Stats row -->
                @if($schools->total() > 0)
                    <div class="mt-10 flex items-center justify-center gap-8 text-center flex-wrap">
                        <div>
                            <div class="font-display font-bold text-2xl gradient-text">{{ $schools->total() }}</div>
                            <div class="text-xs text-slate-400 mt-0.5">Schools listed</div>
                        </div>
                        <div class="w-px h-8 bg-slate-700 hidden sm:block"></div>
                        <div>
                            <div class="font-display font-bold text-2xl text-slate-100">Worldwide</div>
                            <div class="text-xs text-slate-400 mt-0.5">Coverage</div>
                        </div>
                        <div class="w-px h-8 bg-slate-700 hidden sm:block"></div>
                        <div>
                            <div class="font-display font-bold text-2xl text-slate-100">AI</div>
                            <div class="text-xs text-slate-400 mt-0.5">Powered insights</div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </section>

    <!-- Filter bar (UI only for now) -->
    <section class="px-6 mt-4 mb-8">
        <div class="max-w-6xl mx-auto">
            <div class="flex items-center gap-3 flex-wrap">
                <span class="text-xs text-slate-300 font-medium uppercase tracking-wider">Filter:</span>
                <a href="{{ $filterUrl([], ['curriculum', 'activity', 'language']) }}"
                   class="{{ empty($filters['curriculum']) && empty($filters['activity']) && empty($filters['language']) ? $activeChipClass : $inactiveChipClass }}">
                    All Schools
                </a>
                <a href="{{ $filterUrl(['curriculum' => 'IB'], ['activity', 'language']) }}"
                   class="{{ ($filters['curriculum'] ?? '') === 'IB' ? $activeChipClass : $inactiveChipClass }}">
                    IB Curriculum
                </a>
                <a href="{{ $filterUrl(['curriculum' => 'British'], ['activity', 'language']) }}"
                   class="{{ ($filters['curriculum'] ?? '') === 'British' ? $activeChipClass : $inactiveChipClass }}">
                    British
                </a>
                <a href="{{ $filterUrl(['curriculum' => 'American'], ['activity', 'language']) }}"
                   class="{{ ($filters['curriculum'] ?? '') === 'American' ? $activeChipClass : $inactiveChipClass }}">
                    American
                </a>
                <a href="{{ $filterUrl(['language' => 'Arabic'], ['curriculum', 'activity']) }}"
                   class="{{ ($filters['language'] ?? '') === 'Arabic' ? $activeChipClass : $inactiveChipClass }}">
                    Arabic
                </a>
            </div>

            <form method="GET" action="{{ route('schools.index') }}" class="mt-4 grid gap-3 md:grid-cols-[1fr_1fr_1fr_auto]">
                <input type="hidden" name="q" value="{{ $filters['q'] ?? '' }}">
                <input type="hidden" name="curriculum" value="{{ $filters['curriculum'] ?? '' }}">
                <input type="hidden" name="activity" value="{{ $filters['activity'] ?? '' }}">
                <input type="hidden" name="language" value="{{ $filters['language'] ?? '' }}">

                <select name="city" class="glass-card rounded-xl border-slate-700 px-4 py-3 text-sm text-slate-200 focus:outline-none focus:border-indigo-500/60 bg-slate-950/60">
                    <option value="">Any city</option>
                    @foreach($cities as $city)
                        <option value="{{ $city }}" @selected(($filters['city'] ?? '') === $city)>{{ $city }}</option>
                    @endforeach
                </select>

                <input
                    type="number"
                    min="0"
                    name="feesMax"
                    value="{{ $filters['feesMax'] ?? '' }}"
                    placeholder="Max yearly fees"
                    class="glass-card rounded-xl border-slate-700 px-4 py-3 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500/60"
                >

                <select name="sort" class="glass-card rounded-xl border-slate-700 px-4 py-3 text-sm text-slate-200 focus:outline-none focus:border-indigo-500/60 bg-slate-950/60">
                    <option value="name" @selected(($filters['sort'] ?? 'name') === 'name')>Sort by name</option>
                    <option value="fees" @selected(($filters['sort'] ?? '') === 'fees')>Sort by fees</option>
                    <option value="newest" @selected(($filters['sort'] ?? '') === 'newest')>Newest first</option>
                </select>

                <div class="flex gap-2">
                    <button type="submit" class="btn-gradient rounded-xl px-5 py-3 text-sm font-semibold text-white">Apply</button>
                    @if(request()->hasAny(['q', 'city', 'curriculum', 'activity', 'language', 'feesMax', 'sort']))
                        <a href="{{ route('schools.index') }}" class="glass-card rounded-xl border border-slate-700 px-5 py-3 text-sm font-semibold text-slate-200">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </section>

    <!-- Schools Grid -->
    <section class="px-6 pb-24">
        <div class="max-w-6xl mx-auto">

            @if($schools->count() > 0)

                <div class="flex items-center justify-between mb-6">
                    <p class="text-sm text-slate-400">
                        Showing <span class="text-slate-200 font-medium">{{ $schools->count() }}</span>
                        of <span class="text-slate-200 font-medium">{{ $schools->total() }}</span> schools
                    </p>
                    @if(request()->hasAny(['q', 'city', 'curriculum', 'activity', 'language', 'feesMax']))
                        <a href="{{ route('schools.index') }}" class="text-xs font-semibold text-indigo-300 hover:text-indigo-200">Clear filters</a>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-7">
                    @foreach($schools as $school)
                        <x-school-card :school="$school" />
                    @endforeach
                </div>

                @if($schools->hasPages())
                    <div class="mt-12 flex justify-center">
                        {{ $schools->links() }}
                    </div>
                @endif

            @else
                    <div class="glass-card rounded-3xl border border-slate-700/60 p-12 md:p-16 text-center flex flex-col items-center justify-center">
                    <div class="w-10 h-10 rounded-xl bg-slate-800/80 border border-slate-700/80 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m9-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-display font-semibold text-2xl text-slate-100 mb-3">No schools found</h3>
                    <p class="text-slate-300 max-w-lg mx-auto leading-relaxed">
                        Try a broader search, remove a filter, or import more real school data.
                    </p>
                    @if(request()->hasAny(['q', 'city', 'curriculum', 'activity', 'language', 'feesMax', 'sort']))
                        <a href="{{ route('schools.index') }}" class="mt-6 btn-gradient rounded-xl px-5 py-3 text-sm font-semibold text-white">Reset filters</a>
                    @endif
                </div>
            @endif

        </div>
    </section>
@endsection
