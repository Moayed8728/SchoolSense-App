<?php


/*SchoolDocumentBuilderService
= prepares the school text

GeminiEmbeddingService
= sends text to Gemini and gets vector*/



namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiEmbeddingService
{
    public function embed(string $text): array
    {
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.embedding_model');
        $dimensions = (int) config('services.gemini.embedding_dimensions', 768);

        if (!$apiKey) {
            throw new RuntimeException('Missing GEMINI_API_KEY.');
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:embedContent";

        $response = Http::timeout(30)
            ->withHeaders([
                'x-goog-api-key' => $apiKey,
            ])
            ->retry(2, 500)
            ->post($url, [
                'model' => "models/{$model}",
                'content' => [
                    'parts' => [
                        ['text' => $text],
                    ],
                ],
                'outputDimensionality' => $dimensions,
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('Gemini embedding failed: ' . $response->body());
        }

        $values = $response->json('embedding.values');

        if (!is_array($values) || empty($values)) {
            throw new RuntimeException('Gemini returned empty embedding.');
        }

        return array_map('floatval', $values);
    }
}
