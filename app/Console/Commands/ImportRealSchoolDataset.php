<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\Curriculum;
use App\Models\Language;
use App\Models\School;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SplFileObject;
use Throwable;

class ImportRealSchoolDataset extends Command
{
    protected $signature = 'schools:import-real-dataset
                            {--file= : CSV file path relative to the project root, or an absolute path}
                            {--replace : After import, soft-delete schools that are not present in the CSV by name + city + country}';

    protected $description = 'Import real school data from a CSV file into schools and related taxonomy tables';

    private const REQUIRED_COLUMNS = [
        'name',
        'country',
        'city',
        'address',
        'websiteUrl',
        'sourceUrl',
        'description',
        'feesMin',
        'feesMax',
        'currency',
        'feePeriod',
        'curricula',
        'activities',
        'languages',
    ];

    public function handle(): int
    {
        $filePath = $this->resolveFilePath((string) $this->option('file'));

        if (!$filePath) {
            $this->error('Missing required --file option.');
            return self::FAILURE;
        }

        if (!is_file($filePath) || !is_readable($filePath)) {
            $this->error("CSV file does not exist or is not readable: {$filePath}");
            return self::FAILURE;
        }

        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $removed = 0;
        $errors = [];
        $importKeys = [];

        try {
            $csv = new SplFileObject($filePath);
            $csv->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);
            $csv->setCsvControl(',');

            $header = $this->readHeader($csv);
            $missingColumns = array_values(array_diff(self::REQUIRED_COLUMNS, $header));

            if ($missingColumns !== []) {
                $this->error('CSV is missing required columns: ' . implode(', ', $missingColumns));
                return self::FAILURE;
            }

            foreach ($csv as $lineNumber => $row) {
                if (!$this->isUsableRow($row)) {
                    continue;
                }

                $record = $this->combineRow($header, $row);

                if ($this->isRepeatedHeader($record)) {
                    $skipped++;
                    continue;
                }

                try {
                    $result = DB::transaction(fn () => $this->importRow($record));
                    $importKeys[] = $result['key'];

                    if ($result['status'] === 'created') {
                        $imported++;
                    } else {
                        $updated++;
                    }
                } catch (Throwable $exception) {
                    $skipped++;
                    $errors[] = [
                        'row' => $lineNumber + 1,
                        'name' => $record['name'] ?? '',
                        'error' => $exception->getMessage(),
                    ];
                }
            }
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());
            return self::FAILURE;
        }

        if ((bool) $this->option('replace') && $errors === []) {
            $removed = $this->removeSchoolsMissingFromCsv($importKeys);
        }

        $this->newLine();
        $this->info("Imported: {$imported}");
        $this->info("Updated: {$updated}");
        $this->warn("Removed: {$removed}");
        $this->warn("Skipped: {$skipped}");
        $this->error('Errors: ' . count($errors));

        if ($errors !== []) {
            $this->table(['Row', 'Name', 'Error'], array_slice($errors, 0, 20));

            if (count($errors) > 20) {
                $this->warn('Only the first 20 errors are shown.');
            }

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function importRow(array $record): array
    {
        $name = $this->nullableString($record['name'] ?? null);
        $country = $this->nullableString($record['country'] ?? null);
        $city = $this->nullableString($record['city'] ?? null)
            ?? $this->inferCity($record['address'] ?? null, $record['sourceUrl'] ?? null);

        if (!$name || !$country || !$city) {
            throw new \RuntimeException('name, country, and city are required for matching.');
        }

        if (strlen($country) !== 2) {
            throw new \RuntimeException('country must be a 2-letter country code.');
        }

        $attributes = [
            'name' => $name,
            'city' => $city,
            'country' => strtoupper($country),
        ];

        $values = [
            'address' => $this->nullableString($record['address'] ?? null),
            'websiteUrl' => $this->nullableString($record['websiteUrl'] ?? null),
            'description' => $this->nullableString($record['description'] ?? null),
            'feesMin' => $this->nullableInteger($record['feesMin'] ?? null),
            'feesMax' => $this->nullableInteger($record['feesMax'] ?? null),
            'currency' => $this->nullableCurrency($record['currency'] ?? null) ?? 'SAR',
            'feePeriod' => $this->nullableFeePeriod($record['feePeriod'] ?? null) ?? 'yearly',
        ];

        $school = School::withTrashed()->where($attributes)->first();
        $created = !$school;

        if ($school && $school->trashed()) {
            $school->restore();
        }

        $school = School::updateOrCreate($attributes, $values);

        $school->curricula()->sync($this->taxonomyIds($record['curricula'] ?? '', Curriculum::class));
        $school->activities()->sync($this->taxonomyIds($record['activities'] ?? '', Activity::class));
        $school->languages()->sync($this->taxonomyIds($record['languages'] ?? '', Language::class));

        return [
            'status' => $created ? 'created' : 'updated',
            'key' => $this->schoolKey($attributes['name'], $attributes['city'], $attributes['country']),
        ];
    }

    private function removeSchoolsMissingFromCsv(array $importKeys): int
    {
        $importKeys = array_flip(array_unique($importKeys));
        $removed = 0;

        foreach (School::query()
            ->select(['id', 'name', 'city', 'country'])
            ->cursor() as $school) {
            $key = $this->schoolKey($school->name, $school->city, $school->country);

            if (!isset($importKeys[$key])) {
                $school->delete();
                $removed++;
            }
        }

        return $removed;
    }

    private function taxonomyIds(?string $value, string $modelClass): array
    {
        return collect($this->splitPipeValues($value))
            ->map(fn (string $name) => $this->firstOrCreateTaxonomy($modelClass, $name)->getKey())
            ->all();
    }

    private function firstOrCreateTaxonomy(string $modelClass, string $name): Model
    {
        /** @var class-string<Model> $modelClass */
        $existing = $modelClass::query()
            ->whereRaw('LOWER(name) = ?', [Str::lower($name)])
            ->first();

        if ($existing) {
            return $existing;
        }

        return $modelClass::create([
            'name' => $name,
            'slug' => $this->uniqueSlug($modelClass, $name),
        ]);
    }

    private function uniqueSlug(string $modelClass, string $name): string
    {
        $baseSlug = Str::slug($name) ?: (string) Str::uuid();
        $slug = $baseSlug;
        $suffix = 2;

        while ($modelClass::query()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function splitPipeValues(?string $value): array
    {
        if (!$value) {
            return [];
        }

        return collect(explode('|', $value))
            ->map(fn (string $item) => trim($item))
            ->filter()
            ->unique(fn (string $item) => Str::lower($item))
            ->values()
            ->all();
    }

    private function schoolKey(string $name, string $city, string $country): string
    {
        return Str::lower(trim($name)) . '|' . Str::lower(trim($city)) . '|' . Str::upper(trim($country));
    }

    private function readHeader(SplFileObject $csv): array
    {
        foreach ($csv as $row) {
            if (!$this->isUsableRow($row)) {
                continue;
            }

            return array_map(
                fn ($column) => trim((string) $column, " \t\n\r\0\x0B\xEF\xBB\xBF"),
                $row
            );
        }

        throw new \RuntimeException('CSV file is empty.');
    }

    private function combineRow(array $header, array $row): array
    {
        $row = array_pad($row, count($header), null);
        $row = array_slice($row, 0, count($header));

        return array_combine($header, $row) ?: [];
    }

    private function isUsableRow(mixed $row): bool
    {
        return is_array($row)
            && $row !== [null]
            && collect($row)->contains(fn ($value) => $value !== null && trim((string) $value) !== '');
    }

    private function isRepeatedHeader(array $record): bool
    {
        return ($record['name'] ?? null) === 'name'
            && ($record['country'] ?? null) === 'country'
            && ($record['city'] ?? null) === 'city';
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim($this->sanitizeString($value));

        return $value === '' ? null : $value;
    }

    private function sanitizeString(mixed $value): string
    {
        $value = (string) $value;

        if ($value === '') {
            return '';
        }

        if (function_exists('mb_check_encoding') && !mb_check_encoding($value, 'UTF-8')) {
            $value = mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
        }

        $cleaned = @preg_replace('/[^\P{C}\t\r\n]+/u', ' ', $value);

        return $cleaned ?? $value;
    }

    private function inferCity(mixed $address, mixed $sourceUrl): ?string
    {
        $address = $this->nullableString($address);

        if ($address && preg_match('/-\s*([^,]+)\s*,\s*Saudi Arabia/i', $address, $matches)) {
            return trim($matches[1]);
        }

        $sourceUrl = $this->nullableString($sourceUrl);

        if ($sourceUrl && preg_match('/-([a-z]+)-saudi-arabia\/?$/i', $sourceUrl, $matches)) {
            return Str::headline($matches[1]);
        }

        return null;
    }

    private function nullableInteger(mixed $value): ?int
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $normalized = str_replace(',', '', $value);

        if (!is_numeric($normalized) || (int) $normalized < 0) {
            throw new \RuntimeException("Invalid unsigned integer value: {$value}");
        }

        return (int) $normalized;
    }

    private function nullableCurrency(mixed $value): ?string
    {
        $value = $this->nullableString($value);

        if ($value === null) {
            return null;
        }

        $value = strtoupper($value);

        if (!preg_match('/^[A-Z]{3}$/', $value)) {
            throw new \RuntimeException("Invalid currency code: {$value}");
        }

        return $value;
    }

    private function nullableFeePeriod(mixed $value): ?string
    {
        $value = $this->nullableString($value);

        if ($value === null) {
            return null;
        }

        if (!in_array($value, ['yearly', 'semester'], true)) {
            throw new \RuntimeException("Invalid feePeriod: {$value}");
        }

        return $value;
    }

    private function resolveFilePath(string $file): ?string
    {
        $file = trim($file);

        if ($file === '') {
            return null;
        }

        if (preg_match('/^[A-Za-z]:[\\\\\\/]/', $file) || str_starts_with($file, DIRECTORY_SEPARATOR)) {
            return $file;
        }

        return base_path($file);
    }
}
