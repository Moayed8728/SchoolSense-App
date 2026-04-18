@extends('layouts.app')

@section('title', 'Edit School')

@section('content')
<section class="pt-6 pb-20" x-data="{ section: 'direct', dirty: false, deleteOpen: false }">
    <div class="mb-8">
        <a href="{{ route('school-manager.dashboard') }}" class="btn-secondary">
            <svg class="w-4 h-4 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to dashboard
        </a>
    </div>

    <div class="grid gap-7 lg:grid-cols-[minmax(0,1fr)_19rem]">
        <div class="panel-raised rounded-3xl p-6 md:p-8">
        <p class="page-kicker">Manager edit</p>
        <h1 class="page-title mt-3">Edit {{ $school->name }}</h1>
        <p class="page-subtitle">This page intentionally separates quick contact fixes from parent-impacting fields that need admin review.</p>

        <div class="mt-7 flex flex-wrap gap-2">
            <button type="button" @click="section = 'direct'" :class="section === 'direct' ? 'bg-emerald-300 text-slate-950 border-emerald-200' : 'bg-slate-900/45 text-slate-300 border-slate-700'" class="rounded-full border px-4 py-2 text-sm font-semibold transition">
                Direct save
            </button>
            <button type="button" @click="section = 'review'" :class="section === 'review' ? 'bg-amber-200 text-slate-950 border-amber-100' : 'bg-slate-900/45 text-slate-300 border-slate-700'" class="rounded-full border px-4 py-2 text-sm font-semibold transition">
                Admin review
            </button>
            <button type="button" @click="deleteOpen = !deleteOpen" :class="deleteOpen ? 'bg-rose-200 text-slate-950 border-rose-100' : 'bg-slate-900/45 text-slate-300 border-slate-700'" class="rounded-full border px-4 py-2 text-sm font-semibold transition">
                Deletion
            </button>
        </div>

        <form method="POST" action="{{ route('school-manager.schools.update', $school) }}" class="mt-8 space-y-8" @input="dirty = true">
            @csrf
            @method('PATCH')

            <div x-show="section === 'direct'" x-transition.opacity.duration.150ms>
                <h2 class="text-lg font-semibold text-emerald-100">Direct update fields</h2>
                <p class="mt-2 text-sm text-slate-400">These fields publish immediately because they do not change search/filter logic.</p>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="block text-sm text-slate-300 mb-2">Description</label>
                        <textarea name="description" rows="5" class="field-shell w-full">{{ old('description', $school->description) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm text-slate-300 mb-2">Website URL</label>
                        <input type="url" name="websiteUrl" value="{{ old('websiteUrl', $school->websiteUrl) }}" class="field-shell w-full">
                    </div>
                    <div>
                        <label class="block text-sm text-slate-300 mb-2">Contact Page URL</label>
                        <input type="url" name="contactPageUrl" value="{{ old('contactPageUrl', $school->contactPageUrl) }}" class="field-shell w-full">
                    </div>
                    <div>
                        <label class="block text-sm text-slate-300 mb-2">Contact Email</label>
                        <input type="email" name="contactEmail" value="{{ old('contactEmail', $school->contactEmail) }}" class="field-shell w-full">
                    </div>
                    <div>
                        <label class="block text-sm text-slate-300 mb-2">Contact Phone</label>
                        <input type="text" name="contactPhone" value="{{ old('contactPhone', $school->contactPhone) }}" class="field-shell w-full">
                    </div>
                </div>
            </div>

            <div x-show="section === 'review'" x-transition.opacity.duration.150ms class="section-rule">
                <h2 class="text-lg font-semibold text-amber-100">Admin review fields</h2>
                <p class="mt-2 text-sm text-slate-400">These values influence search, fees, recommendations, or parent decisions.</p>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm text-slate-300 mb-2">School Name</label>
                        <input type="text" name="name" value="{{ old('name', $school->name) }}" class="field-shell w-full">
                    </div>
                    <div>
                        <label class="block text-sm text-slate-300 mb-2">City</label>
                        <input type="text" name="city" value="{{ old('city', $school->city) }}" class="field-shell w-full">
                    </div>
                    <div>
                        <label class="block text-sm text-slate-300 mb-2">Fees Min</label>
                        <input type="number" name="feesMin" value="{{ old('feesMin', $school->feesMin) }}" class="field-shell w-full">
                    </div>
                    <div>
                        <label class="block text-sm text-slate-300 mb-2">Fees Max</label>
                        <input type="number" name="feesMax" value="{{ old('feesMax', $school->feesMax) }}" class="field-shell w-full">
                    </div>
                    <div>
                        <label class="block text-sm text-slate-300 mb-2">Currency</label>
                        <input type="text" name="currency" value="{{ old('currency', $school->currency) }}" maxlength="3" class="field-shell w-full uppercase">
                    </div>
                    <div>
                        <label class="block text-sm text-slate-300 mb-2">Fee Period</label>
                        <select name="feePeriod" class="field-shell w-full">
                            <option value="yearly" @selected(old('feePeriod', $school->feePeriod) === 'yearly')>Yearly</option>
                            <option value="semester" @selected(old('feePeriod', $school->feePeriod) === 'semester')>Semester</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 grid gap-4 md:grid-cols-3">
                    <div>
                        <label class="block text-sm text-slate-300 mb-2">Curricula</label>
                        <div class="max-h-56 space-y-2 overflow-y-auto rounded-2xl border border-slate-700/80 bg-slate-900/45 p-3">
                            @foreach($curricula as $curriculum)
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-200 transition hover:bg-slate-800">
                                    <input
                                        type="checkbox"
                                        name="curriculumIds[]"
                                        value="{{ $curriculum->id }}"
                                        @checked(in_array($curriculum->id, old('curriculumIds', $school->curricula->pluck('id')->all())))
                                        class="rounded border-slate-600 bg-slate-950 text-cyan-400 focus:ring-cyan-300/40"
                                    >
                                    <span>{{ $curriculum->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-2 text-xs text-slate-500">Choose as many as apply.</p>
                    </div>
                    <div>
                        <label class="block text-sm text-slate-300 mb-2">Activities</label>
                        <div class="max-h-56 space-y-2 overflow-y-auto rounded-2xl border border-slate-700/80 bg-slate-900/45 p-3">
                            @foreach($activities as $activity)
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-200 transition hover:bg-slate-800">
                                    <input
                                        type="checkbox"
                                        name="activityIds[]"
                                        value="{{ $activity->id }}"
                                        @checked(in_array($activity->id, old('activityIds', $school->activities->pluck('id')->all())))
                                        class="rounded border-slate-600 bg-slate-950 text-cyan-400 focus:ring-cyan-300/40"
                                    >
                                    <span>{{ $activity->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-2 text-xs text-slate-500">Multiple activities are supported.</p>
                    </div>
                    <div>
                        <label class="block text-sm text-slate-300 mb-2">Languages</label>
                        <div class="max-h-56 space-y-2 overflow-y-auto rounded-2xl border border-slate-700/80 bg-slate-900/45 p-3">
                            @foreach($languages as $language)
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-2 text-sm text-slate-200 transition hover:bg-slate-800">
                                    <input
                                        type="checkbox"
                                        name="languageIds[]"
                                        value="{{ $language->id }}"
                                        @checked(in_array($language->id, old('languageIds', $school->languages->pluck('id')->all())))
                                        class="rounded border-slate-600 bg-slate-950 text-cyan-400 focus:ring-cyan-300/40"
                                    >
                                    <span>{{ $language->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-2 text-xs text-slate-500">Select every language offered.</p>
                    </div>
                </div>
            </div>

            <div class="sticky bottom-4 z-10 rounded-2xl border border-slate-700/80 bg-slate-950/80 p-3 backdrop-blur">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-400">
                        <span x-show="dirty" x-transition class="font-semibold text-cyan-200">Unsaved edits detected.</span>
                        <span x-show="!dirty">No changes made yet.</span>
                    </p>
                    <button class="btn-primary">Save changes</button>
                </div>
            </div>
        </form>
    </div>

    <aside class="space-y-4">
        <div class="rail-card rounded-2xl p-5">
            <p class="text-sm font-semibold text-slate-100">What happens on save?</p>
            <div class="mt-4 space-y-3 text-sm text-slate-400">
                <p><span class="text-emerald-200">Direct fields</span> update the school immediately.</p>
                <p><span class="text-amber-200">Review fields</span> create a pending request for admin approval.</p>
                <p><span class="text-cyan-200">Mixed saves</span> do both in one submit.</p>
            </div>
        </div>

    <div x-show="deleteOpen" x-transition.opacity.duration.150ms class="panel-raised rounded-2xl border border-rose-300/25 p-6">
        <h2 class="text-xl font-semibold text-rose-100">Request deletion</h2>
        <p class="mt-2 text-sm text-slate-400">Deletion is admin-controlled. The school remains visible unless an admin approves.</p>
        <form method="POST" action="{{ route('school-manager.schools.deletion-request', $school) }}" class="mt-4 space-y-3">
            @csrf
            <textarea name="reason" rows="4" class="field-shell w-full" placeholder="Explain why this school should be deleted" required></textarea>
            <button class="btn-danger">Submit deletion request</button>
        </form>
    </div>
    </aside>
    </div>
</section>
@endsection
