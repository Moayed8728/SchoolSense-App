<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiReasoningService
{
    public function generateText(string $prompt): string
    {
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.reasoning_model', 'gemini-2.0-flash');

        if (!$apiKey) {
            throw new RuntimeException('Missing GEMINI_API_KEY.');
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $response = Http::timeout(45)
            ->retry(2, 700)
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
                    'maxOutputTokens' => 1200,
                ],
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('Gemini reasoning failed: ' . $response->body());
        }

        $text = $response->json('candidates.0.content.parts.0.text');

        if (!is_string($text) || trim($text) === '') {
            throw new RuntimeException('Gemini returned empty reasoning response.');
        }

        return trim($text);
    }
}
