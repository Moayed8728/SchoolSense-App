@extends('layouts.app')
@section('title','Create Submission')
@section('content')
<section class="pt-10 pb-20 px-6">
    <div class="max-w-4xl mx-auto">
        <x-glass-card title="Submit a School Listing">
            <form method="POST" action="{{ route('school-manager.submissions.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="text-sm text-slate-300">School Name</label>
                    <input name="name" value="{{ old('name') }}" class="w-full mt-2 glass-card rounded-xl px-4 py-3 border-slate-700" required>
                    @error('name') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-slate-300">Country (2 letters)</label>
                        <input name="country" value="{{ old('country','SA') }}" class="w-full mt-2 glass-card rounded-xl px-4 py-3 border-slate-700" required>
                    </div>
                    <div>
                        <label class="text-sm text-slate-300">City</label>
                        <input name="city" value="{{ old('city') }}" class="w-full mt-2 glass-card rounded-xl px-4 py-3 border-slate-700">
                    </div>
                </div>

                <div>
                    <label class="text-sm text-slate-300">Website (optional)</label>
                    <input name="websiteUrl" value="{{ old('websiteUrl') }}" class="w-full mt-2 glass-card rounded-xl px-4 py-3 border-slate-700">
                </div>

                <div>
                    <label class="text-sm text-slate-300">Description (optional)</label>
                    <textarea name="description" rows="5" class="w-full mt-2 glass-card rounded-xl px-4 py-3 border-slate-700">{{ old('description') }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-slate-300">Fees Min (optional)</label>
                        <input type="number" name="feesMin" value="{{ old('feesMin') }}" class="w-full mt-2 glass-card rounded-xl px-4 py-3 border-slate-700">
                    </div>
                    <div>
                        <label class="text-sm text-slate-300">Fees Max (optional)</label>
                        <input type="number" name="feesMax" value="{{ old('feesMax') }}" class="w-full mt-2 glass-card rounded-xl px-4 py-3 border-slate-700">
                    </div>
                </div>

                <div>
                    <label class="text-sm text-slate-300">Fee Period</label>
                    <select name="feePeriod" class="w-full mt-2 glass-card rounded-xl px-4 py-3 border-slate-700" required>
                        <option value="yearly" @selected(old('feePeriod','yearly')==='yearly')>Yearly</option>
                        <option value="semester" @selected(old('feePeriod')==='semester')>Semester</option>
                    </select>
                </div>

                <div class="glass-card rounded-xl p-4 border border-slate-700/60">
                    <p class="text-xs text-slate-400 mb-2">Optional IDs (comma-separated). Example: <span class="text-slate-300">uuid1, uuid2</span></p>
                    <input name="curriculumIds" value="{{ old('curriculumIds') }}" placeholder="Curriculum IDs" class="w-full mt-2 glass-card rounded-xl px-4 py-3 border-slate-700">
                    <input name="activityIds" value="{{ old('activityIds') }}" placeholder="Activity IDs" class="w-full mt-2 glass-card rounded-xl px-4 py-3 border-slate-700">
                    <input name="languageIds" value="{{ old('languageIds') }}" placeholder="Language IDs" class="w-full mt-2 glass-card rounded-xl px-4 py-3 border-slate-700">
                </div>

                <button class="btn-gradient text-white text-sm font-medium px-6 py-3 rounded-xl">
                    Submit (Pending Admin Review)
                </button>
            </form>
        </x-glass-card>
    </div>
</section>
@endsection