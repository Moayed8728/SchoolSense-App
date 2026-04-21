<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RejectSchoolManagerApplicationRequest;
use App\Models\School;
use App\Models\SchoolManagerApplication;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SchoolManagerApplicationReviewController extends Controller
{
    public function index()
    //phase 4 done
    {
        $requests = SchoolManagerApplication::query()
            ->where('status', 'pending')
            ->latest()
            ->paginate(12);

        return view('admin.manager-applications.index', compact('requests'));
    }

    public function show(SchoolManagerApplication $schoolManagerApplication)
    {
        $schoolManagerApplication->load('reviewer', 'createdUser', 'createdSchool');

        return view('admin.manager-applications.show', [
            'requestItem' => $schoolManagerApplication,
        ]);
    }

    public function approve(SchoolManagerApplication $schoolManagerApplication)
    {
        if ($schoolManagerApplication->status !== 'pending') {
            return back()->with('error', 'This application has already been reviewed.');
        }

        if (User::where('email', $schoolManagerApplication->email)->exists()) {
            return back()->with('error', 'A user with this email already exists.');
        }

        if ($schoolManagerApplication->createdUserId || $schoolManagerApplication->createdSchoolId) {
            return back()->with('error', 'This application already created a manager or school.');
        }

        if (
            $schoolManagerApplication->createdUserId
            && School::where('ownerUserId', $schoolManagerApplication->createdUserId)->exists()
        ) {
            return back()->with('error', 'This manager already owns a school.');
        }

        DB::transaction(function () use ($schoolManagerApplication) {
            $user = User::create([
                'name' => $schoolManagerApplication->fullName,
                'email' => $schoolManagerApplication->email,
                'password' => $schoolManagerApplication->password,
                'role' => 'school_manager',
            ]);

            $school = School::create([
                'ownerUserId' => $user->id,
                'name' => $schoolManagerApplication->schoolName,
                'country' => $schoolManagerApplication->country,
                'city' => $schoolManagerApplication->city,
                'address' => $schoolManagerApplication->address,
                'websiteUrl' => $schoolManagerApplication->websiteUrl,
                'contactEmail' => $schoolManagerApplication->contactEmail,
                'contactPhone' => $schoolManagerApplication->contactPhone,
                'contactPageUrl' => $schoolManagerApplication->contactPageUrl,
                'description' => $schoolManagerApplication->description,
                'feesMin' => $schoolManagerApplication->feesMin,
                'feesMax' => $schoolManagerApplication->feesMax,
                'currency' => $schoolManagerApplication->currency,
                'feePeriod' => $schoolManagerApplication->feePeriod,
            ]);

            $school->curricula()->sync($schoolManagerApplication->curriculumIds ?? []);
            $school->activities()->sync($schoolManagerApplication->activityIds ?? []);
            $school->languages()->sync($schoolManagerApplication->languageIds ?? []);

            $schoolManagerApplication->update([
                'status' => 'approved',
                'adminReason' => null,
                'reviewedBy' => auth()->id(),
                'reviewedAt' => now(),
                'createdUserId' => $user->id,
                'createdSchoolId' => $school->id,
            ]);
        });

        return redirect()
            ->route('admin.manager-applications.index')
            ->with('success', 'Application approved. Manager account and school were created.');
    }

    public function reject(RejectSchoolManagerApplicationRequest $request, SchoolManagerApplication $schoolManagerApplication)
    {
        if ($schoolManagerApplication->status !== 'pending') {
            return back()->with('error', 'This application has already been reviewed.');
        }

        $validated = $request->validated();

        $schoolManagerApplication->update([
            'status' => 'rejected',
            'adminReason' => $validated['adminReason'],
            'reviewedBy' => auth()->id(),
            'reviewedAt' => now(),
        ]);

        return redirect()
            ->route('admin.manager-applications.index')
            ->with('success', 'Application rejected.');
    }
}
