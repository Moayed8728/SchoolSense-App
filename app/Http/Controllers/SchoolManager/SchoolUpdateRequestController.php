<?php

namespace App\Http\Controllers\SchoolManager;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequestSchoolDeletionRequest;
use App\Http\Requests\UpdateOwnedSchoolRequest;
use App\Models\Activity;
use App\Models\Curriculum;
use App\Models\Language;
use App\Models\School;
use App\Models\SchoolUpdateRequest;
use Illuminate\Support\Facades\DB;

class SchoolUpdateRequestController extends Controller
{
    public function index()
    {
        $requests = SchoolUpdateRequest::with('school')
            ->where('userId', auth()->id())
            ->latest()
            ->paginate(10);

        return view('school-manager.updates.index', compact('requests'));
    }

    public function editSchool(School $school)
    {
        $this->authorizeOwnedSchool($school);

        return view('school-manager.schools.edit', [
            'school' => $school->load('curricula', 'activities', 'languages'),
            'curricula' => Curriculum::orderBy('name')->get(),
            'activities' => Activity::orderBy('name')->get(),
            'languages' => Language::orderBy('name')->get(),
        ]);
    }

    public function updateSchool(UpdateOwnedSchoolRequest $request, School $school)
    {
        $this->authorizeOwnedSchool($school);

        $validated = $request->validated();

        $directFields = ['description', 'contactEmail', 'contactPhone', 'websiteUrl', 'contactPageUrl'];
        $reviewFields = ['name', 'country', 'city', 'feesMin', 'feesMax', 'currency', 'feePeriod', 'curriculumIds', 'activityIds', 'languageIds'];

        $directUpdates = [];
        foreach ($directFields as $field) {
            if (array_key_exists($field, $validated) && $this->isChanged($school->{$field}, $validated[$field])) {
                $directUpdates[$field] = $validated[$field];
            }
        }

        $reviewChanges = [];
        foreach ($reviewFields as $field) {
            if (!array_key_exists($field, $validated)) {
                continue;
            }

            if ($field === 'curriculumIds') {
                if ($this->idsChanged($school->curricula()->pluck('curricula.id')->all(), $validated[$field] ?? [])) {
                    $reviewChanges[$field] = $validated[$field] ?? [];
                }
                continue;
            }

            if ($field === 'activityIds') {
                if ($this->idsChanged($school->activities()->pluck('activities.id')->all(), $validated[$field] ?? [])) {
                    $reviewChanges[$field] = $validated[$field] ?? [];
                }
                continue;
            }

            if ($field === 'languageIds') {
                if ($this->idsChanged($school->languages()->pluck('languages.id')->all(), $validated[$field] ?? [])) {
                    $reviewChanges[$field] = $validated[$field] ?? [];
                }
                continue;
            }

            if ($this->isChanged($school->{$field}, $validated[$field])) {
                $reviewChanges[$field] = $validated[$field];
            }
        }

        if (empty($directUpdates) && empty($reviewChanges)) {
            return back()
                ->withErrors(['changes' => 'Please provide at least one field to update.'])
                ->withInput();
        }

        DB::transaction(function () use ($school, $directUpdates, $reviewChanges) {
            if (!empty($directUpdates)) {
                $school->update($directUpdates);
            }

            if (!empty($reviewChanges)) {
                SchoolUpdateRequest::create([
                    'userId' => auth()->id(),
                    'schoolId' => $school->id,
                    'type' => 'update',
                    'changes' => $reviewChanges,
                    'status' => 'pending',
                ]);
            }
        });

        $message = !empty($directUpdates) && !empty($reviewChanges)
            ? 'Direct fields were saved now. Review fields were submitted for admin approval.'
            : (!empty($directUpdates) ? 'School details saved successfully.' : 'Changes submitted for admin review.');

        return redirect()
            ->route('school-manager.dashboard')
            ->with('success', $message);
    }

    public function requestDeletion(RequestSchoolDeletionRequest $request, School $school)
    {
        $this->authorizeOwnedSchool($school);

        $validated = $request->validated();

        SchoolUpdateRequest::create([
            'userId' => auth()->id(),
            'schoolId' => $school->id,
            'type' => 'delete',
            'changes' => [
                'reason' => $validated['reason'],
            ],
            'status' => 'pending',
        ]);

        return redirect()
            ->route('school-manager.updates.index')
            ->with('success', 'Deletion request submitted for admin review.');
    }

    public function show(string $id)
    {
        $requestItem = SchoolUpdateRequest::with(['school', 'reviewer'])
            ->where('id', $id)
            ->where('userId', auth()->id())
            ->firstOrFail();

        return view('school-manager.updates.show', compact('requestItem'));
    }

    private function authorizeOwnedSchool(School $school): void
    {
        abort_unless($school->ownerUserId === auth()->id(), 403);
    }

    private function isChanged(mixed $current, mixed $incoming): bool
    {
        return (string) ($current ?? '') !== (string) ($incoming ?? '');
    }

    private function idsChanged(array $current, array $incoming): bool
    {
        sort($current);
        sort($incoming);

        return $current !== $incoming;
    }
}
