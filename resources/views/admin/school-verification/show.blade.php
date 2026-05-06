@extends('layouts.app')

@section('title', 'School Verification Review')

@section('content')
    @php
        $contactStatus = $school->contactEmail && $school->contactPhone
            ? 'Complete'
            : (($school->contactEmail || $school->contactPhone) ? 'Partial' : 'Missing');
        $contactStatusClass = [
            'Complete' => 'status-approved',
            'Partial' => 'status-pending',
            'Missing' => 'status-rejected',
        ][$contactStatus];
    @endphp

    <section class="mb-8 flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="page-kicker">Admin verification</p>
            <h1 class="page-title mt-3">School verification review</h1>
            <p class="page-subtitle">Compare the school’s current contact data against the latest fetched result before saving anything.</p>
        </div>
        <a href="{{ route('admin.school-verification.index') }}" class="btn-secondary">Back to School Verification</a>
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
                <p class="mt-3 max-w-md text-sm leading-6 text-slate-400">We’re checking the website and building the full verification comparison.</p>
            </div>
        </div>

        <section class="panel-raised rounded-3xl p-7">
            <div class="flex flex-wrap items-start justify-between gap-6">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-3">
                        <h2 class="font-display text-3xl font-bold text-slate-50">{{ $school->name }}</h2>
                        <span class="status-chip {{ $contactStatusClass }}">{{ $contactStatus }}</span>
                    </div>
                    <p class="mt-2 text-sm text-slate-400">{{ $school->city }}@if($school->country), {{ $school->country }}@endif</p>
                    <p class="mt-4 max-w-3xl text-sm leading-6 text-slate-400">{{ $school->description ?: 'No description has been added for this school yet.' }}</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('schools.show', $school) }}" class="btn-secondary">Open Public School Page</a>
                    @if($school->websiteUrl)
                        <a href="{{ $school->websiteUrl }}" target="_blank" rel="noopener noreferrer" class="btn-secondary">Visit Website</a>
                        <form method="POST" action="{{ route('admin.schools.fetch-contacts', $school) }}" @submit="loading = true">
                            @csrf
                            <button type="submit" class="btn-primary" :disabled="loading">Fetch Info</button>
                        </form>
                    @endif
                </div>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(22rem,0.9fr)]">
            <section class="space-y-6">
                @if(session('contact_fetch_result') || session('contact_fetch_error'))
                    <div class="rounded-2xl border px-5 py-4 text-sm leading-6 {{ session('contact_fetch_error') ? 'border-rose-300/35 bg-rose-500/10 text-rose-100' : 'border-emerald-300/35 bg-emerald-500/10 text-emerald-100' }}">
                        {!! nl2br(e(session('contact_fetch_error') ?? session('contact_fetch_result'))) !!}
                    </div>
                @endif

                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="glass-card rounded-2xl p-5">
                        <p class="page-kicker">Current email</p>
                        <p class="mt-3 break-all text-base font-semibold text-slate-100">{{ $school->contactEmail ?: 'Not set' }}</p>
                    </div>
                    <div class="glass-card rounded-2xl p-5">
                        <p class="page-kicker">Current phone</p>
                        <p class="mt-3 text-base font-semibold text-slate-100">{{ $school->contactPhone ?: 'Not set' }}</p>
                    </div>
                    <div class="glass-card rounded-2xl p-5">
                        <p class="page-kicker">Current contact page</p>
                        <p class="mt-3 break-all text-base font-semibold text-slate-100">{{ $school->contactPageUrl ?: 'Not set' }}</p>
                    </div>
                </div>

                <section class="panel-raised rounded-3xl p-7">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="page-kicker">Verification result</p>
                            <h3 class="mt-2 text-2xl font-semibold text-white">Fetched review details</h3>
                        </div>
                        @if($pendingContactReview)
                            <span class="status-chip {{ ($pendingContactReview['diffCount'] ?? 0) > 0 ? 'status-pending' : 'status-approved' }}">
                                {{ $pendingContactReview['diffCount'] ?? 0 }} difference{{ ($pendingContactReview['diffCount'] ?? 0) === 1 ? '' : 's' }}
                            </span>
                        @endif
                    </div>

                    @if($pendingContactReview)
                        <div class="mt-6 overflow-x-auto rounded-2xl border border-slate-700/70">
                            <table class="data-table min-w-[60rem]">
                                <thead>
                                    <tr>
                                        <th>Field</th>
                                        <th>Current</th>
                                        <th>Fetched</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(($pendingContactReview['differences'] ?? []) as $difference)
                                        <tr>
                                            <td class="font-semibold text-slate-100">{{ $difference['label'] }}</td>
                                            <td class="break-all">{{ $difference['current'] ?: 'Not set' }}</td>
                                            <td class="break-all">{{ $difference['proposed'] ?: 'Not found' }}</td>
                                            <td>
                                                <span class="status-chip {{ $difference['isDifferent'] ? 'status-pending' : 'status-approved' }}">
                                                    {{ $difference['isDifferent'] ? 'Changed' : 'Same' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6 grid gap-4 xl:grid-cols-3">
                            <div class="glass-card rounded-2xl p-5">
                                <p class="page-kicker">Fetched emails</p>
                                <p class="mt-3 break-words text-sm leading-6 text-slate-100">{{ !empty($pendingContactReview['emails']) ? implode(', ', $pendingContactReview['emails']) : 'email not found' }}</p>
                            </div>
                            <div class="glass-card rounded-2xl p-5">
                                <p class="page-kicker">Fetched phones</p>
                                <p class="mt-3 break-words text-sm leading-6 text-slate-100">{{ !empty($pendingContactReview['phones']) ? implode(', ', $pendingContactReview['phones']) : 'phone number not found' }}</p>
                            </div>
                            <div class="glass-card rounded-2xl p-5">
                                <p class="page-kicker">Fetched contact page</p>
                                <p class="mt-3 break-all text-sm leading-6 text-slate-100">{{ $pendingContactReview['contactPageUrl'] ?: 'contact page not found' }}</p>
                            </div>
                        </div>

                        @if(!empty($pendingContactReview['visited']))
                            <div class="mt-6 rounded-2xl border border-slate-700/70 bg-slate-900/40 p-5">
                                <p class="page-kicker">Visited pages</p>
                                <div class="mt-4 space-y-3">
                                    @foreach($pendingContactReview['visited'] as $visitedPage)
                                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-700/60 bg-slate-950/40 px-4 py-3">
                                            <p class="min-w-0 flex-1 break-all text-sm text-slate-100">{{ $visitedPage['url'] ?? 'Unknown URL' }}</p>
                                            <span class="status-chip status-approved">{{ $visitedPage['status'] ?? 'unknown' }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="mt-6 flex flex-wrap gap-3">
                            @if(($pendingContactReview['diffCount'] ?? 0) > 0)
                                <form method="POST" action="{{ route('admin.schools.apply-contacts', $school) }}">
                                    @csrf
                                    <button type="submit" class="btn-primary">Apply Updates</button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('admin.schools.cancel-contacts', $school) }}">
                                @csrf
                                <button type="submit" class="btn-secondary">{{ ($pendingContactReview['diffCount'] ?? 0) > 0 ? 'Keep Current and Cancel' : 'Dismiss Review' }}</button>
                            </form>
                        </div>
                    @else
                        <div class="mt-6 rounded-2xl border border-slate-700/70 bg-slate-900/35 p-6 text-sm leading-6 text-slate-400">
                            No fetched review is waiting for this school right now.
                            @if($school->websiteUrl)
                                Use <span class="text-slate-100 font-semibold">Fetch Info</span> to generate a full verification review.
                            @else
                                This school needs a website URL before contact verification can run.
                            @endif
                        </div>
                    @endif
                </section>
            </section>

            <aside class="space-y-6">
                <section class="rail-card rounded-3xl p-6">
                    <p class="page-kicker">School details</p>
                    <div class="mt-4 space-y-4 text-sm">
                        <div>
                            <p class="text-slate-500">Website</p>
                            <p class="mt-1 break-all text-slate-100">{{ $school->websiteUrl ?: 'Not set' }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Address</p>
                            <p class="mt-1 text-slate-100">{{ $school->address ?: 'Not set' }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Last extraction</p>
                            <p class="mt-1 text-slate-100">{{ $latestContactExtraction?->created_at?->toDayDateTimeString() ?? 'No extraction yet' }}</p>
                        </div>
                    </div>
                </section>

                <section class="panel-raised rounded-3xl p-6">
                    <p class="page-kicker">Latest extraction</p>
                    @if($latestContactExtraction)
                        <div class="mt-4 space-y-4 text-sm">
                            <div>
                                <p class="text-slate-500">Emails</p>
                                <p class="mt-1 break-words text-slate-100">{{ !empty($latestContactExtraction->foundEmails) ? implode(', ', $latestContactExtraction->foundEmails) : 'email not found' }}</p>
                            </div>
                            <div>
                                <p class="text-slate-500">Phones</p>
                                <p class="mt-1 break-words text-slate-100">{{ !empty($latestContactExtraction->foundPhones) ? implode(', ', $latestContactExtraction->foundPhones) : 'phone number not found' }}</p>
                            </div>
                            <div>
                                <p class="text-slate-500">Approved</p>
                                <p class="mt-1 text-slate-100">
                                    @if($latestContactExtraction->approvedAt)
                                        {{ $latestContactExtraction->approvedAt->toDayDateTimeString() }}
                                    @else
                                        Not approved yet
                                    @endif
                                </p>
                            </div>
                        </div>
                    @else
                        <p class="mt-4 text-sm text-slate-500">No extraction has been recorded for this school yet.</p>
                    @endif
                </section>
            </aside>
        </div>
    </div>
@endsection
