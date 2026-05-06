@extends('layouts.app')

@section('title', 'School Verification')

@section('content')
    <section class="mb-10">
        <p class="page-kicker">Admin verification</p>
        <h1 class="page-title mt-3">School verification</h1>
        <p class="page-subtitle">Review live contact data from the school website before changing the public record.</p>
    </section>

    <div x-data="{ loading: false }" class="space-y-6">
        <div
            x-show="loading"
            x-cloak
            class="fixed inset-0 z-[90] flex items-center justify-center bg-slate-950/80 backdrop-blur-sm"
        >
            <div class="rounded-3xl border border-cyan-300/30 bg-slate-900/95 px-8 py-10 text-center shadow-2xl shadow-cyan-950/20">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border border-cyan-300/30 bg-cyan-400/10">
                    <svg class="h-8 w-8 animate-spin text-cyan-300" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-80" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                </div>
                <h2 class="mt-5 font-display text-2xl font-semibold text-slate-50">Fetching school contact info</h2>
                <p class="mt-3 max-w-md text-sm leading-6 text-slate-400">We’re checking the school website and preparing the full verification review page.</p>
            </div>
        </div>

        <div class="panel-raised rounded-3xl p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="page-kicker">Verification queue</p>
                    <h2 class="mt-2 text-xl font-semibold text-white">All schools</h2>
                </div>
                <span class="status-chip status-approved">{{ $schools->total() }} schools</span>
            </div>

            <div class="mt-6 overflow-x-auto rounded-2xl border border-slate-700/70">
                <table class="data-table min-w-[74rem]">
                    <thead>
                        <tr>
                            <th>School</th>
                            <th>Website</th>
                            <th>Current email</th>
                            <th>Current phone</th>
                            <th>Current contact page</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schools as $school)
                            <tr>
                                <td>
                                    <div>
                                        <p class="font-semibold text-slate-100">{{ $school->name }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $school->city }}@if($school->country), {{ $school->country }}@endif</p>
                                    </div>
                                </td>
                                <td class="max-w-[14rem]">
                                    @if($school->websiteUrl)
                                        <a href="{{ $school->websiteUrl }}" target="_blank" rel="noopener noreferrer" class="break-all text-cyan-300 hover:text-cyan-200">
                                            {{ parse_url($school->websiteUrl, PHP_URL_HOST) ?? $school->websiteUrl }}
                                        </a>
                                    @else
                                        <span class="text-slate-500">Not set</span>
                                    @endif
                                </td>
                                <td class="max-w-[12rem] break-all">{{ $school->contactEmail ?: 'Not set' }}</td>
                                <td>{{ $school->contactPhone ?: 'Not set' }}</td>
                                <td class="max-w-[14rem] break-all">{{ $school->contactPageUrl ?: 'Not set' }}</td>
                                <td>
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('admin.school-verification.show', $school) }}" class="btn-secondary">Open</a>
                                        @if($school->websiteUrl)
                                            <form method="POST" action="{{ route('admin.schools.fetch-contacts', $school) }}" @submit="loading = true">
                                                @csrf
                                                <button type="submit" class="btn-primary" :disabled="loading">Fetch Info</button>
                                            </form>
                                        @else
                                            <span class="text-xs text-slate-500">No website</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-slate-500">No schools available for verification.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($schools->hasPages())
                <div class="mt-6">
                    {{ $schools->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
