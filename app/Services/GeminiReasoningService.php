<?php

namespace App\Services;

use App\Exceptions\GeminiGenerationException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiReasoningService
{
    public function generateText(string $prompt): string
    {
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.reasoning_model', 'gemini-2.5-flash');

        if (!$apiKey) {
            throw new RuntimeException('Missing GEMINI_API_KEY.');
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        try {
            $response = Http::timeout(45)
                ->connectTimeout(10)
                ->post($url, [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.2,
                        'maxOutputTokens' => 3000,
                    ],
                ]);
        } catch (ConnectionException $exception) {
            throw new GeminiGenerationException(
                "Gemini reasoning could not connect using {$model}.",
                null,
                $exception->getMessage()
            );
        }

        if (!$response->successful()) {
            $status = $response->status();
            $message = $response->json('error.message') ?: 'Gemini reasoning request failed.';

            throw new GeminiGenerationException(
                "Gemini reasoning failed ({$status}) using {$model}: {$message}",
                $status,
                $response->body()
            );
        }

        $text = $response->json('candidates.0.content.parts.0.text');

        if (!is_string($text) || trim($text) === '') {
            throw new GeminiGenerationException('Gemini returned empty reasoning response.');
        }

        return trim($text);
    }
}
