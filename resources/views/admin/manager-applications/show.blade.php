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
        $pendingApplicationContactReview = session('admin.application-contact-review.' . $requestItem->id);
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

                @if($pendingApplicationContactReview)
                    <div class="mt-4 rounded-xl border border-amber-300/30 bg-amber-500/10 p-4 text-sm text-amber-50">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-amber-200/80">Pending review</p>
                                <p class="mt-2 text-sm leading-6 text-amber-50">
                                    {{ ($pendingApplicationContactReview['diffCount'] ?? 0) > 0
                                        ? 'Fetched values differ from the submitted application. Review the differences before approving anything.'
                                        : 'The fetch completed, but it did not find any differences from the submitted application.' }}
                                </p>
                            </div>
                            <span class="rounded-full border border-amber-300/35 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-amber-100">
                                {{ $pendingApplicationContactReview['diffCount'] ?? 0 }} difference{{ ($pendingApplicationContactReview['diffCount'] ?? 0) === 1 ? '' : 's' }}
                            </span>
                        </div>

                        <div class="mt-4 overflow-x-auto rounded-xl border border-amber-200/20">
                            <div class="min-w-[46rem]">
                                <div class="grid grid-cols-[minmax(10rem,1.1fr)_minmax(12rem,1.4fr)_minmax(12rem,1.4fr)_7rem] border-b border-amber-200/20 bg-slate-950/50 text-xs font-semibold uppercase tracking-[0.16em] text-amber-200/80">
                                    <div class="px-4 py-3">Field</div>
                                    <div class="px-4 py-3">Submitted</div>
                                    <div class="px-4 py-3">Fetched</div>
                                    <div class="px-4 py-3">Status</div>
                                </div>
                                @foreach(($pendingApplicationContactReview['differences'] ?? []) as $difference)
                                    <div class="grid grid-cols-[minmax(10rem,1.1fr)_minmax(12rem,1.4fr)_minmax(12rem,1.4fr)_7rem] border-b border-amber-200/10 bg-slate-950/35 last:border-b-0">
                                        <div class="px-4 py-4 font-semibold text-amber-50">{{ $difference['label'] }}</div>
                                        <div class="px-4 py-4 text-slate-100 break-all">{{ $difference['current'] ?: 'Not set' }}</div>
                                        <div class="px-4 py-4 text-amber-50 break-all">{{ $difference['proposed'] ?: 'Not found' }}</div>
                                        <div class="px-4 py-4">
                                            <span class="inline-flex rounded-full border px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] {{ $difference['isDifferent'] ? 'border-amber-300/35 text-amber-200' : 'border-emerald-300/35 text-emerald-200' }}">
                                                {{ $difference['isDifferent'] ? 'Changed' : 'Same' }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-4 grid gap-3 xl:grid-cols-3">
                            <div class="rounded-xl border border-slate-700/70 bg-slate-950/35 p-3">
                                <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Fetched emails</p>
                                <p class="mt-2 break-words text-slate-100">{{ !empty($pendingApplicationContactReview['emails']) ? implode(', ', $pendingApplicationContactReview['emails']) : 'email not found' }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-700/70 bg-slate-950/35 p-3">
                                <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Fetched phones</p>
                                <p class="mt-2 break-words text-slate-100">{{ !empty($pendingApplicationContactReview['phones']) ? implode(', ', $pendingApplicationContactReview['phones']) : 'phone number not found' }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-700/70 bg-slate-950/35 p-3">
                                <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Fetched contact page</p>
                                <p class="mt-2 break-all text-slate-100">{{ $pendingApplicationContactReview['contactPageUrl'] ?: 'contact page not found' }}</p>
                            </div>
                        </div>

                        @if(!empty($pendingApplicationContactReview['visited']))
                            <div class="mt-4 rounded-xl border border-slate-700/70 bg-slate-950/35 p-3">
                                <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Visited pages</p>
                                <div class="mt-3 space-y-2">
                                    @foreach($pendingApplicationContactReview['visited'] as $visitedPage)
                                        <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-slate-700/60 bg-slate-900/50 px-3 py-2">
                                            <p class="min-w-0 flex-1 break-all text-slate-100">{{ $visitedPage['url'] ?? 'Unknown URL' }}</p>
                                            <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">{{ $visitedPage['status'] ?? 'unknown' }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="mt-5 flex flex-wrap gap-3">
                            @if(($pendingApplicationContactReview['diffCount'] ?? 0) > 0)
                                <form method="POST" action="{{ route('admin.manager-applications.apply-contacts', $requestItem) }}">
                                    @csrf
                                    <button type="submit" class="btn-primary">Apply Updates</button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('admin.manager-applications.cancel-contacts', $requestItem) }}">
                                @csrf
                                <button type="submit" class="btn-secondary">{{ ($pendingApplicationContactReview['diffCount'] ?? 0) > 0 ? 'Keep Submitted and Cancel' : 'Dismiss Review' }}</button>
                            </form>
                        </div>
                    </div>
                @endif
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
