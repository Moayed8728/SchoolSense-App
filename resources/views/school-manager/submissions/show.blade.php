@extends('layouts.app')
@section('title','Submission Details')
@section('content')
<section class="pt-10 pb-20 px-6">
    <div class="max-w-4xl mx-auto">
        <x-glass-card title="Submission Details">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <div class="text-slate-100 font-semibold text-lg">{{ $submission->name }}</div>
                    <div class="text-sm text-slate-400">{{ $submission->city ?? '—' }}, {{ $submission->country }}</div>
                </div>
                <x-badge color="{{ $submission->status === 'approved' ? 'emerald' : ($submission->status === 'rejected' ? 'rose' : 'amber') }}">
                    {{ ucfirst($submission->status) }}
                </x-badge>
            </div>

            @if($submission->adminReason)
                <div class="glass-card p-4 rounded-xl border border-slate-700/60 mb-6">
                    <p class="text-sm text-slate-300">
                        <span class="text-slate-500">Admin reason:</span>
                        {{ $submission->adminReason }}
                    </p>
                </div>
            @endif

            <div class="space-y-2 text-sm text-slate-300">
                <div><span class="text-slate-500">Website:</span> {{ $submission->websiteUrl ?? '—' }}</div>
                <div><span class="text-slate-500">Fees:</span> {{ $submission->feesMin ?? '—' }} - {{ $submission->feesMax ?? '—' }} {{ $submission->currency }}</div>
                <div><span class="text-slate-500">Period:</span> {{ $submission->feePeriod }}</div>
            </div>

            <div class="mt-6">
                <a href="{{ route('school-manager.submissions.index') }}" class="glass-card px-4 py-2 rounded-xl border border-slate-700/60 text-slate-300 hover:text-white">
                    Back
                </a>
            </div>
        </x-glass-card>
    </div>
</section>
@endsection