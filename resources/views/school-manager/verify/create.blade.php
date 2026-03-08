@extends('layouts.app')
@section('title','Create Verification Request')
@section('content')
<section class="pt-10 pb-20 px-6">
    <div class="max-w-4xl mx-auto">
        <x-glass-card title="Create Verification Request">
            <form method="POST" action="{{ route('school-manager.verify.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="text-sm text-slate-300">Full Name</label>
                    <input name="fullName" value="{{ old('fullName') }}" class="w-full mt-2 glass-card rounded-xl px-4 py-3 border-slate-700" required>
                    @error('fullName') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-sm text-slate-300">School Name</label>
                    <input name="schoolName" value="{{ old('schoolName') }}" class="w-full mt-2 glass-card rounded-xl px-4 py-3 border-slate-700" required>
                    @error('schoolName') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-sm text-slate-300">School Website (optional)</label>
                    <input name="websiteUrl" value="{{ old('websiteUrl') }}" class="w-full mt-2 glass-card rounded-xl px-4 py-3 border-slate-700">
                    @error('websiteUrl') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-sm text-slate-300">Proof / Notes (optional)</label>
                    <textarea name="proofText" rows="5" class="w-full mt-2 glass-card rounded-xl px-4 py-3 border-slate-700">{{ old('proofText') }}</textarea>
                    @error('proofText') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <button class="btn-gradient text-white text-sm font-medium px-6 py-3 rounded-xl">
                    Submit request
                </button>
            </form>
        </x-glass-card>
    </div>
</section>
@endsection