@extends('layouts.app')

@section('title', 'AI Diagnostics')

@section('content')
    @php
        $statusClass = function (string $status): string {
            return $status === 'passed'
                ? 'border-emerald-300/30 bg-emerald-400/10 text-emerald-100'
                : 'border-rose-300/30 bg-rose-400/10 text-rose-100';
        };
    @endphp

    <section class="page-section">
        <div class="ui-container">
            <div class="mb-5">
                <p class="page-kicker">Admin</p>
                <h1 class="page-title mt-3">AI Diagnostics</h1>
                <p class="page-subtitle">
                    Live checks for the deployed Gemini configuration used by AI Search and Compare.
                </p>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <section class="glass-card rounded-2xl p-4 md:p-5">
                    <h2 class="font-display text-xl font-semibold text-slate-50">Configuration</h2>

                    <dl class="mt-4 grid gap-3 text-sm">
                        <div class="rounded-xl border border-slate-700/70 bg-slate-950/25 p-3">
                            <dt class="font-semibold text-slate-100">API key</dt>
                            <dd class="mt-1 text-slate-300">
                                {{ $config['apiKey'] }}
                                @if($config['apiKeyLength'])
                                    <span class="text-slate-500">({{ $config['apiKeyLength'] }} characters)</span>
                                @endif
                            </dd>
                        </div>

                        <div class="rounded-xl border border-slate-700/70 bg-slate-950/25 p-3">
                            <dt class="font-semibold text-slate-100">Reasoning model</dt>
                            <dd class="mt-1 text-slate-300">{{ $config['reasoningModel'] }}</dd>
                        </div>

                        <div class="rounded-xl border border-slate-700/70 bg-slate-950/25 p-3">
                            <dt class="font-semibold text-slate-100">Embedding model</dt>
                            <dd class="mt-1 text-slate-300">{{ $config['embeddingModel'] }}</dd>
                        </div>

                        <div class="rounded-xl border border-slate-700/70 bg-slate-950/25 p-3">
                            <dt class="font-semibold text-slate-100">Stored school embeddings</dt>
                            <dd class="mt-1 text-slate-300">{{ number_format($config['storedEmbeddings']) }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="grid gap-4">
                    <div class="rounded-2xl border p-4 md:p-5 {{ $statusClass($generation['status']) }}">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] opacity-80">Generation</p>
                        <h2 class="mt-2 font-display text-xl font-semibold">AI comparison summaries</h2>
                        <p class="mt-3 text-sm leading-6">{{ $generation['message'] }}</p>
                        @if($generation['detail'])
                            <p class="mt-3 rounded-lg border border-white/10 bg-slate-950/25 px-3 py-2 text-sm">{{ $generation['detail'] }}</p>
                        @endif
                    </div>

                    <div class="rounded-2xl border p-4 md:p-5 {{ $statusClass($embedding['status']) }}">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] opacity-80">Embeddings</p>
                        <h2 class="mt-2 font-display text-xl font-semibold">AI semantic search ranking</h2>
                        <p class="mt-3 text-sm leading-6">{{ $embedding['message'] }}</p>
                        @if($embedding['detail'])
                            <p class="mt-3 rounded-lg border border-white/10 bg-slate-950/25 px-3 py-2 text-sm">{{ $embedding['detail'] }}</p>
                        @endif
                    </div>
                </section>
            </div>
        </div>
    </section>
@endsection
