<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Curriculum;
use App\Models\Language;
use App\Services\SchoolRecommendationExplanationService;
use App\Services\SchoolSearchService;
use App\Support\TaxonomyNameNormalizer;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Throwable;

class SearchController extends Controller
{
    private const MAX_FEE_FILTER = 2147483647;

    public function index(
        Request $request,
        SchoolSearchService $searchService,
        SchoolRecommendationExplanationService $explanationService
    ) {
        $rules = [
            'query' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:255'],
            'feesMin' => ['nullable', 'integer', 'min:0', 'max:' . self::MAX_FEE_FILTER],
            'feesMax' => ['nullable', 'integer', 'min:0', 'max:' . self::MAX_FEE_FILTER, 'gte:feesMin'],
            'curriculumIds' => ['nullable', 'array', 'max:5'],
            'curriculumIds.*' => ['uuid', Rule::exists('curricula', 'id')],
            'activityIds' => ['nullable', 'array', 'max:8'],
            'activityIds.*' => ['uuid', Rule::exists('activities', 'id')],
            'languageIds' => ['nullable', 'array', 'max:5'],
            'languageIds.*' => ['uuid', Rule::exists('languages', 'id')],
        ];

        $messages = [
            'feesMax.gte' => 'Max fees must be greater than or equal to min fees.',
            'feesMin.max' => 'Min fees is too large.',
            'feesMax.max' => 'Max fees is too large.',
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
                'curricula' => $this->taxonomyOptions(Curriculum::class),
                'activities' => $this->taxonomyOptions(Activity::class),
                'languages' => $this->taxonomyOptions(Language::class),
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
            } catch (QueryException $exception) {
                report($exception);
                $searchError = 'Search could not be completed with these filters. Try a smaller fee range and search again.';
            } catch (Throwable $exception) {
                report($exception);
                $searchError = 'AI search could not connect to the embedding service. Check your internet connection and GEMINI_API_KEY, then try again.';
            }
        }

        return view('search.index', [
            'results' => $results,
            'explanations' => $explanations,
            'curricula' => $this->taxonomyOptions(Curriculum::class),
            'activities' => $this->taxonomyOptions(Activity::class),
            'languages' => $this->taxonomyOptions(Language::class),
            'filters' => $filters,
            'searchError' => $searchError,
        ]);
    }

    private function taxonomyOptions(string $modelClass)
    {
        return $modelClass::query()
            ->orderBy('name')
            ->get()
            ->groupBy(fn ($item) => TaxonomyNameNormalizer::key(TaxonomyNameNormalizer::normalize($item->name)))
            ->map(fn ($items) => $items->first(fn ($item) => $item->name === TaxonomyNameNormalizer::normalize($item->name)) ?? $items->first())
            ->sortBy('name')
            ->values();
    }
}
