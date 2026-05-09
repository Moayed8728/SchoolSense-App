<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Services\SchoolComparisonSummaryService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SchoolComparisonController extends Controller
{
    public function index()
    {
        return view('compare.index', [
            'schools' => School::query()
                ->orderBy('name')
                ->get(),
            'selectedSchools' => collect(),
            'summary' => null,
        ]);
    }

    public function compare(Request $request, SchoolComparisonSummaryService $summaryService)
    {
        $validated = $request->validate([
            'schoolAId' => [
                'required',
                'uuid',
                Rule::exists('schools', 'id')->whereNull('deleted_at'),
            ],
            'schoolBId' => [
                'required',
                'uuid',
                'different:schoolAId',
                Rule::exists('schools', 'id')->whereNull('deleted_at'),
            ],
        ], [
            'schoolAId.required' => 'Choose the first school.',
            'schoolBId.required' => 'Choose the second school.',
            'schoolBId.different' => 'Choose two different schools to compare.',
            'schoolAId.exists' => 'The first school is no longer available.',
            'schoolBId.exists' => 'The second school is no longer available.',
        ], [
            'schoolAId' => 'first school',
            'schoolBId' => 'second school',
        ]);

        $selectedSchools = School::query()
            ->with(['curricula', 'activities', 'languages'])
            ->whereIn('id', [
                $validated['schoolAId'],
                $validated['schoolBId'],
            ])
            ->get()
            ->sortBy(fn ($school) => array_search($school->id, [
                $validated['schoolAId'],
                $validated['schoolBId'],
            ]))
            ->values();

        $summary = $summaryService->summarize(
            $selectedSchools[0],
            $selectedSchools[1]
        );

        return view('compare.index', [
            'schools' => School::query()->orderBy('name')->get(),
            'selectedSchools' => $selectedSchools,
            'summary' => $summary,
        ]);
    }
}
