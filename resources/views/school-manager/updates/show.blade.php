@extends('layouts.app')

@section('title', 'Update Request Details')

@section('content')
    <section class="mb-8">
        <p class="page-kicker">Request status</p>
        <h1 class="page-title mt-3">{{ $requestItem->type === 'delete' ? 'Deletion request' : 'Update request' }}</h1>
    </section>

    <div class="panel-raised rounded-3xl p-6 space-y-6">
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-slate-400">School</p>
                <p class="text-white font-semibold">{{ $requestItem->school->name ?? 'Unknown School' }}</p>
            </div>

            <div>
                <p class="text-sm text-slate-400">Status</p>
                <p class="mt-1"><span class="status-chip status-{{ $requestItem->status }}">{{ $requestItem->status }}</span></p>
            </div>

            <div>
                <p class="text-sm text-slate-400">Submitted At</p>
                <p class="text-white">{{ $requestItem->created_at?->toDayDateTimeString() }}</p>
            </div>

            <div>
                <p class="text-sm text-slate-400">Reviewed At</p>
                <p class="text-white">{{ $requestItem->reviewedAt?->toDayDateTimeString() ?? 'Not reviewed yet' }}</p>
            </div>
        </div>

        <div>
            <p class="text-sm text-slate-400 mb-2">
                {{ $requestItem->type === 'delete' ? 'Deletion Request' : 'Requested Changes' }}
            </p>
            <x-update-change-list :changes="$requestItem->changes" :school="$requestItem->school" :type="$requestItem->type" />
        </div>

        @if($requestItem->adminReason)
            <div>
                <p class="text-sm text-slate-400 mb-2">Admin Reason</p>
                <div class="rounded-xl border border-rose-300/25 bg-rose-500/10 p-4 text-rose-100 whitespace-pre-line">
                    {{ $requestItem->adminReason }}
                </div>
            </div>
        @endif
    </div>
@endsection
