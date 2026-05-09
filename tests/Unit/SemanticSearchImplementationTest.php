<?php

use App\Services\GeminiEmbeddingService;
use App\Services\SchoolSearchService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class);

it('uses metadata-filtered vector retrieval with a relevance cutoff', function () {
    $service = file_get_contents(__DIR__ . '/../../app/Services/SchoolSearchService.php');

    expect($service)
        ->toContain('school_embeddings.embedding <=> ?::vector')
        ->toContain('WHERE schools.deleted_at IS NULL')
        ->toContain('name_similarity')
        ->toContain('GREATEST(')
        ->toContain('WHERE similarity >= ?')
        ->toContain('LOWER(schools.city) = LOWER(?)')
        ->toContain('schools."feesMax" IS NULL OR schools."feesMax" >= ?')
        ->toContain('schools."feesMin" IS NULL OR schools."feesMin" <= ?')
        ->toContain('curriculum_school')
        ->toContain('activity_school')
        ->toContain('language_school')
        ->toContain('ORDER BY similarity DESC, distance ASC')
        ->not->toContain('gender');
});

it('builds a metadata-filtered pgvector search query with the expected bindings', function () {
    $gemini = Mockery::mock(GeminiEmbeddingService::class);
    $gemini->shouldReceive('embed')
        ->once()
        ->with('British STEM school')
        ->andReturn([0.1, 0.2, 0.3]);

    app()->instance(GeminiEmbeddingService::class, $gemini);

    DB::shouldReceive('select')
        ->once()
        ->withArgs(function (string $sql, array $bindings) {
            expect($sql)
                ->toContain('school_embeddings.embedding <=> ?::vector')
                ->toContain('WHERE schools.deleted_at IS NULL')
                ->toContain('LOWER(schools.name) LIKE LOWER(?)')
                ->toContain('WHERE similarity >= ?')
                ->toContain('LOWER(schools.city) = LOWER(?)')
                ->toContain('schools."feesMax" IS NULL OR schools."feesMax" >= ?')
                ->toContain('schools."feesMin" IS NULL OR schools."feesMin" <= ?')
                ->toContain('curriculum_school."curriculumId" IN (?, ?)')
                ->toContain('activity_school."activityId" IN (?)')
                ->toContain('language_school."languageId" IN (?)')
                ->toContain('ORDER BY similarity DESC, distance ASC')
                ->not->toContain('gender');

            expect($bindings)->toBe([
                '[0.1,0.2,0.3]',
                '[0.1,0.2,0.3]',
                'British STEM school',
                '%British STEM school%',
                'British STEM school',
                '[0.1,0.2,0.3]',
                'British STEM school',
                '%British STEM school%',
                'British STEM school',
                'Jeddah',
                10000,
                40000,
                'curriculum-1',
                'curriculum-2',
                'activity-1',
                'language-1',
                0.62,
                5,
            ]);

            return true;
        })
        ->andReturn([
            (object) ['id' => 'school-1', 'similarity' => 0.82],
        ]);

    $results = app(SchoolSearchService::class)->search([
        'query' => 'British STEM school',
        'city' => 'Jeddah',
        'feesMin' => 10000,
        'feesMax' => 40000,
        'curriculumIds' => ['curriculum-1', 'curriculum-2'],
        'activityIds' => ['activity-1'],
        'languageIds' => ['language-1'],
        'limit' => 5,
    ]);

    expect($results)
        ->toHaveCount(1)
        ->and($results[0]->id)->toBe('school-1');
});

it('does not call Gemini or the database for an empty query', function () {
    $gemini = Mockery::mock(GeminiEmbeddingService::class);
    $gemini->shouldNotReceive('embed');
    app()->instance(GeminiEmbeddingService::class, $gemini);

    DB::shouldReceive('select')->never();

    expect(app(SchoolSearchService::class)->search(['query' => '   ']))->toBe([]);
});

it('keeps the search page aligned with the supported filters', function () {
    $view = file_get_contents(__DIR__ . '/../../resources/views/search/index.blade.php');

    expect($view)
        ->toContain('name="query"')
        ->toContain('name="city"')
        ->toContain('name="feesMin"')
        ->toContain('name="feesMax"')
        ->toContain('name="curriculumIds[]"')
        ->toContain('name="activityIds[]"')
        ->toContain('name="languageIds[]"')
        ->toContain('Relevance')
        ->not->toContain('name="gender"')
        ->not->toContain('$school->gender');
});
