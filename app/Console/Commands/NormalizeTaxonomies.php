<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\Curriculum;
use App\Models\Language;
use App\Models\SchoolManagerApplication;
use App\Models\SchoolUpdateRequest;
use App\Support\TaxonomyNameNormalizer;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NormalizeTaxonomies extends Command
{
    protected $signature = 'taxonomies:normalize';

    protected $description = 'Clean quoted/newline taxonomy names and merge duplicates into canonical rows';

    public function handle(): int
    {
        $summary = DB::transaction(function () {
            return [
                'curricula' => $this->normalizeModel(Curriculum::class, 'curriculum_school', 'curriculumId', 'curriculumIds'),
                'activities' => $this->normalizeModel(Activity::class, 'activity_school', 'activityId', 'activityIds'),
                'languages' => $this->normalizeModel(Language::class, 'language_school', 'languageId', 'languageIds'),
            ];
        });

        $this->table(
            ['Taxonomy', 'Renamed', 'Merged'],
            collect($summary)->map(fn (array $item, string $name) => [
                $name,
                $item['renamed'],
                $item['merged'],
            ])->all()
        );

        return self::SUCCESS;
    }

    private function normalizeModel(string $modelClass, string $pivotTable, string $pivotColumn, string $jsonField): array
    {
        /** @var Collection<int, Model> $items */
        $items = $modelClass::query()->orderBy('name')->get();
        $renamed = 0;
        $merged = 0;

        foreach ($items->groupBy(fn (Model $item) => TaxonomyNameNormalizer::key(
            TaxonomyNameNormalizer::normalize($item->getAttribute('name'))
        )) as $group) {
            $group = $group->filter(fn (Model $item) => TaxonomyNameNormalizer::normalize($item->getAttribute('name')) !== '');

            if ($group->isEmpty()) {
                continue;
            }

            $canonicalName = TaxonomyNameNormalizer::normalize($group->first()->getAttribute('name'));
            $canonical = $this->canonicalItem($group, $canonicalName);

            if ($canonical->getAttribute('name') !== $canonicalName) {
                $canonical->forceFill([
                    'name' => $canonicalName,
                    'slug' => $this->uniqueSlug($modelClass, $canonicalName, $canonical->getKey()),
                ])->save();
                $renamed++;
            }

            foreach ($group as $duplicate) {
                if ($duplicate->is($canonical)) {
                    continue;
                }

                $this->movePivotRows($pivotTable, $pivotColumn, $duplicate->getKey(), $canonical->getKey());
                $this->replaceIdsInJsonFields($jsonField, $duplicate->getKey(), $canonical->getKey());
                $duplicate->delete();
                $merged++;
            }
        }

        return compact('renamed', 'merged');
    }

    private function canonicalItem(Collection $group, string $canonicalName): Model
    {
        return $group->first(fn (Model $item) => $item->getAttribute('name') === $canonicalName)
            ?? $group->first(fn (Model $item) => $item->getAttribute('slug') === TaxonomyNameNormalizer::slug($canonicalName))
            ?? $group->first();
    }

    private function movePivotRows(string $pivotTable, string $pivotColumn, string $fromId, string $toId): void
    {
        $rows = DB::table($pivotTable)
            ->where($pivotColumn, $fromId)
            ->get(['schoolId', 'created_at', 'updated_at'])
            ->map(fn ($row) => [
                'schoolId' => $row->schoolId,
                $pivotColumn => $toId,
                'created_at' => $row->created_at,
                'updated_at' => now(),
            ])
            ->all();

        if ($rows !== []) {
            DB::table($pivotTable)->insertOrIgnore($rows);
        }

        DB::table($pivotTable)->where($pivotColumn, $fromId)->delete();
    }

    private function replaceIdsInJsonFields(string $field, string $fromId, string $toId): void
    {
        SchoolManagerApplication::query()
            ->whereJsonContains($field, $fromId)
            ->each(function (SchoolManagerApplication $application) use ($field, $fromId, $toId) {
                $application->forceFill([
                    $field => $this->replaceIdList($application->getAttribute($field) ?? [], $fromId, $toId),
                ])->save();
            });

        SchoolUpdateRequest::query()
            ->each(function (SchoolUpdateRequest $request) use ($field, $fromId, $toId) {
                $changes = $request->changes ?? [];

                if (!array_key_exists($field, $changes)) {
                    return;
                }

                $changes[$field] = $this->replaceIdList($changes[$field] ?? [], $fromId, $toId);
                $request->forceFill(['changes' => $changes])->save();
            });
    }

    private function replaceIdList(array $ids, string $fromId, string $toId): array
    {
        return collect($ids)
            ->map(fn ($id) => $id === $fromId ? $toId : $id)
            ->unique()
            ->values()
            ->all();
    }

    private function uniqueSlug(string $modelClass, string $name, string $exceptId): string
    {
        $baseSlug = TaxonomyNameNormalizer::slug($name);
        $slug = $baseSlug;
        $suffix = 2;

        while ($modelClass::query()
            ->where('slug', $slug)
            ->whereKeyNot($exceptId)
            ->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
