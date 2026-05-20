@extends('layouts.app')

@section('title', 'AI School Search')

@section('content')
    @php
        $selectedCurricula = $filters['curriculumIds'] ?? [];
        $selectedActivities = $filters['activityIds'] ?? [];
        $selectedLanguages = $filters['languageIds'] ?? [];
        $promptChips = [
            'British curriculum under 40,000 SAR',
            'International schools in Jeddah',
            'Strong STEM programs',
            'Affordable schools with Arabic and English',
        ];
    @endphp

    <section class="page-section">
        <div class="ui-container-wide">
            <div class="section-header">
                <div>
                    <p class="page-kicker">Dense Semantic Retrieval</p>
                    <h1 class="page-title mt-3">Find schools by meaning</h1>
                    <p class="page-subtitle">
                        Apply exact metadata filters first, then rank the remaining schools by pgvector cosine similarity.
                    </p>
                </div>

                <a href="{{ route('schools.index') }}" class="btn-secondary w-full md:w-auto">
                    Browse directory
                </a>
            </div>

            @if($errors->any())
                <div class="mb-5 rounded-2xl border border-rose-300/35 bg-rose-500/10 p-4 text-rose-100">
                    <h2 class="font-display text-lg font-semibold">Search needs a quick fix</h2>
                    <ul class="mt-3 list-disc space-y-1 pl-5 text-sm leading-6 text-rose-100/85">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="GET" action="{{ route('search.index') }}" class="glass-card ai-search-shell rounded-2xl p-4">
                <div class="grid gap-5">
                    <div>
                        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                            <label for="query" class="block text-sm font-semibold text-slate-200">Ask SchoolSense AI</label>
                            <span class="rounded-full border border-cyan-300/20 bg-cyan-300/10 px-2 py-0.5 text-[0.7rem] font-semibold uppercase tracking-[0.13em] text-cyan-100">Semantic</span>
                        </div>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-4 flex items-center">
                                <svg class="h-5 w-5 text-cyan-300/80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.2-5.2m1.7-5.05a6.75 6.75 0 1 1-13.5 0 6.75 6.75 0 0 1 13.5 0Z" />
                                </svg>
                            </div>
                            <input
                                id="query"
                                type="search"
                                name="query"
                                value="{{ old('query', $filters['query'] ?? '') }}"
                                placeholder="I want a British STEM-focused school in Jeddah under 40k"
                                class="field-shell ai-search-input w-full pl-12 pr-28 placeholder-slate-500"
                            >
                            <button type="submit" class="btn-primary absolute right-1.5 top-1/2 min-h-0 -translate-y-1/2 px-3">Search</button>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach($promptChips as $prompt)
                                <a href="{{ route('search.index', ['query' => $prompt]) }}"
                                   class="prompt-chip">
                                    {{ $prompt }}
                                </a>
                            @endforeach
                        </div>
                        @error('query')
                            <p class="mt-2 text-sm text-rose-200">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label for="city" class="mb-2 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">City</label>
                            <input
                                id="city"
                                type="text"
                                name="city"
                                value="{{ old('city', $filters['city'] ?? '') }}"
                                placeholder="Jeddah"
                                class="field-shell w-full placeholder-slate-500"
                            >
                            @error('city')
                                <p class="mt-2 text-sm text-rose-200">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="feesMin" class="mb-2 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Min fees</label>
                            <input
                                id="feesMin"
                                type="number"
                                min="0"
                                max="2147483647"
                                name="feesMin"
                                value="{{ old('feesMin', $filters['feesMin'] ?? '') }}"
                                placeholder="10000"
                                class="field-shell w-full placeholder-slate-500"
                            >
                            @error('feesMin')
                                <p class="mt-2 text-sm text-rose-200">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="feesMax" class="mb-2 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Max fees</label>
                            <input
                                id="feesMax"
                                type="number"
                                min="0"
                                max="2147483647"
                                name="feesMax"
                                value="{{ old('feesMax', $filters['feesMax'] ?? '') }}"
                                placeholder="40000"
                                class="field-shell w-full placeholder-slate-500"
                            >
                            @error('feesMax')
                                <p class="mt-2 text-sm text-rose-200">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                    <div class="grid gap-4 lg:grid-cols-3">
                        <div class="rounded-xl border border-slate-700/70 bg-slate-900/35 p-3">
                            <p class="mb-3 text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Curriculum</p>
                            <div class="grid max-h-40 gap-2 overflow-y-auto pr-1">
                                @foreach($curricula as $curriculum)
                                    <label class="flex items-center gap-2.5 rounded-lg border border-slate-700/70 bg-slate-950/20 px-3 py-2 text-sm text-slate-200">
                                        <input
                                            type="checkbox"
                                            name="curriculumIds[]"
                                            value="{{ $curriculum->id }}"
                                            @checked(in_array($curriculum->id, $selectedCurricula, true))
                                            class="rounded border-slate-600 bg-slate-900 text-cyan-400 focus:ring-cyan-300/30"
                                        >
                                        <span>{{ $curriculum->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('curriculumIds')
                                <p class="mt-2 text-sm text-rose-200">{{ $message }}</p>
                            @enderror
                            @error('curriculumIds.*')
                                <p class="mt-2 text-sm text-rose-200">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="rounded-xl border border-slate-700/70 bg-slate-900/35 p-3">
                            <p class="mb-3 text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Activities</p>
                            <div class="grid max-h-40 gap-2 overflow-y-auto pr-1">
                                @foreach($activities as $activity)
                                    <label class="flex items-center gap-2.5 rounded-lg border border-slate-700/70 bg-slate-950/20 px-3 py-2 text-sm text-slate-200">
                                        <input
                                            type="checkbox"
                                            name="activityIds[]"
                                            value="{{ $activity->id }}"
                                            @checked(in_array($activity->id, $selectedActivities, true))
                                            class="rounded border-slate-600 bg-slate-900 text-cyan-400 focus:ring-cyan-300/30"
                                        >
                                        <span>{{ $activity->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('activityIds')
                                <p class="mt-2 text-sm text-rose-200">{{ $message }}</p>
                            @enderror
                            @error('activityIds.*')
                                <p class="mt-2 text-sm text-rose-200">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="rounded-xl border border-slate-700/70 bg-slate-900/35 p-3">
                            <p class="mb-3 text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Languages</p>
                            <div class="grid max-h-40 gap-2 overflow-y-auto pr-1">
                                @foreach($languages as $language)
                                    <label class="flex items-center gap-2.5 rounded-lg border border-slate-700/70 bg-slate-950/20 px-3 py-2 text-sm text-slate-200">
                                        <input
                                            type="checkbox"
                                            name="languageIds[]"
                                            value="{{ $language->id }}"
                                            @checked(in_array($language->id, $selectedLanguages, true))
                                            class="rounded border-slate-600 bg-slate-900 text-cyan-400 focus:ring-cyan-300/30"
                                        >
                                        <span>{{ $language->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('languageIds')
                                <p class="mt-2 text-sm text-rose-200">{{ $message }}</p>
                            @enderror
                            @error('languageIds.*')
                                <p class="mt-2 text-sm text-rose-200">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-slate-700/70 pt-4 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-slate-400">
                        Exact filters reduce the candidate set first. Results below the relevance cutoff are hidden.
                        </p>
                        <div class="flex gap-3">
                            <a href="{{ route('search.index') }}" class="btn-secondary">Reset</a>
                            <button type="submit" class="btn-primary">Apply search</button>
                        </div>
                    </div>
                </div>
            </form>

            <div class="mt-6">
                @if($searchError)
                    <div class="rounded-2xl border border-rose-300/35 bg-rose-500/10 p-5 text-center text-rose-100">
                        <h2 class="font-display text-2xl font-semibold">AI search is unavailable</h2>
                        <p class="mx-auto mt-3 max-w-2xl text-sm leading-6 text-rose-100/80">
                            {{ $searchError }}
                        </p>
                    </div>
                @elseif(! $errors->any() && ($filters['query'] ?? null) && count($results) === 0)
                    <div class="glass-card rounded-2xl p-5 text-center">
                        <h2 class="font-display text-2xl font-semibold text-slate-100">No matching schools found</h2>
                        <p class="mx-auto mt-3 max-w-2xl text-sm leading-6 text-slate-400">
                            Try a broader phrase or remove a metadata filter. Some filter options may not be linked to embedded schools yet. If the database has schools but none appear here, generate embeddings with
                            <code class="rounded-lg border border-slate-700 bg-slate-900/70 px-2 py-1 text-slate-200">php artisan schools:embed</code>.
                        </p>
                    </div>
                @elseif(! ($filters['query'] ?? null))
                @elseif(! $errors->any())
                    @if(($explanationMeta['status'] ?? null) === 'fallback' && !empty($explanationMeta['message']))
                        <div class="mb-5 rounded-2xl border border-amber-300/30 bg-amber-400/10 p-4 text-sm leading-6 text-amber-100">
                            {{ $explanationMeta['message'] }}
                        </div>
                    @endif

                    <div class="mb-5 flex items-center justify-between gap-4">
                        <p class="text-sm text-slate-400">
                            Showing <span class="font-semibold text-slate-100">{{ count($results) }}</span> semantically ranked matches
                        </p>
                    </div>

                    <div class="grid gap-4">
                        @foreach($results as $school)
                            @php
                                $ai = $explanations[$school->id] ?? null;
                                $isFavorited = auth()->check()
                                    ? auth()->user()->favorites()->where('schoolId', $school->id)->exists()
                                    : false;
                            @endphp

                            <article class="glass-card recommendation-card transition-card card-glow rounded-xl p-4">
                                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start gap-3">
                                            <div class="school-avatar">
                                                {{ strtoupper(substr($school->name, 0, 1)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <h2 class="font-display text-lg font-semibold leading-snug text-slate-50">
                                                    {{ $school->name }}
                                                </h2>

                                                <p class="mt-1 text-sm text-slate-400">
                                                    {{ $school->city }}{{ $school->country ? ', ' . $school->country : '' }}
                                                </p>

                                                <div class="mt-2 flex flex-wrap gap-1.5">
                                                    @if($school->feesMin || $school->feesMax)
                                                        <span class="meta-pill">{{ $school->currency }} {{ $school->feesMin ? number_format($school->feesMin) : '0' }}{{ $school->feesMax ? ' - ' . number_format($school->feesMax) : '+' }}</span>
                                                    @endif
                                                    @if(!empty($school->city))
                                                        <span class="meta-pill">{{ $school->city }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        @if($school->description)
                                            <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-300 line-clamp-2">
                                                {{ $school->description }}
                                            </p>
                                        @endif

                                        @if($ai)
                                            <div class="mt-3 rounded-xl border border-cyan-300/25 bg-cyan-300/10 p-3">
                                                <div class="text-xs font-semibold uppercase tracking-[0.16em] text-cyan-200/80">
                                                    AI Match Explanation
                                                </div>

                                                @if(!empty($ai['reason']))
                                                    <p class="mt-2 text-sm leading-6 text-slate-200">
                                                        {{ $ai['reason'] }}
                                                    </p>
                                                @endif

                                                @if(!empty($ai['caution']))
                                                    <p class="mt-2 text-xs leading-5 text-amber-200">
                                                        Note: {{ $ai['caution'] }}
                                                    </p>
                                                @endif
                                            </div>
                                        @endif

                                        <div class="mt-3 flex flex-wrap gap-2">
                                            <a href="{{ route('schools.show', ['school' => $school->id, 'from' => request()->fullUrl()]) }}" class="btn-primary">View</a>
                                            <a href="{{ route('compare.index', ['schoolAId' => $school->id]) }}" class="btn-secondary">Compare</a>
                                            @auth
                                                @if(auth()->user()->role !== 'school_manager')
                                                    <form method="POST" action="{{ $isFavorited ? route('favorites.destroy', $school->id) : route('favorites.store', $school->id) }}">
                                                        @csrf
                                                        @if($isFavorited)
                                                            @method('DELETE')
                                                        @endif
                                                        <button type="submit" class="{{ $isFavorited ? 'btn-secondary' : 'btn-secondary' }}">
                                                            {{ $isFavorited ? 'Saved' : 'Save' }}
                                                        </button>
                                                    </form>
                                                @endif
                                            @endauth
                                        </div>
                                    </div>

                                    <div class="w-full shrink-0 rounded-xl border border-cyan-300/20 bg-cyan-300/10 px-3 py-2.5 text-left md:w-36 md:text-right">
                                        <div class="text-xs font-semibold uppercase tracking-[0.16em] text-cyan-200/80">Relevance</div>
                                        <div class="mt-1 font-display text-xl font-bold text-cyan-100">
                                            {{ number_format($school->similarity * 100, 1) }}%
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
