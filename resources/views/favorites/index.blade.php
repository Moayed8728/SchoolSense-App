@extends('layouts.app')

@section('title', 'Favorites')

@section('content')
<section class="pt-10 pb-20 px-6">
    <div class="max-w-6xl mx-auto">

        <div class="glass-card rounded-3xl border border-slate-600/60 p-8 md:p-10 mb-8">
            <div class="mb-8">
                <a href="{{ route('schools.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-600/70 bg-slate-900/45 px-4 py-2.5 text-sm font-semibold text-slate-100 transition-all duration-200 hover:border-indigo-400/70 hover:bg-slate-700/80">
                    <svg class="w-4 h-4 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Schools
                </a>
            </div>
            <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                <div>
                    <h1 class="font-display font-bold text-3xl md:text-4xl text-slate-50 mb-2">
                        Your <span class="gradient-text">Favorites</span>
                    </h1>
                    <p class="text-slate-300">Compare saved schools with fees, contact links, and programs in one place.</p>
                </div>

                <div class="rounded-2xl border border-slate-600/60 bg-slate-900/45 px-5 py-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Saved Schools</p>
                    <p class="mt-1 text-3xl font-bold text-slate-100">{{ $schools->total() }}</p>
                </div>
            </div>
        </div>

        @if($schools->count())
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-7">
                @foreach($schools as $school)
                    <x-school-card :school="$school" :favorite-controls="true" />
                @endforeach
            </div>

            <div class="mt-12 flex justify-center">
                {{ $schools->links() }}
            </div>
        @else
            <div class="glass-card rounded-3xl border border-slate-600/60 p-12 text-center">
                <h3 class="font-display font-semibold text-2xl text-slate-100 mb-3">No favorites yet</h3>
                <p class="text-slate-300 mb-8">Browse schools and use “Save to Favorites” on a school profile.</p>
                <a href="{{ route('schools.index') }}" class="btn-gradient text-white text-sm font-medium px-6 py-3 rounded-xl">
                    Browse schools
                </a>
            </div>
        @endif
    </div>
</section>
@endsection
