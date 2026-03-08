@extends('layouts.app')
@section('title','Update Request Details')
@section('content')
<section class="pt-10 pb-20 px-6">
    <div class="max-w-4xl mx-auto">
        <x-glass-card title="Update Request Details">
            <div class="flex items-center justify-between mb-6">
                <div class="text-slate-100 font-semibold">School ID: {{ $update->schoolId }}</div>
                <x-badge color="{{ $update->status === 'approved' ? 'emerald' : ($update->status === 'rejected' ? 'rose' : 'amber') }}">
                    {{ ucfirst($update->status) }}
                </x-badge>
            </div>

            @if($update->adminReason)
                <div class="glass-card p-4 rounded-xl border border-slate-700/60 mb-6">
                    <p class="text-sm text-slate-300">
                        <span class="text-slate-500">Admin reason:</span>
                        {{ $update->adminReason }}
                    </p>
                </div>
            @endif

            <pre class="glass-card p-4 rounded-xl border border-slate-700/60 text-xs text-slate-200 overflow-auto">{{ json_encode($update->changes, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) }}</pre>

            <div class="mt-6">
                <a href="{{ route('school-manager.updates.index') }}" class="glass-card px-4 py-2 rounded-xl border border-slate-700/60 text-slate-300 hover:text-white">
                    Back
                </a>
            </div>
        </x-glass-card>
    </div>
</section>
@endsection