<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RejectSchoolUpdateRequest;
use App\Models\SchoolUpdateRequest;
use Illuminate\Support\Facades\DB;

class UpdateReviewController extends Controller
{
    public function index()
    {
        $requests = SchoolUpdateRequest::with(['user', 'school'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(12);

        return view('admin.updates.index', compact('requests'));
    }

    public function show(SchoolUpdateRequest $schoolUpdateRequest)
    {
        $schoolUpdateRequest->load('user', 'school', 'reviewer');

        return view('admin.updates.show', [
            'requestItem' => $schoolUpdateRequest,
        ]);
    }

    public function approve(SchoolUpdateRequest $schoolUpdateRequest)
    {
        if ($schoolUpdateRequest->status !== 'pending') {
            return back()->with('error', 'This update request has already been reviewed.');
        }

        DB::transaction(function () use ($schoolUpdateRequest) {
            $school = $schoolUpdateRequest->school;
            $changes = $schoolUpdateRequest->changes ?? [];

            if ($schoolUpdateRequest->type === 'delete') {
                $school->delete();

                $schoolUpdateRequest->update([
                    'status' => 'approved',
                    'reviewedBy' => auth()->id(),
                    'reviewedAt' => now(),
                    'adminReason' => null,
                ]);

                return;
            }

            $mappedUpdates = [
                'name'           => $changes['name'] ?? null,
                'country'        => $changes['country'] ?? null,
                'city'           => $changes['city'] ?? null,
                'address'        => $changes['address'] ?? null,
                'websiteUrl'     => $changes['websiteUrl'] ?? null,
                'contactEmail'   => $changes['contactEmail'] ?? null,
                'contactPhone'   => $changes['contactPhone'] ?? null,
                'contactPageUrl' => $changes['contactPageUrl'] ?? null,
                'description'    => $changes['description'] ?? null,
                'feesMin'        => $changes['feesMin'] ?? null,
                'feesMax'        => $changes['feesMax'] ?? null,
                'currency'       => $changes['currency'] ?? null,
                'feePeriod'      => $changes['feePeriod'] ?? null,
            ];

            $directUpdates = collect($mappedUpdates)
                ->filter(fn ($value) => !is_null($value))
                ->toArray();

            if (!empty($directUpdates)) {
                $school->update($directUpdates);
            }

            if (array_key_exists('curriculumIds', $changes)) {
                $school->curricula()->sync($changes['curriculumIds'] ?? []);
            }

            if (array_key_exists('activityIds', $changes)) {
                $school->activities()->sync($changes['activityIds'] ?? []);
            }

            if (array_key_exists('languageIds', $changes)) {
                $school->languages()->sync($changes['languageIds'] ?? []);
            }

            $schoolUpdateRequest->update([
                'status' => 'approved',
                'reviewedBy' => auth()->id(),
                'reviewedAt' => now(),
                'adminReason' => null,
            ]);
        });

        return redirect()
            ->route('admin.updates.index')
            ->with('success', 'Update request approved and applied successfully.');
    }

    public function reject(RejectSchoolUpdateRequest $request, SchoolUpdateRequest $schoolUpdateRequest)
    {
        if ($schoolUpdateRequest->status !== 'pending') {
            return back()->with('error', 'This update request has already been reviewed.');
        }

        $validated = $request->validated();

        DB::transaction(function () use ($schoolUpdateRequest, $validated) {
            $schoolUpdateRequest->update([
                'status' => 'rejected',
                'reviewedBy' => auth()->id(),
                'reviewedAt' => now(),
                'adminReason' => $validated['adminReason'] ?? null,
            ]);
        });

        return redirect()
            ->route('admin.updates.index')
            ->with('success', 'Update request rejected.');
    }
}
