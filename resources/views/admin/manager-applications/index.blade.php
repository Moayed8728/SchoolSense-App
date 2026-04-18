@extends('layouts.app')

@section('title', 'Manager Applications')

@section('content')
    <section class="mb-8">
        <p class="page-kicker">Applications</p>
        <h1 class="page-title mt-3">Review account and school together.</h1>
        <p class="page-subtitle">Approving an application creates the manager account, creates the school, and links ownership in one transaction.</p>
    </section>

    <div class="panel rounded-2xl overflow-hidden">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="px-4 py-3 text-left">Applicant</th>
                    <th class="px-4 py-3 text-left">School</th>
                    <th class="px-4 py-3 text-left">City</th>
                    <th class="px-4 py-3 text-left">Website</th>
                    <th class="px-4 py-3 text-left">Submitted</th>
                    <th class="px-4 py-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $requestItem)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-white">{{ $requestItem->fullName }}</p>
                            <p class="text-slate-400">{{ $requestItem->email }}</p>
                        </td>
                        <td class="px-4 py-3">{{ $requestItem->schoolName }}</td>
                        <td class="px-4 py-3">{{ $requestItem->city }}</td>
                        <td class="px-4 py-3 break-all">{{ $requestItem->websiteUrl }}</td>
                        <td class="px-4 py-3">{{ $requestItem->created_at?->diffForHumans() }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.manager-applications.show', $requestItem) }}" class="btn-secondary px-3 py-2">
                                Review
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <p class="text-lg font-semibold text-slate-100">No pending applications</p>
                            <p class="mt-2 text-sm text-slate-400">When a school applies, it will appear here with account and listing details.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $requests->links() }}
    </div>
@endsection
