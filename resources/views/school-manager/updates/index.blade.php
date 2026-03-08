@extends('layouts.app')
@section('title','Update Requests')
@section('content')
<section class="pt-10 pb-20 px-6">
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="font-display font-bold text-2xl text-slate-100">Update Requests</h1>
            <a href="{{ route('school-manager.updates.create') }}" class="btn-gradient text-white text-sm font-medium px-5 py-2.5 rounded-xl">
                New Update Request
            </a>
        </div>

        <div class="grid grid-cols-1 gap-4">
            @forelse($requests as $r)
                <a href="{{ route('school-manager.updates.show', $r) }}" class="glass-card rounded-2xl p-5 border border-slate-700/60 hover:border-slate-600 transition">
                    <div class="flex items-center justify-between">
                        <div class="text-slate-100 font-semibold">School: {{ $r->schoolId }}</div>
                        <x-badge color="{{ $r->status === 'approved' ? 'emerald' : ($r->status === 'rejected' ? 'rose' : 'amber') }}">
                            {{ ucfirst($r->status) }}
                        </x-badge>
                    </div>
                </a>
            @empty
                <div class="glass-card rounded-2xl p-10 border border-slate-700/60 text-center text-slate-400">
                    No update requests yet.
                </div>
            @endforelse
        </div>

        <div class="mt-10 flex justify-center">
            {{ $requests->links() }}
        </div>
    </div>
</section>
@endsection