<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Curriculum;
use App\Models\Language;
use App\Services\SchoolSearchService;
use Illuminate\Http\Request;
use Throwable;

class SearchController extends Controller
{
    public function index(Request $request, SchoolSearchService $searchService)
    {
        $filters = $request->validate([
            'query' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:255'],
            'feesMin' => ['nullable', 'integer', 'min:0'],
            'feesMax' => ['nullable', 'integer', 'min:0'],
            'curriculumIds' => ['nullable', 'array'],
            'curriculumIds.*' => ['uuid', 'exists:curricula,id'],
            'activityIds' => ['nullable', 'array'],
            'activityIds.*' => ['uuid', 'exists:activities,id'],
            'languageIds' => ['nullable', 'array'],
            'languageIds.*' => ['uuid', 'exists:languages,id'],
        ]);

        $results = [];
        $searchError = null;

        if ($request->filled('query')) {
            try {
                $results = $searchService->search([
                    ...$filters,
                    'limit' => 10,
                ]);
            } catch (Throwable $exception) {
                report($exception);
                $searchError = 'AI search could not connect to the embedding service. Check your internet connection and GEMINI_API_KEY, then try again.';
            }
        }

        return view('search.index', [
            'results' => $results,
            'curricula' => Curriculum::orderBy('name')->get(),
            'activities' => Activity::orderBy('name')->get(),
            'languages' => Language::orderBy('name')->get(),
            'filters' => $filters,
            'searchError' => $searchError,
        ]);
    }
}
