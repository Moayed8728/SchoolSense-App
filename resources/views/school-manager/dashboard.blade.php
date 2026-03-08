@extends('layouts.app')

@section('title', 'School Manager')

@section('content')
<section class="pt-10 pb-20 px-6">
    <div class="max-w-6xl mx-auto">

        <div class="glass-card rounded-3xl border border-slate-700/60 p-8 md:p-10 mb-10">
            <h1 class="font-display font-bold text-3xl md:text-4xl text-slate-50 mb-3">
                School Manager <span class="gradient-text">Dashboard</span>
            </h1>
            <p class="text-slate-300 text-sm md:text-base">
                Submit your school, request updates, and manage your verification in one place.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="{{ route('school-manager.submissions.create') }}"
               class="glass-card rounded-2xl p-6 border border-slate-700/60 hover:border-slate-600 transition-all duration-200 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-display font-semibold text-lg text-slate-100">New Submission</h2>
                    <span class="w-9 h-9 rounded-xl btn-gradient flex items-center justify-center text-white">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                    </span>
                </div>
                <p class="text-sm text-slate-400 mb-4">
                    Submit a new school to be listed on SchoolSense.
                </p>
                <span class="text-xs text-indigo-300 font-medium inline-flex items-center gap-1">
                    Start submission
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </span>
            </a>

            <a href="{{ route('school-manager.submissions.index') }}"
               class="glass-card rounded-2xl p-6 border border-slate-700/60 hover:border-slate-600 transition-all duration-200 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-display font-semibold text-lg text-slate-100">My Submissions</h2>
                    <span class="w-9 h-9 rounded-xl bg-slate-800/70 border border-slate-600/60 flex items-center justify-center text-slate-200">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 12h14M5 16h8"/>
                        </svg>
                    </span>
                </div>
                <p class="text-sm text-slate-400 mb-4">
                    Track the status of your submitted schools.
                </p>
                <span class="text-xs text-indigo-300 font-medium inline-flex items-center gap-1">
                    View submissions
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </span>
            </a>

            <a href="{{ route('school-manager.updates.index') }}"
               class="glass-card rounded-2xl p-6 border border-slate-700/60 hover:border-slate-600 transition-all duration-200 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-display font-semibold text-lg text-slate-100">Update Requests</h2>
                    <span class="w-9 h-9 rounded-xl bg-slate-800/70 border border-slate-600/60 flex items-center justify-center text-slate-200">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5h2m-1 0v6m0 4h.01M4 6a9 9 0 1116 0 9 9 0 01-16 0z"/>
                        </svg>
                    </span>
                </div>
                <p class="text-sm text-slate-400 mb-4">
                    Request updates to existing school information.
                </p>
                <span class="text-xs text-indigo-300 font-medium inline-flex items-center gap-1">
                    Manage updates
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </span>
            </a>
        </div>
    </div>
</section>
@endsection

