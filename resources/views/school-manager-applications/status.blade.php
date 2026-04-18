<x-guest-layout>
    <p class="page-kicker">Manager application</p>
    <h1 class="mt-3 font-display text-2xl font-bold text-slate-100">Application Status</h1>

    <div class="mt-6 rail-card rounded-2xl p-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Status</p>
                <p class="mt-2 text-2xl font-bold capitalize text-slate-100">{{ $application->status }}</p>
            </div>
            <span class="status-chip status-{{ $application->status }}">{{ $application->status }}</span>
        </div>
        <p class="mt-2 text-sm text-slate-400">{{ $application->schoolName }} · {{ $application->email }}</p>
    </div>

    @if($application->status === 'pending')
        <div class="mt-5 rounded-2xl border border-amber-300/25 bg-amber-400/10 p-4">
            <p class="text-sm font-semibold text-amber-100">Waiting for admin review</p>
            <p class="mt-2 text-sm text-amber-100/80">Keep this page link. Once reviewed, this status will show whether your manager account was created or why the application needs changes.</p>
        </div>
    @elseif($application->status === 'approved')
        <div class="mt-5 rounded-2xl border border-emerald-300/25 bg-emerald-400/10 p-4">
            <p class="text-sm font-semibold text-emerald-100">Approved</p>
            <p class="mt-2 text-sm text-emerald-100/80">Your manager account and school listing were created. Log in with the credentials from your application.</p>
        </div>
        <a href="{{ route('login') }}" class="btn-primary mt-5">Log in</a>
    @else
        <div class="mt-5 rounded-2xl border border-rose-300/30 bg-rose-500/10 p-4">
            <p class="text-sm font-semibold text-rose-100">Rejected</p>
            <p class="mt-2 text-sm text-rose-100">{{ $application->adminReason ?: 'No reason was provided.' }}</p>
        </div>
        <a href="{{ route('school-manager-applications.create') }}" class="btn-primary mt-5">Apply again</a>
    @endif
</x-guest-layout>
