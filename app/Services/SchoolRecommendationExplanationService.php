<?php

namespace App\Services;

use App\Exceptions\GeminiGenerationException;
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

        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        try {
            $response = app(GeminiReasoningService::class)->generateText($prompt);
            $explanations = $this->parseJsonResponse($response);

            if ($explanations !== []) {
                $explanations['__meta'] = [
                    'status' => 'generated',
                    'message' => null,
                ];

                Cache::put($cacheKey, $explanations, now()->addDay());

                return $explanations;
            }

            return $this->fallbackExplanations(
                $schools,
                'Gemini responded, but the explanation format could not be read.'
            );
        } catch (GeminiGenerationException $e) {
            report($e);

            return $this->fallbackExplanations($schools, $this->messageFor($e));
        } catch (\Throwable $e) {
            report($e);

            return $this->fallbackExplanations(
                $schools,
                'AI explanations could not be generated right now.'
            );
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

        $start = strpos($clean, '[');
        $end = strrpos($clean, ']');

        if ($start !== false && $end !== false && $end > $start) {
            $clean = substr($clean, $start, $end - $start + 1);
        }

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

    private function fallbackExplanations(Collection $schools, ?string $message = null): array
    {
        $usedKeywordFallback = $schools->contains(fn ($school) => (bool) ($school->search_fallback ?? false));

        $fallbacks = $schools->mapWithKeys(function ($school) {
            return [
                $school->id => [
                    'reason' => ($school->search_fallback ?? false)
                        ? 'This school matched your search by keyword because Gemini embeddings are unavailable on this deployment.'
                        : 'This school appeared in the semantic search results based on similarity to your query.',
                    'caution' => null,
                ],
            ];
        })->all();

        $fallbacks['__meta'] = [
            'status' => 'fallback',
            'message' => $usedKeywordFallback
                ? 'Gemini embeddings are unavailable on this deployment, so keyword fallback results are shown.'
                : $message,
        ];

        return $fallbacks;
    }

    private function cacheKey(string $userQuery, Collection $schools): string
    {
        $schoolIds = $schools
            ->map(fn ($school) => $school->id)
            ->implode('|');

        return 'school_search_explanations:' . md5(trim($userQuery) . '|' . $schoolIds);
    }

    private function messageFor(GeminiGenerationException $exception): string
    {
        if (str_contains($exception->getMessage(), 'Missing GEMINI_API_KEY')) {
            return 'Gemini is not configured on this deployment, so fallback text is shown.';
        }

        if ($exception->isQuotaExceeded()) {
            return 'Gemini quota is currently exhausted, so these explanations are fallback text.';
        }

        if ($exception->status() === 401 || $exception->status() === 403) {
            return 'Gemini rejected the API key or permissions, so these explanations are fallback text.';
        }

        if ($exception->status() === 404) {
            return 'The configured Gemini reasoning model was not accepted, so these explanations are fallback text.';
        }

        return 'Gemini could not generate explanations right now, so fallback text is shown.';
    }
}
