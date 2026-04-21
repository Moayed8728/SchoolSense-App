<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Services\SchoolContactScraperService;
use Illuminate\Console\Command;

class FetchSchoolContacts extends Command
{
    protected $signature = 'schools:fetch-contacts 
                            {schoolId? : Optional school UUID}
                            {--limit=10 : Max schools when no schoolId is given}';

    protected $description = 'Fetch and enrich school contact info from official website URLs';

    public function handle(SchoolContactScraperService $scraper): int
    {
        $schoolId = $this->argument('schoolId');

        if ($schoolId) {
            $school = School::find($schoolId);

            if (!$school) {
                $this->error('School not found.');
                return self::FAILURE;
            }

            $this->runForSchool($scraper, $school);

            return self::SUCCESS;
        }

        $limit = (int) $this->option('limit');

        $schools = School::query()
            ->whereNotNull('websiteUrl')
            ->where(function ($query) {
                $query->whereNull('contactEmail')
                    ->orWhereNull('contactPhone')
                    ->orWhereNull('contactPageUrl');
            })
            ->limit($limit)
            ->get();

        if ($schools->isEmpty()) {
            $this->info('No schools found that need contact enrichment.');
            return self::SUCCESS;
        }

        foreach ($schools as $school) {
            $this->runForSchool($scraper, $school);
        }

        return self::SUCCESS;
    }

    private function runForSchool(SchoolContactScraperService $scraper, School $school): void
    { //print summary
        $this->line('');
        $this->info("Scraping: {$school->name} ({$school->id})");

        $result = $scraper->scrapeSchool($school);

        if (($result['status'] ?? null) !== 'ok') {
            $this->warn($result['message'] ?? 'Skipped.');
            return;
        }

        $this->line('Visited pages: ' . count($result['visited'] ?? []));

        $this->line(
            'Emails found: ' . (!empty($result['emails']) ? implode(', ', $result['emails']) : 'email not found')
        );

        $this->line(
            'Phones found: ' . (!empty($result['phones']) ? implode(', ', $result['phones']) : 'phone number not found')
        );

        $selectedUpdates = $this->chooseContactValues($school, $result);

        if (!empty($result['contactPageUrl'])) {
            $this->line('Contact page: ' . $result['contactPageUrl']);
        }

        if (!empty($selectedUpdates)) {
            $school->update($selectedUpdates);
            $result['schoolUpdated'] = array_merge($result['schoolUpdated'] ?? [], $selectedUpdates);
            $this->info('Admin selected: ' . implode(', ', array_keys($selectedUpdates)));
        }

        if (!empty($result['schoolUpdated'])) {
            $this->info('School updated: ' . implode(', ', array_keys($result['schoolUpdated'])));
        } else {
            $this->comment('No school fields updated.');
        }
    }

    private function chooseContactValues(School $school, array $result): array
    {
        if (!$this->input->isInteractive()) {
            return [];
        }

        $updates = [];

        $email = $this->chooseContactValue('email', $result['emails'] ?? [], $school->contactEmail);

        if ($email) {
            $updates['contactEmail'] = $email;
        }

        $phone = $this->chooseContactValue('phone number', $result['phones'] ?? [], $school->contactPhone);

        if ($phone) {
            $updates['contactPhone'] = $phone;
        }

        return $updates;
    }

    private function chooseContactValue(string $label, array $values, ?string $currentValue): ?string
    {
        $values = collect($values)
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (count($values) <= 1) {
            return null;
        }

        $keepCurrent = $currentValue
            ? "Keep current ({$currentValue})"
            : 'Keep scraper choice';

        $choices = array_merge($values, [$keepCurrent]);
        $selected = $this->choice("Choose {$label} to store in database", $choices, 0);

        return $selected === $keepCurrent ? null : $selected;
    }
}
