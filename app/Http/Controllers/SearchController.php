<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Curriculum;
use App\Models\Language;
use App\Services\SchoolRecommendationExplanationService;
use App\Services\SchoolSearchService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Throwable;

class SearchController extends Controller
{
    public function index(
        Request $request,
        SchoolSearchService $searchService,
        SchoolRecommendationExplanationService $explanationService
    ) {
        $rules = [
            'query' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:255'],
            'feesMin' => ['nullable', 'integer', 'min:0'],
            'feesMax' => ['nullable', 'integer', 'min:0', 'gte:feesMin'],
            'curriculumIds' => ['nullable', 'array', 'max:5'],
            'curriculumIds.*' => ['uuid', Rule::exists('curricula', 'id')],
            'activityIds' => ['nullable', 'array', 'max:8'],
            'activityIds.*' => ['uuid', Rule::exists('activities', 'id')],
            'languageIds' => ['nullable', 'array', 'max:5'],
            'languageIds.*' => ['uuid', Rule::exists('languages', 'id')],
        ];

        $messages = [
            'feesMax.gte' => 'Max fees must be greater than or equal to min fees.',
            'curriculumIds.max' => 'Choose up to 5 curricula.',
            'activityIds.max' => 'Choose up to 8 activities.',
            'languageIds.max' => 'Choose up to 5 languages.',
        ];

        $attributes = [
            'query' => 'search query',
            'feesMin' => 'min fees',
            'feesMax' => 'max fees',
            'curriculumIds' => 'curricula',
            'curriculumIds.*' => 'curriculum',
            'activityIds' => 'activities',
            'activityIds.*' => 'activity',
            'languageIds' => 'languages',
            'languageIds.*' => 'language',
        ];

        $validator = Validator::make($request->query(), $rules, $messages, $attributes);

        if ($validator->fails()) {
            return view('search.index', [
                'results' => [],
                'explanations' => [],
                'curricula' => Curriculum::orderBy('name')->get(),
                'activities' => Activity::orderBy('name')->get(),
                'languages' => Language::orderBy('name')->get(),
                'filters' => $request->only([
                    'query',
                    'city',
                    'feesMin',
                    'feesMax',
                    'curriculumIds',
                    'activityIds',
                    'languageIds',
                ]),
                'searchError' => null,
            ])->withErrors($validator);
        }

        $filters = $validator->validated();

        $results = [];
        $explanations = [];
        $searchError = null;

        if ($request->filled('query')) {
            try {
                $results = $searchService->search([
                    ...$filters,
                    'limit' => 10,
                ]);

                $explanations = $explanationService->explain(
                    $filters['query'],
                    $results
                );
            } catch (Throwable $exception) {
                report($exception);
                $searchError = 'AI search could not connect to the embedding service. Check your internet connection and GEMINI_API_KEY, then try again.';
            }
        }

        return view('search.index', [
            'results' => $results,
            'explanations' => $explanations,
            'curricula' => Curriculum::orderBy('name')->get(),
            'activities' => Activity::orderBy('name')->get(),
            'languages' => Language::orderBy('name')->get(),
            'filters' => $filters,
            'searchError' => $searchError,
        ]);
    }
}
