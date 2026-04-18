@extends('layouts.app')

@section('title', 'Review School Update Request')

@section('content')
    <section class="mb-8">
        <p class="page-kicker text-amber-200/90">Admin review</p>
        <h1 class="page-title mt-3">{{ $requestItem->type === 'delete' ? 'Review deletion request' : 'Review school update' }}</h1>
        <p class="page-subtitle">Check the requested change before applying it to the public school listing.</p>
    </section>

    <div class="panel-raised rounded-3xl p-6 space-y-6">
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-slate-400">Requested By</p>
                <p class="text-white font-semibold">{{ $requestItem->user->name ?? 'Unknown User' }}</p>
                <p class="text-slate-300">{{ $requestItem->user->email ?? '—' }}</p>
            </div>

            <div>
                <p class="text-sm text-slate-400">Target School</p>
                <p class="text-white font-semibold">{{ $requestItem->school->name ?? 'Unknown School' }}</p>
                <p class="text-slate-300">{{ $requestItem->school->city ?? '—' }}</p>
            </div>
        </div>

        <div>
            <p class="text-sm text-slate-400 mb-2">
                {{ $requestItem->type === 'delete' ? 'Deletion Request' : 'Requested Changes' }}
            </p>
            <x-update-change-list :changes="$requestItem->changes" :school="$requestItem->school" :type="$requestItem->type" />
        </div>

        @if($requestItem->status === 'pending')
            <div class="flex flex-wrap gap-3">
                <form method="POST" action="{{ route('admin.updates.approve', $requestItem) }}">
                    @csrf
                    <button type="submit" class="rounded-xl bg-emerald-300 px-4 py-2 font-semibold text-slate-950 hover:bg-emerald-200">
                        {{ $requestItem->type === 'delete' ? 'Approve and Delete School' : 'Approve and Apply Changes' }}
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.updates.reject', $requestItem) }}" class="w-full space-y-3">
                    @csrf
                    <textarea
                        name="adminReason"
                        rows="4"
                        class="field-shell w-full"
                        placeholder="Optional rejection reason"
                    ></textarea>

                    <button type="submit" class="btn-danger">
                        Reject
                    </button>
                </form>
            </div>
        @endif
    </div>
@endsection
