<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolContactExtraction;
use App\Models\SchoolManagerApplication;
use App\Services\SchoolContactScraperService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

class SchoolContactController extends Controller
{
    public function index()
    {
        $schools = School::query()
            ->latest()
            ->paginate(12);

        return view('admin.school-verification.index', [
            'schools' => $schools,
        ]);
    }

    public function show(School $school)
    {
        return view('admin.school-verification.show', [
            'school' => $school,
            'pendingContactReview' => session($this->schoolReviewSessionKey($school)),
            'latestContactExtraction' => $school->contactExtractions()->latest()->first(),
        ]);
    }

    public function fetch(School $school, SchoolContactScraperService $scraper): RedirectResponse
    {
        $result = $scraper->scrapeSchool($school);

        if (($result['status'] ?? null) !== 'ok') {
            return redirect()
                ->route('admin.school-verification.show', $school)
                ->with('contact_fetch_error', $result['message'] ?? 'Contact scraping failed.');
        }

        $review = $this->buildReviewPayload(
            modelId: $school->id,
            currentValues: [
                'contactEmail' => $school->contactEmail,
                'contactPhone' => $school->contactPhone,
                'contactPageUrl' => $school->contactPageUrl,
            ],
            proposedValues: [
                'contactEmail' => Arr::get($result, 'emails.0'),
                'contactPhone' => Arr::get($result, 'phones.0'),
                'contactPageUrl' => $result['contactPageUrl'] ?? null,
            ],
            result: $result
        );

        session()->put($this->schoolReviewSessionKey($school), $review);

        return redirect()
            ->route('admin.school-verification.show', $school)
            ->with('contact_fetch_result', $this->buildReviewMessage($review, 'school'));
    }

    public function apply(School $school): RedirectResponse
    {
        $review = session()->get($this->schoolReviewSessionKey($school));

        if (!$review) {
            return back()->with('contact_fetch_error', 'No fetched contact review is waiting for approval.');
        }

        $updates = $this->extractApprovedUpdates($review);

        if (!empty($updates)) {
            $school->update($updates);
        }

        $this->markExtractionApproved($review);
        session()->forget($this->schoolReviewSessionKey($school));

        $message = !empty($updates)
            ? 'Approved contact updates saved: ' . implode(', ', array_keys($updates)) . '.'
            : 'No differences were found, so the school record was left unchanged.';

        return redirect()
            ->route('admin.school-verification.show', $school)
            ->with('contact_fetch_result', $message);
    }

    public function cancel(School $school): RedirectResponse
    {
        session()->forget($this->schoolReviewSessionKey($school));

        return redirect()
            ->route('admin.school-verification.show', $school)
            ->with('contact_fetch_result', 'Fetched contact review discarded. The school record was not changed.');
    }

    public function fetchApplication(SchoolManagerApplication $schoolManagerApplication, SchoolContactScraperService $scraper): RedirectResponse
    {
        if (blank($schoolManagerApplication->websiteUrl)) {
            return back()->with('application_contact_fetch_error', 'Application has no website URL.');
        }

        $result = $scraper->scrapeWebsite($schoolManagerApplication->websiteUrl);

        if (($result['status'] ?? null) !== 'ok') {
            return back()->with('application_contact_fetch_error', $result['message'] ?? 'Contact scraping failed.');
        }

        $review = $this->buildReviewPayload(
            modelId: $schoolManagerApplication->id,
            currentValues: [
                'contactEmail' => $schoolManagerApplication->contactEmail,
                'contactPhone' => $schoolManagerApplication->contactPhone,
                'contactPageUrl' => $schoolManagerApplication->contactPageUrl,
            ],
            proposedValues: [
                'contactEmail' => Arr::get($result, 'emails.0'),
                'contactPhone' => Arr::get($result, 'phones.0'),
                'contactPageUrl' => $result['contactPageUrl'] ?? null,
            ],
            result: $result
        );

        session()->put($this->applicationReviewSessionKey($schoolManagerApplication), $review);

        return back()->with('application_contact_fetch_result', $this->buildReviewMessage($review, 'application'));
    }

    public function applyApplication(SchoolManagerApplication $schoolManagerApplication): RedirectResponse
    {
        $review = session()->get($this->applicationReviewSessionKey($schoolManagerApplication));

        if (!$review) {
            return back()->with('application_contact_fetch_error', 'No fetched contact review is waiting for approval.');
        }

        $updates = $this->extractApprovedUpdates($review);

        if (!empty($updates)) {
            $schoolManagerApplication->update($updates);
        }

        session()->forget($this->applicationReviewSessionKey($schoolManagerApplication));

        $message = !empty($updates)
            ? 'Approved application contact updates saved: ' . implode(', ', array_keys($updates)) . '.'
            : 'No differences were found, so the submitted contact details were left unchanged.';

        return back()->with('application_contact_fetch_result', $message);
    }

    public function cancelApplication(SchoolManagerApplication $schoolManagerApplication): RedirectResponse
    {
        session()->forget($this->applicationReviewSessionKey($schoolManagerApplication));

        return back()->with('application_contact_fetch_result', 'Fetched contact review discarded. The submitted application details were not changed.');
    }

    private function buildReviewPayload(string $modelId, array $currentValues, array $proposedValues, array $result): array
    {
        $differences = [];

        foreach ($this->fieldLabels() as $field => $label) {
            $current = $this->normalizeValue($currentValues[$field] ?? null);
            $proposed = $this->normalizeValue($proposedValues[$field] ?? null);

            $differences[$field] = [
                'label' => $label,
                'current' => $current,
                'proposed' => $proposed,
                'isDifferent' => $current !== $proposed,
            ];
        }

        return [
            'modelId' => $modelId,
            'differences' => $differences,
            'diffCount' => collect($differences)->where('isDifferent', true)->count(),
            'emails' => $result['emails'] ?? [],
            'phones' => $result['phones'] ?? [],
            'contactPageUrl' => $result['contactPageUrl'] ?? null,
            'visited' => $result['visited'] ?? [],
            'extractionId' => $result['extractionId'] ?? null,
            'fetchedAt' => now()->toIso8601String(),
        ];
    }

    private function buildReviewMessage(array $review, string $subject): string
    {
        $lines = [
            'Contact scraping completed. No data was updated yet.',
            $review['diffCount'] > 0
                ? ucfirst($subject) . ' review found ' . $review['diffCount'] . ' difference(s).'
                : 'No differences were found between the fetched values and the current record.',
            'Emails: ' . (!empty($review['emails']) ? implode(', ', $review['emails']) : 'email not found'),
            'Phones: ' . (!empty($review['phones']) ? implode(', ', $review['phones']) : 'phone number not found'),
            'Contact page: ' . ($review['contactPageUrl'] ?: 'contact page not found'),
            'Review the differences below, then choose to apply or cancel.',
        ];

        return implode("\n", $lines);
    }

    private function extractApprovedUpdates(array $review): array
    {
        return collect($review['differences'] ?? [])
            ->filter(fn (array $difference) => ($difference['isDifferent'] ?? false) && filled($difference['proposed'] ?? null))
            ->mapWithKeys(fn (array $difference, string $field) => [$field => $difference['proposed']])
            ->all();
    }

    private function markExtractionApproved(array $review): void
    {
        if (blank($review['extractionId'] ?? null) || !auth()->check()) {
            return;
        }

        SchoolContactExtraction::query()
            ->whereKey($review['extractionId'])
            ->update([
                'approvedBy' => auth()->id(),
                'approvedAt' => now(),
            ]);
    }

    private function normalizeValue(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    private function fieldLabels(): array
    {
        return [
            'contactEmail' => 'Contact email',
            'contactPhone' => 'Contact phone',
            'contactPageUrl' => 'Contact page URL',
        ];
    }

    private function schoolReviewSessionKey(School $school): string
    {
        return 'admin.school-contact-review.' . $school->id;
    }

    private function applicationReviewSessionKey(SchoolManagerApplication $schoolManagerApplication): string
    {
        return 'admin.application-contact-review.' . $schoolManagerApplication->id;
    }
}
