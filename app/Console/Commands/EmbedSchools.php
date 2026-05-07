<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Services\SchoolEmbeddingService;
use Illuminate\Console\Command;

class EmbedSchools extends Command
{
    protected $signature = 'schools:embed
                            {schoolId? : Optional school UUID}
                            {--force : Re-embed even if content hash did not change}
                            {--limit=50 : Max schools when no schoolId is provided}';

    protected $description = 'Generate school documents and Gemini embeddings for RAG search';

    public function handle(SchoolEmbeddingService $schoolEmbeddingService): int
    {
        $schoolId = $this->argument('schoolId');

        $query = School::query()
            ->with(['curricula', 'activities', 'languages', 'feeBands'])
            ->orderBy('name');

        if ($schoolId) {
            $query->where('id', $schoolId);
        } else {
            $query->limit((int) $this->option('limit'));
        }

        $schools = $query->get();

        if ($schools->isEmpty()) {
            $this->warn('No schools found.');
            return self::SUCCESS;
        }

        foreach ($schools as $school) {
            $this->embedSchool($school, $schoolEmbeddingService);
        }

        return self::SUCCESS;
    }

    private function embedSchool(School $school, SchoolEmbeddingService $schoolEmbeddingService): void
    {
        $this->line('');
        $this->info("Processing: {$school->name}");

        $embedded = $schoolEmbeddingService->embed($school, (bool) $this->option('force'));

        if (!$embedded) {
            $this->comment('Skipped: content unchanged.');
            return;
        }

        $this->info('Embedded successfully.');
    }
}
