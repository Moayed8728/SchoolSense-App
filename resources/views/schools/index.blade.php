@extends('layouts.app')

@section('title', 'Discover Schools')

@section('content')

<!-- Light Mode Button-->>
<button class="px-4 py-1.5 text-xs font-medium rounded-full glass-card text-slate-300 hover:text-slate-100 border-slate-700 transition-all duration-200">Light Mode</button>
    <!-- Hero Section -->
    <section class="pt-10 pb-10 px-6">
        <div class="max-w-6xl mx-auto">
            <div class="glass-card rounded-3xl border border-slate-700/60 p-8 md:p-12">
                

                <!-- Heading -->
                <h1 class="font-display font-bold text-4xl md:text-5xl lg:text-6xl text-center leading-tight tracking-tight mb-6">
                    <span class="text-slate-500">Discover Schools</span><br>
                    <span class="gradient-text">near you</span>
                </h1>
                <p class="text-center text-slate-300 text-lg max-w-2xl mx-auto mb-10 leading-relaxed">
                    Explore schools with verified links. Detailed data (fees, programs, contact) will be added through school managers and admin verification.
                </p>

                <!-- Search bar (UI only) -->
                <div class="max-w-lg mx-auto">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>

                        <input
                            type="search"
                            placeholder="Search schools, curricula, languages…"
                            class="w-full glass-card rounded-xl pl-11 pr-4 py-3.5 text-sm text-slate-200 placeholder-slate-500 border-slate-700 focus:outline-none focus:border-indigo-500/60 focus:ring-1 focus:ring-indigo-500/30 transition-all duration-200"
                            aria-label="Search schools"
                        >
                        
                    </div>
                </div>

                <!-- Stats row -->
                @if($schools->total() > 0)
                    <div class="mt-10 flex items-center justify-center gap-8 text-center flex-wrap">
                        <div>
                            <div class="font-display font-bold text-2xl gradient-text">{{ $schools->total() }}</div>
                            <div class="text-xs text-slate-400 mt-0.5">Schools listed</div>
                        </div>
                        <div class="w-px h-8 bg-slate-700 hidden sm:block"></div>
                        <div>
                            <div class="font-display font-bold text-2xl text-slate-100">Worldwide</div>
                            <div class="text-xs text-slate-400 mt-0.5">Coverage</div>
                        </div>
                        <div class="w-px h-8 bg-slate-700 hidden sm:block"></div>
                        <div>
                            <div class="font-display font-bold text-2xl text-slate-100">AI</div>
                            <div class="text-xs text-slate-400 mt-0.5">Powered insights</div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </section>

    <!-- Filter bar (UI only for now) -->
     
    <section class="px-6 mt-4 mb-8">
        <div class="max-w-6xl mx-auto">
            <div class="flex items-center gap-3 flex-wrap">
                <span class="text-xs text-slate-300 font-medium uppercase tracking-wider">Filter:</span>
                <button class="px-4 py-1.5 text-xs font-medium rounded-full btn-gradient text-white">All Schools</button>
                <button class="px-4 py-1.5 text-xs font-medium rounded-full glass-card text-slate-300 hover:text-slate-100 border-slate-700 transition-all duration-200">IB Curriculum</button>
                <button class="px-4 py-1.5 text-xs font-medium rounded-full glass-card text-slate-300 hover:text-slate-100 border-slate-700 transition-all duration-200">British</button>
                <button class="px-4 py-1.5 text-xs font-medium rounded-full glass-card text-slate-300 hover:text-slate-100 border-slate-700 transition-all duration-200">American</button>
                <button class="px-4 py-1.5 text-xs font-medium rounded-full glass-card text-slate-300 hover:text-slate-100 border-slate-700 transition-all duration-200">Arabic</button>
            </div>
        </div>
    </section>

    <!-- Schools Grid -->
    <section class="px-6 pb-24">
        <div class="max-w-6xl mx-auto">

            @if($schools->count() > 0)

                <div class="flex items-center justify-between mb-6">
                    <p class="text-sm text-slate-400">
                        Showing <span class="text-slate-200 font-medium">{{ $schools->count() }}</span>
                        of <span class="text-slate-200 font-medium">{{ $schools->total() }}</span> schools
                    </p>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-slate-400">Sort by:</span>
                        <select class="text-xs glass-card text-slate-200 rounded-lg px-3 py-1.5 border-slate-700 focus:outline-none focus:border-indigo-500/60 bg-transparent">
                            <option value="name">Name</option>
                            <option value="fees">Fees</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-7">
                    @foreach($schools as $school)
                        <x-school-card :school="$school" />
                    @endforeach
                </div>

                @if($schools->hasPages())
                    <div class="mt-12 flex justify-center">
                        {{ $schools->links() }}
                    </div>
                @endif

            @else
                    <div class="glass-card rounded-3xl border border-slate-700/60 p-12 md:p-16 text-center flex flex-col items-center justify-center">
                    <div class="w-10 h-10 rounded-xl bg-slate-800/80 border border-slate-700/80 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m9-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-display font-semibold text-2xl text-slate-100 mb-3">No schools found</h3>
                    <p class="text-slate-300 max-w-lg mx-auto leading-relaxed">
                        We’re still building the verified directory. Once school managers submit data and admins approve it,
                        schools will appear here with accurate fees, programs, and contact info.
                    </p>
                </div>
            @endif

        </div>
    </section>
@endsection