<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolSubmissionRequest;
use Illuminate\Support\Facades\DB;

class SubmissionController extends Controller
{

    public function approve(SchoolSubmissionRequest $submission)
    {

        DB::transaction(function () use ($submission) {

            $school = School::create([
                'name' => $submission->name,
                'country' => $submission->country,
                'city' => $submission->city,
                'address' => $submission->address,
                'websiteUrl' => $submission->websiteUrl,
                'contactEmail' => $submission->contactEmail,
                'contactPhone' => $submission->contactPhone,
                'contactPageUrl' => $submission->contactPageUrl,
                'description' => $submission->description,
                'feesMin' => $submission->feesMin,
                'feesMax' => $submission->feesMax,
                'currency' => $submission->currency,
                'feePeriod' => $submission->feePeriod,

                // IMPORTANT
                'ownerUserId' => $submission->userId
            ]);

            if ($submission->curriculumIds) {
                $school->curricula()->sync($submission->curriculumIds);
            }

            if ($submission->activityIds) {
                $school->activities()->sync($submission->activityIds);
            }

            if ($submission->languageIds) {
                $school->languages()->sync($submission->languageIds);
            }

            $submission->update([
                'status' => 'approved',
                'reviewedBy' => auth()->id(),
                'reviewedAt' => now()
            ]);

        });

        return back()->with('success','School approved and created.');
    }

}