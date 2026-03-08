@extends('layouts.app')

@section('title', 'Create Update Request')

@section('content')
<section class="pt-10 pb-20 px-6">
    <div class="max-w-4xl mx-auto">
        <x-glass-card title="Request School Update">
            @if($schools->isEmpty())
                <div class="text-center py-10">
                    <h3 class="font-display font-semibold text-xl text-slate-100 mb-2">No approved schools yet</h3>
                    <p class="text-slate-400 mb-6">
                        You can submit update requests only for schools you own after admin approval.
                    </p>
                    <a href="{{ route('school-manager.submissions.index') }}"
                       class="btn-gradient text-white text-sm font-medium px-5 py-2.5 rounded-xl inline-flex">
                        View My Submissions
                    </a>
                </div>
            @else
                <form method="POST" action="{{ route('school-manager.updates.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm text-slate-300 mb-2">School</label>
                        <select name="schoolId" class="w-full glass-card rounded-xl px-4 py-3 border-slate-700">
                            <option value="">Select your school</option>
                            @foreach($schools as $school)
                                <option value="{{ $school->id }}" @selected(old('schoolId') == $school->id)>
                                    {{ $school->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('schoolId')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm text-slate-300 mb-2">Description</label>
                        <textarea name="description" rows="5" class="w-full glass-card rounded-xl px-4 py-3 border-slate-700">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-slate-300 mb-2">Contact Email</label>
                            <input type="email" name="contactEmail" value="{{ old('contactEmail') }}"
                                   class="w-full glass-card rounded-xl px-4 py-3 border-slate-700">
                            @error('contactEmail')
                                <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm text-slate-300 mb-2">Contact Phone</label>
                            <input type="text" name="contactPhone" value="{{ old('contactPhone') }}"
                                   class="w-full glass-card rounded-xl px-4 py-3 border-slate-700">
                            @error('contactPhone')
                                <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm text-slate-300 mb-2">Contact Page URL</label>
                        <input type="url" name="contactPageUrl" value="{{ old('contactPageUrl') }}"
                               class="w-full glass-card rounded-xl px-4 py-3 border-slate-700">
                        @error('contactPageUrl')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-slate-300 mb-2">Fees Min</label>
                            <input type="number" name="feesMin" value="{{ old('feesMin') }}"
                                   class="w-full glass-card rounded-xl px-4 py-3 border-slate-700">
                            @error('feesMin')
                                <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm text-slate-300 mb-2">Fees Max</label>
                            <input type="number" name="feesMax" value="{{ old('feesMax') }}"
                                   class="w-full glass-card rounded-xl px-4 py-3 border-slate-700">
                            @error('feesMax')
                                <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <button type="submit"
                            class="btn-gradient text-white text-sm font-medium px-6 py-3 rounded-xl">
                        Submit Update Request
                    </button>
                </form>
            @endif
        </x-glass-card>
    </div>
</section>
@endsection