<?php

namespace App\Services;

use App\Exceptions\GeminiGenerationException;
use App\Models\School;
use Illuminate\Support\Facades\Cache;

class SchoolComparisonSummaryService
{
    public function summarize(School $schoolA, School $schoolB): array
    {
        $prompt = $this->buildPrompt($schoolA, $schoolB);
        $cacheKey = $this->cacheKey($schoolA, $schoolB);

        try {
            if ($cached = Cache::get($cacheKey)) {
                return $cached;
            }

            $response = app(GeminiReasoningService::class)->generateText($prompt);
            $summary = [
                ...$this->parseJsonResponse($response),
                'status' => 'generated',
                'message' => null,
            ];

            Cache::put($cacheKey, $summary, now()->addDay());

            return $summary;
        } catch (GeminiGenerationException $e) {
            report($e);

            return $this->unavailableSummary($this->messageFor($e));
        } catch (\Throwable $e) {
            report($e);

            return $this->unavailableSummary($this->messageForThrowable($e));
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
Keep every field concise so the full response is complete.
Use 1 overview sentence, 2 strengths per school, 2 tradeoffs, and 1 best-fit sentence.

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
        $clean = $this->extractJsonObject($response);

        $decoded = json_decode($clean, true);

        if (is_string($decoded)) {
            $decoded = json_decode($this->extractJsonObject($decoded), true);
        }

        if (!is_array($decoded)) {
            throw new GeminiGenerationException('Gemini returned a comparison response that could not be formatted.');
        }

        return [
            'overview' => is_string($decoded['overview'] ?? null) ? $decoded['overview'] : null,
            'schoolAStrengths' => $this->listFrom($decoded['schoolAStrengths'] ?? []),
            'schoolBStrengths' => $this->listFrom($decoded['schoolBStrengths'] ?? []),
            'tradeoffs' => $this->listFrom($decoded['tradeoffs'] ?? []),
            'bestFit' => is_string($decoded['bestFit'] ?? null) ? $decoded['bestFit'] : null,
        ];
    }

    private function extractJsonObject(string $response): string
    {
        $clean = trim($response);
        $clean = preg_replace('/^```(?:json)?\s*/i', '', $clean);
        $clean = preg_replace('/\s*```$/', '', $clean);
        $clean = stripcslashes($clean);

        $start = strpos($clean, '{');
        $end = strrpos($clean, '}');

        if ($start !== false && $end !== false && $end > $start) {
            return substr($clean, $start, $end - $start + 1);
        }

        return $clean;
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

    private function unavailableSummary(string $message): array
    {
        return [
            'status' => 'unavailable',
            'message' => $message,
            'overview' => null,
            'schoolAStrengths' => [],
            'schoolBStrengths' => [],
            'tradeoffs' => [],
            'bestFit' => null,
        ];
    }

    private function messageFor(GeminiGenerationException $exception): string
    {
        if (str_contains($exception->getMessage(), 'Missing GEMINI_API_KEY')) {
            return 'Gemini is not configured on this deployment. Add GEMINI_API_KEY to the deployed environment variables, then redeploy or restart the service.';
        }

        if ($exception->isQuotaExceeded()) {
            return 'Gemini quota is currently exhausted. The selected school data is still shown below.';
        }

        if ($exception->status() === 401 || $exception->status() === 403) {
            return 'Gemini rejected the API key or permissions. The selected school data is still shown below.';
        }

        if ($exception->status() === 404) {
            return 'The configured Gemini reasoning model was not accepted. The selected school data is still shown below.';
        }

        return 'AI comparison could not be generated right now. The selected school data is still shown below.';
    }

    private function messageForThrowable(\Throwable $exception): string
    {
        if (str_contains($exception->getMessage(), 'Missing GEMINI_API_KEY')) {
            return 'Gemini is not configured on this deployment. Add GEMINI_API_KEY to the deployed environment variables, then redeploy or restart the service.';
        }

        return 'AI comparison could not be generated right now. The selected school data is still shown below.';
    }
}
