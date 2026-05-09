<?php

//It does NOT build the document itself.
// It does NOT directly generate embeddings itself.
// Instead, it coordinates other services. 

namespace App\Services;

use App\Models\School;
use App\Models\SchoolDocument;
use App\Models\SchoolEmbedding;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class SchoolEmbeddingService
{
    public function __construct(
        private SchoolDocumentBuilderService $documentBuilder,
        private GeminiEmbeddingService $embeddingService
    ) {
    }

    public function embed(School $school, bool $force = false): bool
    {
        $school->loadMissing(['curricula', 'activities', 'languages', 'feeBands']);

        $content = $this->documentBuilder->build($school);
        $contentHash = $this->documentBuilder->hash($content);

        $document = SchoolDocument::updateOrCreate(
            ['schoolId' => $school->id],
            [
                'content' => $content,
                'contentHash' => $contentHash,
                'generatedAt' => now(),
            ]
        );

        $existingEmbedding = SchoolEmbedding::where('schoolId', $school->id)->first();

        if (!$force && $existingEmbedding && $existingEmbedding->contentHash === $contentHash) {
            return false;
        }

        $embedding = $this->embeddingService->embed($content);
        $expectedDimensions = (int) config('services.gemini.embedding_dimensions', 768);

        if (count($embedding) !== $expectedDimensions) {
            throw new RuntimeException(
                'Embedding dimensions are ' . count($embedding) . ", expected {$expectedDimensions}."
            );
        }

        DB::statement(
            <<<'SQL'
            INSERT INTO school_embeddings (
                id,
                "schoolId",
                "documentId",
                model,
                dimensions,
                "contentHash",
                "embeddedAt",
                embedding,
                created_at,
                updated_at
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?::vector, ?, ?)
            ON CONFLICT ("schoolId") DO UPDATE SET
                "documentId" = EXCLUDED."documentId",
                model = EXCLUDED.model,
                dimensions = EXCLUDED.dimensions,
                "contentHash" = EXCLUDED."contentHash",
                "embeddedAt" = EXCLUDED."embeddedAt",
                embedding = EXCLUDED.embedding,
                updated_at = EXCLUDED.updated_at
            SQL,
            [
                (string) Str::uuid(),
                $school->id,
                $document->id,
                config('services.gemini.embedding_model'),
                count($embedding),
                $contentHash,
                now(),
                '[' . implode(',', $embedding) . ']',
                now(),
                now(),
            ]
        );

        return true;
    }
}
