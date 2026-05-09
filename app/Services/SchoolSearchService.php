<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SchoolSearchService
{
    private const DEFAULT_MIN_SIMILARITY = 0.62;

    public function search(array $filters): array
    {
        $queryText = trim($filters['query'] ?? '');

        if ($queryText === '') {
            return [];
        }

        $embedding = app(GeminiEmbeddingService::class)->embed($queryText);

        $vector = '[' . implode(',', $embedding) . ']';

        $sql = '
            SELECT *
            FROM (
                SELECT
                    schools.*,
                    (school_embeddings.embedding <=> ?::vector) AS distance,
                    (1 - (school_embeddings.embedding <=> ?::vector)) AS semantic_similarity,
                    CASE
                        WHEN LOWER(schools.name) = LOWER(?) THEN 1.0
                        WHEN LOWER(schools.name) LIKE LOWER(?) THEN 0.94
                        WHEN LOWER(?) LIKE CONCAT(\'%\', LOWER(schools.name), \'%\') THEN 0.90
                        ELSE 0
                    END AS name_similarity,
                    GREATEST(
                        (1 - (school_embeddings.embedding <=> ?::vector)),
                        CASE
                            WHEN LOWER(schools.name) = LOWER(?) THEN 1.0
                            WHEN LOWER(schools.name) LIKE LOWER(?) THEN 0.94
                            WHEN LOWER(?) LIKE CONCAT(\'%\', LOWER(schools.name), \'%\') THEN 0.90
                            ELSE 0
                        END
                    ) AS similarity
                FROM schools
                INNER JOIN school_embeddings
                    ON school_embeddings."schoolId" = schools.id
                WHERE schools.deleted_at IS NULL
        ';

        $nameLike = '%' . $queryText . '%';
        $bindings = [
            $vector,
            $vector,
            $queryText,
            $nameLike,
            $queryText,
            $vector,
            $queryText,
            $nameLike,
            $queryText,
        ];

        if (!empty($filters['city'])) {
            $sql .= ' AND LOWER(schools.city) = LOWER(?)';
            $bindings[] = $filters['city'];
        }

        if (!empty($filters['feesMin'])) {
            $sql .= ' AND (schools."feesMax" IS NULL OR schools."feesMax" >= ?)';
            $bindings[] = (int) $filters['feesMin'];
        }

        if (!empty($filters['feesMax'])) {
            $sql .= ' AND (schools."feesMin" IS NULL OR schools."feesMin" <= ?)';
            $bindings[] = (int) $filters['feesMax'];
        }

        $this->whereHasAny($sql, $bindings, 'curriculum_school', 'curriculumId', $filters['curriculumIds'] ?? []);
        $this->whereHasAny($sql, $bindings, 'activity_school', 'activityId', $filters['activityIds'] ?? []);
        $this->whereHasAny($sql, $bindings, 'language_school', 'languageId', $filters['languageIds'] ?? []);

        $sql .= '
            ) AS ranked_schools
            WHERE similarity >= ?
            ORDER BY similarity DESC, distance ASC
            LIMIT ?
        ';

        $bindings[] = (float) ($filters['minSimilarity'] ?? self::DEFAULT_MIN_SIMILARITY);
        $bindings[] = (int) ($filters['limit'] ?? 10);

        return DB::select($sql, $bindings);
    }

    private function whereHasAny(string &$sql, array &$bindings, string $table, string $column, array $ids): void
    {
        $ids = array_values(array_filter($ids));

        if ($ids === []) {
            return;
        }

        $placeholders = implode(', ', array_fill(0, count($ids), '?'));

        $sql .= "
            AND EXISTS (
                SELECT 1 FROM {$table}
                WHERE {$table}.\"schoolId\" = schools.id
                AND {$table}.\"{$column}\" IN ({$placeholders})
            )
        ";

        array_push($bindings, ...$ids);
    }
}
