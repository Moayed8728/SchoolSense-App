<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Curriculum;
use App\Models\Language;
use App\Models\SchoolManagerApplication;
use App\Http\Requests\StoreSchoolManagerApplicationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SchoolManagerApplicationController extends Controller
{
    public function create(): View
    {
        return view('school-manager-applications.create', [
            'curricula' => Curriculum::orderBy('name')->get(),
            'activities' => Activity::orderBy('name')->get(),
            'languages' => Language::orderBy('name')->get(),
        ]);
    }

    public function store(StoreSchoolManagerApplicationRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $hasPendingApplication = SchoolManagerApplication::query()
            ->where('email', $validated['email'])
            ->where('status', 'pending')
            ->exists();

        if ($hasPendingApplication) {
            return back()
                ->withErrors(['email' => 'There is already a pending school manager application for this email.'])
                ->withInput();
        }

        $application = SchoolManagerApplication::create([
            ...$validated,
            'password' => Hash::make($validated['password']),
            'curriculumIds' => $validated['curriculumIds'] ?? [],
            'activityIds' => $validated['activityIds'] ?? [],
            'languageIds' => $validated['languageIds'] ?? [],
            'status' => 'pending',
        ]);

        $visibleApplications = session('school_manager_application_access', []);
        $visibleApplications[$application->id] = $application->email;
        session(['school_manager_application_access' => $visibleApplications]);

        return redirect()
            ->route('school-manager-applications.status', $application)
            ->with('success', 'Your school manager application was submitted for admin review.');
    }

    public function status(SchoolManagerApplication $application): View
    {
        $user = auth()->user();
        $visibleApplications = session('school_manager_application_access', []);
        $sessionEmail = $visibleApplications[$application->id] ?? null;

        abort_unless(
            ($user && $user->email === $application->email)
                || $sessionEmail === $application->email,
            403
        );

        return view('school-manager-applications.status', compact('application'));
    }
}
