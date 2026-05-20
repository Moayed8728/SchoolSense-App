@extends('layouts.app')

@section('title', 'Compare Schools')

@section('content')
    @php
        $selectedAId = old('schoolAId', $selectedSchools[0]->id ?? '');
        $selectedBId = old('schoolBId', $selectedSchools[1]->id ?? '');
    @endphp

    <section class="px-2 pb-16 pt-6 md:px-4">
        <div class="mx-auto max-w-7xl">
            <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="page-kicker">AI Comparison</p>
                    <h1 class="page-title mt-3">Compare two schools</h1>
                    <p class="page-subtitle">
                        Pick two schools to review side-by-side details and generate a grounded AI summary from the available data.
                    </p>
                </div>

                <a href="{{ route('search.index') }}" class="btn-secondary w-full md:w-auto">
                    Back to AI Search
                </a>
            </div>

            @if($errors->any())
                <div class="mb-6 rounded-3xl border border-rose-300/35 bg-rose-500/10 p-5 text-rose-100">
                    <h2 class="font-display text-lg font-semibold">Comparison needs a quick fix</h2>
                    <ul class="mt-3 list-disc space-y-1 pl-5 text-sm leading-6 text-rose-100/85">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('compare.compare') }}" class="glass-card rounded-2xl p-4 md:p-5">
                @csrf

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="schoolAId" class="mb-2 block text-sm font-semibold text-slate-200">First School</label>
                        <select id="schoolAId" name="schoolAId" class="field-shell w-full" required>
                            <option value="">Choose a school</option>
                            @foreach($schools as $school)
                                <option value="{{ $school->id }}" @selected($selectedAId === $school->id)>
                                    {{ $school->name }} - {{ $school->city }}
                                </option>
                            @endforeach
                        </select>
                        @error('schoolAId')
                            <p class="mt-2 text-sm text-rose-200">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="schoolBId" class="mb-2 block text-sm font-semibold text-slate-200">Second School</label>
                        <select id="schoolBId" name="schoolBId" class="field-shell w-full" required>
                            <option value="">Choose a school</option>
                            @foreach($schools as $school)
                                <option value="{{ $school->id }}" @selected($selectedBId === $school->id)>
                                    {{ $school->name }} - {{ $school->city }}
                                </option>
                            @endforeach
                        </select>
                        @error('schoolBId')
                            <p class="mt-2 text-sm text-rose-200">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-5 flex flex-col gap-3 border-t border-slate-700/70 pt-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-400">
                        The summary uses only the selected school records. Missing details stay marked as not specified.
                    </p>
                    <button class="btn-primary" type="submit">
                        Compare Schools
                    </button>
                </div>
            </form>

            @if($selectedSchools->count() === 2)
                @php
                    [$a, $b] = [$selectedSchools[0], $selectedSchools[1]];
                @endphp

                @if($summary)
                    <section class="mt-6 rounded-2xl border border-cyan-300/25 bg-cyan-300/10 p-4 md:p-5">
                        <div class="text-xs font-semibold uppercase tracking-[0.16em] text-cyan-200/80">
                            AI Comparison Summary
                        </div>

                        @if(($summary['status'] ?? null) === 'unavailable')
                            <p class="mt-3 max-w-4xl text-sm leading-6 text-amber-100">
                                {{ $summary['message'] }}
                            </p>
                        @endif

                        @if($summary['overview'])
                            <p class="mt-3 max-w-4xl text-sm leading-6 text-slate-200">{{ $summary['overview'] }}</p>
                        @endif

                        @if(($summary['status'] ?? null) === 'generated')
                            <div class="mt-5 grid gap-4 lg:grid-cols-3">
                                <div class="metric-card rounded-xl">
                                    <h3 class="text-sm font-semibold text-slate-100">{{ $a->name }} strengths</h3>
                                    <ul class="mt-3 list-disc space-y-2 pl-5 text-sm leading-6 text-slate-300">
                                        @forelse($summary['schoolAStrengths'] as $point)
                                            <li>{{ $point }}</li>
                                        @empty
                                            <li>No specific strengths identified from available data.</li>
                                        @endforelse
                                    </ul>
                                </div>

                                <div class="metric-card rounded-xl">
                                    <h3 class="text-sm font-semibold text-slate-100">{{ $b->name }} strengths</h3>
                                    <ul class="mt-3 list-disc space-y-2 pl-5 text-sm leading-6 text-slate-300">
                                        @forelse($summary['schoolBStrengths'] as $point)
                                            <li>{{ $point }}</li>
                                        @empty
                                            <li>No specific strengths identified from available data.</li>
                                        @endforelse
                                    </ul>
                                </div>

                                <div class="metric-card rounded-xl">
                                    <h3 class="text-sm font-semibold text-slate-100">Tradeoffs</h3>
                                    <ul class="mt-3 list-disc space-y-2 pl-5 text-sm leading-6 text-slate-300">
                                        @forelse($summary['tradeoffs'] as $point)
                                            <li>{{ $point }}</li>
                                        @empty
                                            <li>No major tradeoffs identified from available data.</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>

                            @if($summary['bestFit'])
                                <div class="mt-5 rounded-xl border border-slate-700/70 bg-slate-950/35 p-3 text-sm leading-6 text-slate-200">
                                    <span class="font-semibold text-cyan-100">Best fit:</span>
                                    {{ $summary['bestFit'] }}
                                </div>
                            @endif
                        @endif
                    </section>
                @endif

                <section class="mt-6 grid gap-5 lg:grid-cols-2">
                    @foreach($selectedSchools as $school)
                        <article class="glass-card rounded-2xl p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h2 class="font-display text-xl font-semibold leading-snug text-slate-50">{{ $school->name }}</h2>
                                    <p class="mt-2 text-sm text-slate-400">{{ $school->city }}, {{ $school->country }}</p>
                                </div>

                                <a href="{{ route('schools.show', $school) }}" class="btn-secondary shrink-0">
                                    View
                                </a>
                            </div>

                            <dl class="mt-5 grid gap-3 text-sm">
                                <div class="rounded-xl border border-slate-700/70 bg-slate-950/25 p-3">
                                    <dt class="font-semibold text-slate-100">Curricula</dt>
                                    <dd class="mt-2 leading-6 text-slate-300">
                                        {{ $school->curricula->pluck('name')->implode(', ') ?: 'Not specified' }}
                                    </dd>
                                </div>

                                <div class="rounded-xl border border-slate-700/70 bg-slate-950/25 p-3">
                                    <dt class="font-semibold text-slate-100">Activities</dt>
                                    <dd class="mt-2 leading-6 text-slate-300">
                                        {{ $school->activities->pluck('name')->implode(', ') ?: 'Not specified' }}
                                    </dd>
                                </div>

                                <div class="rounded-xl border border-slate-700/70 bg-slate-950/25 p-3">
                                    <dt class="font-semibold text-slate-100">Languages</dt>
                                    <dd class="mt-2 leading-6 text-slate-300">
                                        {{ $school->languages->pluck('name')->implode(', ') ?: 'Not specified' }}
                                    </dd>
                                </div>

                                <div class="rounded-xl border border-slate-700/70 bg-slate-950/25 p-3">
                                    <dt class="font-semibold text-slate-100">Fees</dt>
                                    <dd class="mt-2 leading-6 text-slate-300">
                                        @if($school->feesMin && $school->feesMax)
                                            {{ number_format($school->feesMin) }} - {{ number_format($school->feesMax) }}
                                            {{ $school->currency }} / {{ $school->feePeriod }}
                                        @elseif($school->feesMin)
                                            From {{ number_format($school->feesMin) }} {{ $school->currency }} / {{ $school->feePeriod }}
                                        @elseif($school->feesMax)
                                            Up to {{ number_format($school->feesMax) }} {{ $school->currency }} / {{ $school->feePeriod }}
                                        @else
                                            Not specified
                                        @endif
                                    </dd>
                                </div>

                                <div class="rounded-xl border border-slate-700/70 bg-slate-950/25 p-3">
                                    <dt class="font-semibold text-slate-100">Description</dt>
                                    <dd class="mt-2 leading-6 text-slate-300">
                                        {{ $school->description ?: 'Not specified' }}
                                    </dd>
                                </div>

                                <div class="rounded-xl border border-slate-700/70 bg-slate-950/25 p-3">
                                    <dt class="font-semibold text-slate-100">Contact</dt>
                                    <dd class="mt-2 leading-6 text-slate-300">
                                        {{ $school->contactEmail ?: 'No email' }}
                                        <br>
                                        {{ $school->contactPhone ?: 'No phone' }}
                                    </dd>
                                </div>
                            </dl>
                        </article>
                    @endforeach
                </section>
            @endif
        </div>
    </section>
@endsection
