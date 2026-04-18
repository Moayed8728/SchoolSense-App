@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    <section class="mb-10">
        <p class="page-kicker">Admin review desk</p>
        <h1 class="page-title mt-3">Approve the data that changes trust.</h1>
        <p class="page-subtitle">Applications create accounts and schools. Update requests change search-critical fields or remove listings.</p>
    </section>

    <div class="grid gap-6 md:grid-cols-2">
        <a href="{{ route('admin.manager-applications.index') }}" class="panel-raised group rounded-3xl p-7 block transition duration-200 hover:-translate-y-1 hover:border-cyan-300/50">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="page-kicker">Applications</p>
                    <h2 class="mt-3 text-xl font-semibold text-white">Manager applications</h2>
                </div>
                <span class="text-5xl font-bold text-cyan-200">{{ $pendingApplicationCount }}</span>
            </div>
            <p class="mt-6 text-sm leading-6 text-slate-400">Review identity proof and proposed school details in one pass.</p>
            <p class="mt-5 text-sm font-semibold text-cyan-200">Open queue →</p>
        </a>

        <a href="{{ route('admin.updates.index') }}" class="rail-card group rounded-3xl p-7 block transition duration-200 hover:-translate-y-1 hover:border-l-amber-300">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="page-kicker text-amber-200/90">Changes</p>
                    <h2 class="mt-3 text-xl font-semibold text-white">Updates and deletion</h2>
                </div>
                <span class="text-5xl font-bold text-amber-100">{{ $pendingUpdateCount }}</span>
            </div>
            <p class="mt-6 text-sm leading-6 text-slate-400">Apply structured edits only after checking parent-impacting details.</p>
            <p class="mt-5 text-sm font-semibold text-amber-100">Review changes →</p>
        </a>
    </div>
@endsection
