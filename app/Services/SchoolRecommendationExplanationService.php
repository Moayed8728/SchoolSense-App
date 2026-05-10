<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class SchoolRecommendationExplanationService
{
    public function explain(string $userQuery, array|Collection $schools): array
    {
        $schools = collect($schools)->take(5)->values();

        if ($schools->isEmpty() || trim($userQuery) === '') {
            return [];
        }

        $prompt = $this->buildPrompt($userQuery, $schools);
        $cacheKey = $this->cacheKey($userQuery, $schools);

        try {
            return Cache::remember($cacheKey, now()->addDay(), function () use ($prompt, $schools) {
                $response = app(GeminiReasoningService::class)->generateText($prompt);
                $explanations = $this->parseJsonResponse($response);

                return $explanations === [] ? $this->fallbackExplanations($schools) : $explanations;
            });
        } catch (\Throwable $e) {
            report($e);

            return $this->fallbackExplanations($schools);
        }
    }

    private function buildPrompt(string $userQuery, Collection $schools): string
    {
        $schoolBlocks = $schools->map(function ($school, int $index) {
            $rank = $index + 1;

            return <<<TEXT
School {$rank}
ID: {$school->id}
Name: {$school->name}
Country: {$school->country}
City: {$school->city}
Address: {$school->address}
Description: {$school->description}
Fees Min: {$school->feesMin}
Fees Max: {$school->feesMax}
Currency: {$school->currency}
Fee Period: {$school->feePeriod}
Contact Email: {$school->contactEmail}
Contact Phone: {$school->contactPhone}
Semantic Similarity: {$school->similarity}
TEXT;
        })->implode("\n\n");

        return <<<PROMPT
You are an assistant for SchoolSense, a school discovery platform.

User search query:
"{$userQuery}"

You are given ONLY the retrieved school data below.
Use ONLY this data.
Do not invent facts.
Do not mention missing data as if you know it.
If a detail is not provided, say it is not specified.

Task:
For each school, write a short explanation of why it may match the user's search.
Also include one short caution/tradeoff if useful.

Return JSON only in this exact format:
[
  {
    "schoolId": "uuid",
    "reason": "short reason",
    "caution": "short caution or null"
  }
]

Retrieved schools:
{$schoolBlocks}
PROMPT;
    }

    private function parseJsonResponse(string $response): array
    {
        $clean = trim($response);

        $clean = preg_replace('/^```json\s*/i', '', $clean);
        $clean = preg_replace('/^```\s*/i', '', $clean);
        $clean = preg_replace('/\s*```$/', '', $clean);

        $decoded = json_decode($clean, true);

        if (!is_array($decoded)) {
            return [];
        }

        $explanations = [];

        foreach ($decoded as $item) {
            if (!isset($item['schoolId'])) {
                continue;
            }

            $explanations[$item['schoolId']] = [
                'reason' => $item['reason'] ?? null,
                'caution' => $item['caution'] ?? null,
            ];
        }

        return $explanations;
    }

    private function fallbackExplanations(Collection $schools): array
    {
        return $schools->mapWithKeys(function ($school) {
            return [
                $school->id => [
                    'reason' => 'This school appeared in the semantic search results based on similarity to your query.',
                    'caution' => null,
                ],
            ];
        })->all();
    }

    private function cacheKey(string $userQuery, Collection $schools): string
    {
        $schoolIds = $schools
            ->map(fn ($school) => $school->id)
            ->implode('|');

        return 'school_search_explanations:' . md5(trim($userQuery) . '|' . $schoolIds);
    }
}
