@extends('layouts.app')

@section('title', 'Update Requests')

@section('content')
    <div class="mb-8">
        <p class="page-kicker">Manager workspace</p>
        <h1 class="page-title mt-3">Update requests</h1>
    </div>

    @if($requests->count() > 0)
        <div class="overflow-hidden rounded-2xl border border-slate-700/80 bg-slate-800/75 shadow-xl shadow-slate-950/15">
            <table class="min-w-full text-sm text-slate-200">
                <thead class="border-b border-slate-700/80 bg-slate-900/45 text-xs uppercase tracking-[0.16em] text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">School</th>
                        <th class="px-4 py-3 text-left">Type</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Submitted</th>
                        <th class="px-4 py-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $requestItem)
                        <tr class="border-b border-slate-700/60 transition hover:bg-slate-700/25">
                            <td class="px-4 py-3">{{ $requestItem->school->name ?? $requestItem->schoolId }}</td>
                            <td class="px-4 py-3">{{ $requestItem->type === 'delete' ? 'Deletion' : 'Update' }}</td>
                            <td class="px-4 py-3">
                                <span class="status-chip status-{{ $requestItem->status }}">{{ $requestItem->status }}</span>
                            </td>
                            <td class="px-4 py-3">{{ $requestItem->created_at?->diffForHumans() }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('school-manager.updates.show', $requestItem) }}" class="inline-flex items-center gap-2 rounded-xl border border-cyan-300/35 bg-cyan-400/10 px-3 py-2 text-sm font-semibold text-cyan-100 transition hover:border-cyan-300/70 hover:bg-cyan-400/15 focus:outline-none focus:ring-2 focus:ring-cyan-300/40">
                                    View
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $requests->links() }}
        </div>
    @else
        <div class="glass-card rounded-2xl p-8 text-slate-300">
            No update requests found.
        </div>
    @endif
@endsection
