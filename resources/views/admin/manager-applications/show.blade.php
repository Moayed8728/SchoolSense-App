@extends('layouts.app')

@section('title', 'Review Manager Application')

@section('content')
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
