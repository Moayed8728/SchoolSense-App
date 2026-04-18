@extends('layouts.app')

@section('title', 'Update Requests')

@section('content')
    <section class="mb-8">
        <p class="page-kicker text-amber-200/90">Review queue</p>
        <h1 class="page-title mt-3">School data changes</h1>
        <p class="page-subtitle">Approve structured edits and deletion requests only after checking the parent-facing impact.</p>
    </section>

    @if($requests->count() > 0)
        <div class="panel rounded-2xl overflow-hidden">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left">User</th>
                        <th class="px-4 py-3 text-left">School</th>
                        <th class="px-4 py-3 text-left">Type</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Submitted</th>
                        <th class="px-4 py-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $requestItem)
                        <tr>
                            <td class="px-4 py-3">{{ $requestItem->user->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $requestItem->school->name ?? $requestItem->schoolId }}</td>
                            <td class="px-4 py-3">{{ $requestItem->type === 'delete' ? 'Deletion' : 'Update' }}</td>
                            <td class="px-4 py-3">
                                <span class="status-chip status-{{ $requestItem->status }}">{{ $requestItem->status }}</span>
                            </td>
                            <td class="px-4 py-3">{{ $requestItem->created_at?->diffForHumans() }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.updates.show', $requestItem) }}" class="btn-secondary px-3 py-2">
                                    Review
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
        <div class="rail-card rounded-2xl p-8">
            <p class="text-lg font-semibold text-slate-100">No pending update requests.</p>
            <p class="mt-2 text-sm text-slate-400">The queue is clear. Approved managers can still save low-risk edits directly.</p>
        </div>
    @endif
@endsection
