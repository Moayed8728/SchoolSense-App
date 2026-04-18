@extends('layouts.app')

@section('title', 'Manager Dashboard')

@section('content')
    <section class="mb-12 border-b border-slate-700/70 pb-10">
        <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_16rem] lg:items-end">
            <div>
                <p class="page-kicker">Manager workspace</p>
                <h1 class="page-title mt-3 max-w-3xl">Manage the school details parents actually rely on.</h1>
                <p class="page-subtitle">Quick contact fixes publish immediately. Changes that affect search, fees, and parent decisions go through admin review.</p>
            </div>

            <a href="{{ route('school-manager.updates.index') }}" class="group border-l border-slate-700 pl-6 transition hover:border-cyan-300/60">
                <p class="text-sm font-medium text-slate-400">Pending review</p>
                <div class="mt-2 flex items-end gap-3">
                    <span class="text-5xl font-semibold tracking-tight text-slate-50">{{ $pendingUpdateCount }}</span>
                    <span class="status-chip status-pending mb-2">Queue</span>
                </div>
                <p class="mt-2 text-sm text-slate-400 group-hover:text-slate-200">View update requests</p>
            </a>
        </div>
    </section>

    @if($schools->isEmpty())
        <div class="max-w-2xl border-l-4 border-amber-300/70 bg-amber-400/10 px-5 py-4">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-amber-200">Setup needed</p>
            <h2 class="mt-2 text-xl font-semibold text-slate-50">No school is linked to this manager account yet.</h2>
            <p class="mt-2 text-sm leading-6 text-slate-300">This account exists, but no owned school is attached. Ask an admin to check the application approval record.</p>
        </div>
    @else
        <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_18rem]">
            <div class="space-y-10">
                @foreach($schools as $school)
                    <article class="border-b border-slate-700/70 pb-10">
                        <div class="grid gap-6 md:grid-cols-[minmax(0,1fr)_auto] md:items-start">
                            <div class="min-w-0">
                                <p class="page-kicker">Owned school</p>
                                <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-50">{{ $school->name }}</h2>
                                <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-slate-400">
                                    <span>{{ $school->city }}, {{ $school->country }}</span>
                                    <span class="hidden h-1 w-1 rounded-full bg-slate-600 sm:inline-block"></span>
                                    <span>Updated {{ $school->updated_at?->diffForHumans() }}</span>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-3 md:justify-end">
                                <a href="{{ route('schools.show', $school) }}" class="btn-secondary">
                                    View
                                </a>
                                <a href="{{ route('school-manager.schools.edit', $school) }}" class="btn-primary">
                                    Edit school
                                </a>
                            </div>
                        </div>

                        <div class="mt-8 grid gap-6 sm:grid-cols-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Website</p>
                                <p class="mt-2 truncate text-sm font-medium text-slate-200">{{ parse_url($school->websiteUrl ?? '', PHP_URL_HOST) ?: 'Missing' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Fees</p>
                                <p class="mt-2 text-sm font-medium text-slate-200">
                                    @if($school->feesMin || $school->feesMax)
                                        {{ $school->currency }} {{ $school->feesMin ? number_format($school->feesMin) : '-' }} - {{ $school->feesMax ? number_format($school->feesMax) : '-' }}
                                    @else
                                        Missing
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Contact</p>
                                <p class="mt-2 truncate text-sm font-medium text-slate-200">{{ $school->contactEmail ?: $school->contactPhone ?: 'Missing' }}</p>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <aside class="lg:sticky lg:top-24 lg:self-start">
                <div class="border-l border-slate-700 pl-5">
                    <p class="text-sm font-semibold text-slate-50">Editing model</p>
                    <div class="mt-4 space-y-4 text-sm leading-6 text-slate-400">
                        <p><span class="font-medium text-emerald-300">Direct:</span> description, contact, website links.</p>
                        <p><span class="font-medium text-amber-300">Review:</span> fees, city, name, curricula, activities, languages.</p>
                        <p><span class="font-medium text-rose-300">Deletion:</span> request only, admin approved.</p>
                    </div>
                </div>
            </aside>
        </div>
    @endif
@endsection
