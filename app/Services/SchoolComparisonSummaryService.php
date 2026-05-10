<?php

namespace App\Services;

use App\Models\School;
use Illuminate\Support\Facades\Cache;

class SchoolComparisonSummaryService
{
    public function summarize(School $schoolA, School $schoolB): array
    {
        $prompt = $this->buildPrompt($schoolA, $schoolB);
        $cacheKey = $this->cacheKey($schoolA, $schoolB);

        try {
            return Cache::remember($cacheKey, now()->addDay(), function () use ($prompt) {
                $response = app(GeminiReasoningService::class)->generateText($prompt);

                return [
                    ...$this->parseJsonResponse($response),
                    'status' => 'generated',
                    'message' => null,
                ];
            });
        } catch (\Throwable $e) {
            report($e);

            return [
                'status' => 'unavailable',
                'message' => 'AI comparison could not be generated right now. The selected school data is still shown below.',
                'overview' => null,
                'schoolAStrengths' => [],
                'schoolBStrengths' => [],
                'tradeoffs' => [],
                'bestFit' => null,
            ];
        }
    }

    private function buildPrompt(School $schoolA, School $schoolB): string
    {
        $a = $this->schoolBlock($schoolA);
        $b = $this->schoolBlock($schoolB);

        return <<<PROMPT
You are an assistant for SchoolSense, a school discovery platform.

Compare the two schools using ONLY the provided data.
Do not invent facts.
If information is missing, say it is not specified.
Keep the answer useful for parents.

Return JSON only in this exact format:
{
  "overview": "short overall comparison",
  "schoolAStrengths": ["point 1", "point 2"],
  "schoolBStrengths": ["point 1", "point 2"],
  "tradeoffs": ["tradeoff 1", "tradeoff 2"],
  "bestFit": "short practical recommendation"
}

School A:
{$a}

School B:
{$b}
PROMPT;
    }

    private function schoolBlock(School $school): string
    {
        $school->loadMissing(['curricula', 'activities', 'languages']);

        $curricula = $school->curricula->pluck('name')->implode(', ') ?: 'Not specified';
        $activities = $school->activities->pluck('name')->implode(', ') ?: 'Not specified';
        $languages = $school->languages->pluck('name')->implode(', ') ?: 'Not specified';

        $fees = 'Not specified';

        if ($school->feesMin && $school->feesMax) {
            $fees = "{$school->feesMin} - {$school->feesMax} {$school->currency} per {$school->feePeriod}";
        } elseif ($school->feesMin) {
            $fees = "From {$school->feesMin} {$school->currency} per {$school->feePeriod}";
        } elseif ($school->feesMax) {
            $fees = "Up to {$school->feesMax} {$school->currency} per {$school->feePeriod}";
        }

        return <<<TEXT
ID: {$school->id}
Name: {$school->name}
Country: {$school->country}
City: {$school->city}
Address: {$school->address}
Website: {$school->websiteUrl}
Contact Email: {$school->contactEmail}
Contact Phone: {$school->contactPhone}
Description: {$school->description}
Curricula: {$curricula}
Activities: {$activities}
Languages: {$languages}
Fees: {$fees}
TEXT;
    }

    private function parseJsonResponse(string $response): array
    {
        $clean = trim($response);

        $clean = preg_replace('/^```json\s*/i', '', $clean);
        $clean = preg_replace('/^```\s*/i', '', $clean);
        $clean = preg_replace('/\s*```$/', '', $clean);

        $decoded = json_decode($clean, true);

        if (!is_array($decoded)) {
            return [
                'overview' => $response,
                'schoolAStrengths' => [],
                'schoolBStrengths' => [],
                'tradeoffs' => [],
                'bestFit' => null,
            ];
        }

        return [
            'overview' => $decoded['overview'] ?? null,
            'schoolAStrengths' => $this->listFrom($decoded['schoolAStrengths'] ?? []),
            'schoolBStrengths' => $this->listFrom($decoded['schoolBStrengths'] ?? []),
            'tradeoffs' => $this->listFrom($decoded['tradeoffs'] ?? []),
            'bestFit' => is_string($decoded['bestFit'] ?? null) ? $decoded['bestFit'] : null,
        ];
    }

    private function listFrom(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, is_string(...)));
    }

    private function cacheKey(School $schoolA, School $schoolB): string
    {
        return 'school_comparison_summary:' . md5($schoolA->id . '|' . $schoolB->id);
    }
}
