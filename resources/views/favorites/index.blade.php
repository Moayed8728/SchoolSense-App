@extends('layouts.app')

@section('title', 'Favorites')

@section('content')
<section class="pt-10 pb-20 px-6">
    <div class="max-w-6xl mx-auto">

        <div class="glass-card rounded-3xl border border-slate-700/60 p-8 md:p-10 mb-8">
            <div class="mb-6">
                <a href="{{ route('schools.index') }}"
                   class="glass-card text-slate-300 text-sm font-medium px-4 py-2 rounded-xl inline-flex items-center gap-2 hover:text-white hover:border-slate-600 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Schools
                </a>
            </div>
            <h1 class="font-display font-bold text-3xl md:text-4xl text-slate-50 mb-2">
                Your <span class="gradient-text">Favorites</span>
            </h1>
            <p class="text-slate-300">Saved schools appear here.</p>
        </div>

        @if($schools->count())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($schools as $school)
                    <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                        <div class="flex-1 min-w-0">
                            <x-school-card :school="$school" />
                        </div>

                        <form method="POST" action="{{ route('favorites.destroy', $school) }}" class="shrink-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="glass-card text-slate-300 text-xs font-medium px-3 py-2 rounded-xl inline-flex items-center justify-center gap-1.5 hover:text-white hover:border-slate-600 transition-all duration-200 whitespace-nowrap">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.75.75 0 011.04 0l2.12 2.024 2.94.427a.75.75 0 01.416 1.279l-2.127 2.19.502 3.09a.75.75 0 01-1.088.79L12 12.347l-2.263 1.182a.75.75 0 01-1.088-.79l.502-3.09-2.127-2.19a.75.75 0 01.416-1.28l2.94-.426 2.12-2.024z"/>
                                </svg>
                                Remove
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>

            <div class="mt-12 flex justify-center">
                {{ $schools->links() }}
            </div>
        @else
            <div class="glass-card rounded-3xl border border-slate-700/60 p-12 text-center">
                <h3 class="font-display font-semibold text-2xl text-slate-100 mb-3">No favorites yet</h3>
                <p class="text-slate-300 mb-8">Browse schools and hit “Save”.</p>
                <a href="{{ route('schools.index') }}" class="btn-gradient text-white text-sm font-medium px-6 py-3 rounded-xl">
                    Browse schools
                </a>
            </div>
        @endif
    </div>
</section>
@endsection