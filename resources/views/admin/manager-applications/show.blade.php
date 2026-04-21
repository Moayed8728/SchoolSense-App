@extends('layouts.app')

@section('title', 'Review Manager Application')

@section('content')
    @php
        $applicationContactStatus = $requestItem->contactEmail && $requestItem->contactPhone
            ? 'Complete'
            : (($requestItem->contactEmail || $requestItem->contactPhone) ? 'Partial' : 'Missing');
        $applicationContactStatusClass = [
            'Complete' => 'status-approved',
            'Partial' => 'status-pending',
            'Missing' => 'status-rejected',
        ][$applicationContactStatus];
    @endphp

    <section class="mb-8">
        <p class="page-kicker">Application review</p>
        <h1 class="page-title mt-3">Review Manager Application</h1>
        <p class="page-subtitle">A single approval creates the manager account, school listing, and owner link.</p>
    </section>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="panel-raised rounded-3xl p-6 lg:col-span-2">
            <h2 class="mb-4 text-xl font-semibold text-white">Applicant</h2>
            <div class="grid gap-4 md:grid-cols-2 text-sm">
                <div>
                    <p class="text-slate-400">Full Name</p>
                    <p class="font-semibold text-white">{{ $requestItem->fullName }}</p>
                </div>
                <div>
                    <p class="text-slate-400">Email</p>
                    <p class="text-white">{{ $requestItem->email }}</p>
                </div>
                <div>
                    <p class="text-slate-400">Status</p>
                    <p class="mt-1"><span class="status-chip status-{{ $requestItem->status }}">{{ $requestItem->status }}</span></p>
                </div>
                <div>
                    <p class="text-slate-400">Submitted</p>
                    <p class="text-white">{{ $requestItem->created_at?->toDayDateTimeString() }}</p>
                </div>
            </div>

            <h2 class="mb-4 mt-8 text-xl font-semibold text-white">School</h2>
            <div class="grid gap-4 md:grid-cols-2 text-sm">
                <div>
                    <p class="text-slate-400">School Name</p>
                    <p class="font-semibold text-white">{{ $requestItem->schoolName }}</p>
                </div>
                <div>
                    <p class="text-slate-400">Location</p>
                    <p class="text-white">{{ $requestItem->city }}, {{ $requestItem->country }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-slate-400">Website</p>
                    <p class="break-all text-white">{{ $requestItem->websiteUrl }}</p>
                </div>
                <div>
                    <p class="text-slate-400">Contact Email</p>
                    <p class="text-white">{{ $requestItem->contactEmail ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-slate-400">Contact Phone</p>
                    <p class="text-white">{{ $requestItem->contactPhone ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-slate-400">Fees</p>
                    <p class="text-white">
                        {{ $requestItem->currency }}
                        {{ $requestItem->feesMin ? number_format($requestItem->feesMin) : '—' }}
                        -
                        {{ $requestItem->feesMax ? number_format($requestItem->feesMax) : '—' }}
                        / {{ $requestItem->feePeriod }}
                    </p>
                </div>
            </div>

            <div class="mt-6">
                <p class="mb-2 text-slate-400">Description</p>
                <div class="rounded-xl border border-slate-700/70 bg-slate-900/45 p-4 text-slate-200 whitespace-pre-line">{{ $requestItem->description ?: '—' }}</div>
            </div>

            <section class="mt-6 rounded-2xl border border-cyan-300/30 bg-slate-900/80 p-6 shadow-xl shadow-cyan-950/10">
                <div class="mb-6 flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-cyan-300/35 bg-cyan-400/10">
                        <svg class="h-5 w-5 text-cyan-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="page-kicker">Admin tool</p>
                        <h3 class="mt-2 font-display text-xl font-semibold text-slate-50">Verify submitted contact info</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-400">Fetch contact details from the submitted school website before approving this manager application.</p>
                    </div>
                </div>

                @if(session('application_contact_fetch_result') || session('application_contact_fetch_error'))
                    <div class="mb-5 rounded-xl border px-4 py-3 text-sm leading-6 {{ session('application_contact_fetch_error') ? 'border-rose-300/35 bg-rose-500/10 text-rose-100' : 'border-emerald-300/35 bg-emerald-500/10 text-emerald-100' }}">
                        {!! nl2br(e(session('application_contact_fetch_error') ?? session('application_contact_fetch_result'))) !!}
                    </div>
                @endif

                <div class="rounded-xl border border-slate-700/70 bg-slate-950/30 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Submitted contact status</p>
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                        <span class="status-chip {{ $applicationContactStatusClass }}">{{ $applicationContactStatus }}</span>

                        <form method="POST" action="{{ route('admin.manager-applications.fetch-contacts', $requestItem) }}" x-data="{ loading: false }" @submit="loading = true">
                            @csrf
                            <button type="submit" class="btn-primary min-w-44" :disabled="loading" :class="loading ? 'cursor-wait opacity-75' : ''">
                                <span x-show="! loading">Fetch Contact Info</span>
                                <span x-show="loading" x-cloak class="inline-flex items-center gap-2">
                                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                    Fetching...
                                </span>
                            </button>
                        </form>
                    </div>
                </div>
            </section>

            <div class="mt-6">
                <p class="mb-2 text-slate-400">Proof Text</p>
                <div class="rounded-xl border border-slate-700/70 bg-slate-900/45 p-4 text-slate-200 whitespace-pre-line">{{ $requestItem->proofText ?: '—' }}</div>
            </div>
        </div>

        <div class="rail-card rounded-3xl p-6 self-start lg:sticky lg:top-24">
            @if($requestItem->status === 'pending')
                <form method="POST" action="{{ route('admin.manager-applications.approve', $requestItem) }}">
                    @csrf
                    <button type="submit" class="w-full rounded-xl bg-emerald-300 px-4 py-3 font-semibold text-slate-950 hover:bg-emerald-200">
                        Approve and Create
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.manager-applications.reject', $requestItem) }}" class="mt-5 space-y-3">
                    @csrf
                    <textarea
                        name="adminReason"
                        rows="5"
                        class="field-shell w-full"
                        placeholder="Required rejection reason"
                        required
                    ></textarea>

                    <button type="submit" class="btn-danger w-full">
                        Reject
                    </button>
                </form>
            @else
                <div class="rounded-xl border border-slate-700/70 bg-slate-900/45 p-4 text-sm">
                    <p><span class="text-slate-400">Reviewed By:</span> <span class="text-white">{{ $requestItem->reviewer->name ?? '—' }}</span></p>
                    <p><span class="text-slate-400">Reviewed At:</span> <span class="text-white">{{ $requestItem->reviewedAt?->toDayDateTimeString() ?? '—' }}</span></p>
                    <p><span class="text-slate-400">Admin Reason:</span> <span class="text-white">{{ $requestItem->adminReason ?: '—' }}</span></p>
                </div>
            @endif
        </div>
    </div>
@endsection
