<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\GeminiGenerationException;
use App\Http\Controllers\Controller;
use App\Models\SchoolEmbedding;
use App\Services\GeminiEmbeddingService;
use App\Services\GeminiReasoningService;
use Throwable;

class AiDiagnosticsController extends Controller
{
    public function __invoke()
    {
        $apiKey = config('services.gemini.api_key');

        return view('admin.ai-diagnostics', [
            'config' => [
                'apiKey' => $apiKey ? 'present' : 'missing',
                'apiKeyLength' => $apiKey ? strlen($apiKey) : 0,
                'reasoningModel' => config('services.gemini.reasoning_model'),
                'embeddingModel' => config('services.gemini.embedding_model'),
                'embeddingDimensions' => config('services.gemini.embedding_dimensions'),
                'storedEmbeddings' => SchoolEmbedding::count(),
            ],
            'generation' => $this->checkGeneration($apiKey),
            'embedding' => $this->checkEmbedding($apiKey),
        ]);
    }

    private function checkGeneration(?string $apiKey): array
    {
        if (!$apiKey) {
            return $this->result('failed', 'GEMINI_API_KEY is missing on this deployment.');
        }

        try {
            $text = app(GeminiReasoningService::class)->generateText(
                'Reply with exactly this JSON and nothing else: {"ok":true}'
            );

            return $this->result('passed', 'Gemini generation connected successfully.', $text);
        } catch (GeminiGenerationException $exception) {
            return $this->result('failed', $exception->getMessage());
        } catch (Throwable $exception) {
            return $this->result('failed', 'Generation check failed: ' . $exception->getMessage());
        }
    }

    private function checkEmbedding(?string $apiKey): array
    {
        if (!$apiKey) {
            return $this->result('failed', 'GEMINI_API_KEY is missing on this deployment.');
        }

        try {
            $embedding = app(GeminiEmbeddingService::class)->embed('school search diagnostic');

            return $this->result('passed', 'Gemini embeddings connected successfully.', count($embedding) . ' dimensions returned.');
        } catch (Throwable $exception) {
            return $this->result('failed', 'Embedding check failed: ' . $exception->getMessage());
        }
    }

    private function result(string $status, string $message, ?string $detail = null): array
    {
        return compact('status', 'message', 'detail');
    }
}
