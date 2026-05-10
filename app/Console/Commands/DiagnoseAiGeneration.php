<?php

namespace App\Console\Commands;

use App\Exceptions\GeminiGenerationException;
use App\Services\GeminiReasoningService;
use Illuminate\Console\Command;
use Throwable;

class DiagnoseAiGeneration extends Command
{
    protected $signature = 'ai:diagnose {--live : Send a tiny Gemini generation request}';

    protected $description = 'Check SchoolSense AI generation configuration and optionally make a live Gemini request.';

    public function handle(): int
    {
        $apiKey = config('services.gemini.api_key');
        $reasoningModel = config('services.gemini.reasoning_model');
        $embeddingModel = config('services.gemini.embedding_model');

        $this->line('Gemini generation configuration');
        $this->line('API key: ' . ($apiKey ? 'present' : 'missing'));
        $this->line('Reasoning model: ' . $reasoningModel);
        $this->line('Embedding model: ' . $embeddingModel);

        if (!$apiKey) {
            $this->error('GEMINI_API_KEY is missing.');

            return self::FAILURE;
        }

        if (!$this->option('live')) {
            $this->info('Config looks present. Run with --live to test the generation endpoint.');

            return self::SUCCESS;
        }

        try {
            $text = app(GeminiReasoningService::class)->generateText(
                'Reply with exactly this JSON and nothing else: {"ok":true}'
            );

            $this->info('Live Gemini generation succeeded.');
            $this->line($text);

            return self::SUCCESS;
        } catch (GeminiGenerationException $exception) {
            $this->error($exception->getMessage());

            if ($exception->isQuotaExceeded()) {
                $this->warn('This is a quota/billing/rate-limit problem with the configured Gemini API key.');
            }

            return self::FAILURE;
        } catch (Throwable $exception) {
            $this->error('AI generation check failed: ' . $exception->getMessage());

            return self::FAILURE;
        }
    }
}
