<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolManagerApplication;
use App\Services\SchoolContactScraperService;
use Illuminate\Http\RedirectResponse;

class SchoolContactController extends Controller
{
    public function fetch(School $school, SchoolContactScraperService $scraper): RedirectResponse
    {
        $result = $scraper->scrapeSchool($school);

        if (($result['status'] ?? null) !== 'ok') {
            return back()->with('contact_fetch_error', $result['message'] ?? 'Contact scraping failed.');
        }

        $message = implode("\n", [
            'Contact scraping completed.',
            'Emails: ' . (!empty($result['emails']) ? implode(', ', $result['emails']) : 'email not found'),
            'Phones: ' . (!empty($result['phones']) ? implode(', ', $result['phones']) : 'phone number not found'),
            'Contact page: ' . ($result['contactPageUrl'] ?: 'contact page not found'),
        ]);

        return back()->with('contact_fetch_result', $message);
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

        $updates = [];

        if (blank($schoolManagerApplication->contactEmail) && !empty($result['emails'])) {
            $updates['contactEmail'] = $result['emails'][0];
        }

        if (!empty($result['phones'])) {
            $updates['contactPhone'] = $result['phones'][0];
        }

        if (blank($schoolManagerApplication->contactPageUrl) && filled($result['contactPageUrl'])) {
            $updates['contactPageUrl'] = $result['contactPageUrl'];
        }

        if (!empty($updates)) {
            $schoolManagerApplication->update($updates);
        }

        $message = implode("\n", [
            'Application contact scraping completed.',
            'Emails: ' . (!empty($result['emails']) ? implode(', ', $result['emails']) : 'email not found'),
            'Phones: ' . (!empty($result['phones']) ? implode(', ', $result['phones']) : 'phone number not found'),
            'Contact page: ' . ($result['contactPageUrl'] ?: 'contact page not found'),
        ]);

        return back()->with('application_contact_fetch_result', $message);
    }
}
