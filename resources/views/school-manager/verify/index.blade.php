@extends('layouts.app')
@section('title','Verification')
@section('content')
<section class="pt-10 pb-20 px-6">
    <div class="max-w-4xl mx-auto">
        <x-glass-card title="School Representative Verification">
            @if($requestRow)
                <p class="text-slate-300 mb-3">Latest request status:</p>
                <div class="flex items-center gap-2 mb-4">
                    <x-badge color="{{ $requestRow->status === 'approved' ? 'emerald' : ($requestRow->status === 'rejected' ? 'rose' : 'amber') }}">
                        {{ ucfirst($requestRow->status) }}
                    </x-badge>
                    <span class="text-sm text-slate-400">{{ $requestRow->schoolName }}</span>
                </div>

                @if($requestRow->adminReason)
                    <div class="glass-card p-4 rounded-xl border border-slate-700/60">
                        <p class="text-sm text-slate-300">
                            <span class="text-slate-500">Admin note:</span>
                            {{ $requestRow->adminReason }}
                        </p>
                    </div>
                @endif

                <div class="mt-6">
                    <a href="{{ route('school-manager.verify.create') }}" class="btn-gradient text-white text-sm font-medium px-5 py-2.5 rounded-xl inline-flex">
                        Submit another request
                    </a>
                </div>
            @else
                <p class="text-slate-300 mb-6">You have not submitted a verification request yet.</p>
                <a href="{{ route('school-manager.verify.create') }}" class="btn-gradient text-white text-sm font-medium px-5 py-2.5 rounded-xl inline-flex">
                    Create verification request
                </a>
            @endif
        </x-glass-card>
    </div>
</section>
@endsection