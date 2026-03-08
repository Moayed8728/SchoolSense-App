@extends('layouts.app')
@section('title','My Submissions')
@section('content')
<section class="pt-10 pb-20 px-6">
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="font-display font-bold text-2xl text-slate-100">My Submissions</h1>
            <a href="{{ route('school-manager.submissions.create') }}" class="btn-gradient text-white text-sm font-medium px-5 py-2.5 rounded-xl">
                New Submission
            </a>
        </div>

        <div class="grid grid-cols-1 gap-4">
            @forelse($submissions as $s)
                <a href="{{ route('school-manager.submissions.show', $s) }}" class="glass-card rounded-2xl p-5 border border-slate-700/60 hover:border-slate-600 transition">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <div class="text-slate-100 font-semibold">{{ $s->name }}</div>
                            <div class="text-sm text-slate-400">{{ $s->city ?? '—' }}, {{ $s->country }}</div>
                        </div>
                        <x-badge color="{{ $s->status === 'approved' ? 'emerald' : ($s->status === 'rejected' ? 'rose' : 'amber') }}">
                            {{ ucfirst($s->status) }}
                        </x-badge>
                    </div>
                    @if($s->adminReason)
                        <p class="text-xs text-slate-500 mt-3">Admin note: {{ $s->adminReason }}</p>
                    @endif
                </a>
            @empty
                <div class="glass-card rounded-2xl p-10 border border-slate-700/60 text-center text-slate-400">
                    No submissions yet.
                </div>
            @endforelse
        </div>

        <div class="mt-10 flex justify-center">
            {{ $submissions->links() }}
        </div>
    </div>
</section>
@endsection