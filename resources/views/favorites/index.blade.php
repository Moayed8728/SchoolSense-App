@extends('layouts.app')

@section('title', 'Favorites')

@section('content')
<section class="page-section">
    <div class="ui-container">

        <div class="glass-card mb-5 rounded-2xl border border-slate-600/60 p-4 md:p-5">
            <div class="mb-5">
                <a href="{{ route('schools.index') }}"
                   class="btn-secondary">
                    <svg class="w-4 h-4 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Schools
                </a>
            </div>
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <h1 class="page-title">
                        Your <span class="gradient-text">Favorites</span>
                    </h1>
                    <p class="page-subtitle">Compare saved schools with fees, contact links, and programs in one place.</p>
                </div>

                <div class="rounded-xl border border-slate-600/60 bg-slate-900/45 px-4 py-3">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Saved Schools</p>
                    <p class="mt-1 text-2xl font-bold text-slate-100">{{ $schools->total() }}</p>
                </div>
            </div>
        </div>

        @if($schools->count())
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach($schools as $school)
                    <x-school-card :school="$school" :favorite-controls="true" />
                @endforeach
            </div>

            <div class="mt-8 flex justify-center">
                {{ $schools->links() }}
            </div>
        @else
            <div class="glass-card rounded-2xl border border-slate-600/60 p-8 text-center">
                <h3 class="font-display mb-3 text-xl font-semibold text-slate-100">No favorites yet</h3>
                <p class="mb-5 text-slate-300">Browse schools and use “Save to Favorites” on a school profile.</p>
                <a href="{{ route('schools.index') }}" class="btn-primary">
                    Browse schools
                </a>
            </div>
        @endif
    </div>
</section>
@endsection
