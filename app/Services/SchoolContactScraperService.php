<?php

namespace App\Services;

use App\Models\School;
use App\Models\SchoolContactExtraction;
use App\Models\SchoolContactSource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class SchoolContactScraperService
{
    public function scrapeWebsite(string $websiteUrl): array
    {
        $baseUrl = $this->normalizeUrl($websiteUrl);

        if (!$baseUrl) {
            return [
                'status' => 'failed',
                'message' => 'Invalid websiteUrl.',
            ];
        }

        $pagesToVisit = collect([$baseUrl])
            ->merge($this->buildLikelyUrls($baseUrl))
            ->unique()
            ->values();

        $visited = [];
        $emails = [];
        $phones = [];
        $contactPageUrl = null;

        for ($i = 0; $i < $pagesToVisit->count() && $i < 3; $i++) {
            $url = $pagesToVisit[$i];

            try {
                $response = Http::timeout(15)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (compatible; SchoolSenseBot/1.0; +https://schoolsense.local)',
                        'Accept-Language' => 'en-US,en;q=0.9',
                    ])
                    ->get($url);

                $visited[] = [
                    'url' => $url,
                    'status' => $response->status(),
                ];

                if (!$response->successful()) {
                    continue;
                }

                $html = $response->body();

                $emails = array_merge($emails, $this->extractEmails($html));
                $phones = array_merge($phones, $this->extractPhones($html, $url, $i === 0));

                $foundLinks = $this->extractLikelyContactLinks($html, $url);

                if (!$contactPageUrl) {
                    if ($this->looksLikeContactPage($url)) {
                        $contactPageUrl = $url;
                    } elseif (!empty($foundLinks)) {
                        $contactPageUrl = $foundLinks[0];
                    }
                }

                if ($i === 0 && !empty($foundLinks)) {
                    $pagesToVisit = $pagesToVisit
                        ->merge($foundLinks)
                        ->unique()
                        ->values();
                }
            } catch (\Throwable $e) {
                $visited[] = [
                    'url' => $url,
                    'status' => 'exception',
                    'error' => Str::limit($e->getMessage(), 200),
                ];
            }
        }

        return [
            'status' => 'ok',
            'visited' => $visited,
            'emails' => $this->uniqueEmails($emails),
            'phones' => $this->uniquePhones($phones),
            'contactPageUrl' => $contactPageUrl,
        ];
    }

    public function scrapeSchool(School $school, bool $force = false): array
    {
        if (blank($school->websiteUrl)) {
            return [
                'status' => 'skipped',
                'message' => 'School has no websiteUrl.',
                'schoolId' => $school->id,
            ];
        }

        $baseUrl = $this->normalizeUrl($school->websiteUrl); // https://edu/,,,,,.com

        if (!$baseUrl) {
            return [
                'status' => 'failed',
                'message' => 'Invalid websiteUrl.',
                'schoolId' => $school->id,
            ];
        }

        $pagesToVisit = collect([$baseUrl])
            ->merge($this->buildLikelyUrls($baseUrl))
            ->unique()
            ->values();

        $visited = [];
        $emails = [];
        $phones = [];
        $contactPageUrl = null;
        $homepageSource = null;

        for ($i = 0; $i < $pagesToVisit->count() && $i < 3; $i++) { // 3 is fast
            $url = $pagesToVisit[$i];

            $source = SchoolContactSource::firstOrCreate(
                [
                    'schoolId' => $school->id,
                    'sourceUrl' => $url,
                ],
                [
                    'domain' => $this->extractDomain($url),
                    'status' => 'pending',
                    'pagesScraped' => 0,
                ]
            );

            if ($i === 0) {
                $homepageSource = $source;
            }

            try { //http request
                $response = Http::timeout(15)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (compatible; SchoolSenseBot/1.0; +https://schoolsense.local)', // Helps avoid basic bot blocking
                        'Accept-Language' => 'en-US,en;q=0.9',
                    ])
                    ->get($url);

                $status = $response->status();

                $source->update([
                    'status' => $this->mapSourceStatus($status),
                    'lastScrapedAt' => now(),
                    'lastError' => $response->successful() ? null : ('HTTP ' . $status),
                    'pagesScraped' => (int) $source->pagesScraped + 1,
                ]);

                $visited[] = [
                    'url' => $url,
                    'status' => $status,
                ];

                if (!$response->successful()) {
                    continue;
                }

                $html = $response->body();

                $emails = array_merge($emails, $this->extractEmails($html));
                $phones = array_merge($phones, $this->extractPhones($html, $url, $i === 0));

                $foundLinks = $this->extractLikelyContactLinks($html, $url);

                if (!$contactPageUrl) {
                    if ($this->looksLikeContactPage($url)) {
                        $contactPageUrl = $url;
                    } elseif (!empty($foundLinks)) {
                        $contactPageUrl = $foundLinks[0];
                    }
                }

                if ($i === 0 && !empty($foundLinks)) {
                    $pagesToVisit = $pagesToVisit
                        ->merge($foundLinks)
                        ->unique()
                        ->values();
                }
            } catch (\Throwable $e) {
                $source->update([
                    'status' => 'failed',
                    'lastScrapedAt' => now(),
                    'lastError' => Str::limit($e->getMessage(), 500),
                    'pagesScraped' => (int) $source->pagesScraped + 1,
                ]);

                $visited[] = [
                    'url' => $url,
                    'status' => 'exception',
                    'error' => Str::limit($e->getMessage(), 200),
                ];
            }
        }

        $emails = $this->uniqueEmails($emails, $school);
        $phones = $this->uniquePhones($phones);

        $extraction = SchoolContactExtraction::create([
            'schoolId' => $school->id,
            'sourceId' => $homepageSource?->id,
            'foundEmails' => $emails ?: null,
            'foundPhones' => $phones ?: null,
            'foundAddresses' => null,
            'foundSocialLinks' => null,
            'pages' => $visited ?: null,
            'approvedBy' => null,
            'approvedAt' => null,
        ]);

        $updates = [];

        if (blank($school->contactEmail) && !empty($emails)) {
            $updates['contactEmail'] = $emails[0];
        }

        if (!empty($phones)) {
            $updates['contactPhone'] = $phones[0];
        }

        if (blank($school->contactPageUrl) && filled($contactPageUrl)) {
            $updates['contactPageUrl'] = $contactPageUrl;
        }

        if (!empty($updates)) {
            $school->update($updates);
        }

        return [
            'status' => 'ok',
            'schoolId' => $school->id,
            'schoolName' => $school->name,
            'visited' => $visited,
            'emails' => $emails,
            'phones' => $phones,
            'contactPageUrl' => $contactPageUrl,
            'schoolUpdated' => $updates,
            'extractionId' => $extraction->id,
        ];
    }

    private function buildLikelyUrls(string $baseUrl): array
    {
        $baseUrl = rtrim($baseUrl, '/');

        return [
            $baseUrl . '/contact',
            $baseUrl . '/contact-us',
            $baseUrl . '/contactus',
            $baseUrl . '/about',
            $baseUrl . '/about-us',
            $baseUrl . '/admissions',
        ];
    }

    private function extractLikelyContactLinks(string $html, string $currentUrl): array
    {
        $crawler = new Crawler($html, $currentUrl);
        $links = [];

        try {
            $crawler->filter('a[href]')->each(function (Crawler $node) use (&$links, $currentUrl) {
                $href = trim((string) $node->attr('href'));
                $text = Str::lower(trim($node->text('')));

                if ($href === '' || Str::startsWith($href, ['mailto:', 'tel:', '#', 'javascript:'])) {
                    return;
                }

                $absolute = $this->makeAbsoluteUrl($href, $currentUrl);

                if (!$absolute) {
                    return;
                }

                $haystack = Str::lower($absolute . ' ' . $text);

                if (
                    Str::contains($haystack, 'contact') ||
                    Str::contains($haystack, 'about') ||
                    Str::contains($haystack, 'admission')
                ) {
                    $links[] = $absolute;
                }
            });
        } catch (\Throwable $e) {
            return [];
        }

        return collect($links)
            ->filter(fn ($url) => filled($url))
            ->unique()
            ->take(3)
            ->values()
            ->all();
    }

    private function extractEmails(string $html): array
    {
        preg_match_all(
            '/[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}/',
            $html,
            $matches
        );

        return $matches[0] ?? [];
    }

    private function extractPhones(string $html, string $url, bool $isHomepage): array
    {
        $candidates = [];
        $isContactPage = $this->looksLikeContactPage($url);

        foreach ($this->extractTelLinkPhones($html) as $phone) {
            $candidates[] = $this->phoneCandidate($phone, 400, 'tel');
        }

        $text = $this->visibleText($html);

        if ($text === '') {
            return $candidates;
        }

        $keywordScore = $isContactPage ? 300 : ($isHomepage ? 220 : 180);

        foreach ($this->keywordPhoneLines($text) as $line) {
            foreach ($this->matchPhoneLikeText($line) as $phone) {
                $candidates[] = $this->phoneCandidate($phone, $keywordScore, 'keyword-text');
            }
        }

        foreach ($this->matchPhoneLikeText($text) as $phone) {
            $candidates[] = $this->phoneCandidate($phone, 50, 'generic-text');
        }

        return array_values(array_filter($candidates));
    }

    private function extractTelLinkPhones(string $html): array
    {
        $crawler = new Crawler($html);
        $phones = [];

        try {
            $crawler->filter('a[href]')->each(function (Crawler $node) use (&$phones) {
                $href = trim((string) $node->attr('href'));

                if (!Str::startsWith(Str::lower($href), 'tel:')) {
                    return;
                }

                $phones[] = preg_replace('/^tel:/i', '', $href);
            });
        } catch (\Throwable $e) {
            return [];
        }

        return $phones;
    }

    private function visibleText(string $html): string
    {
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', ' ', $html) ?? $html;
        $html = preg_replace('/<style\b[^>]*>.*?<\/style>/is', ' ', $html) ?? $html;
        $html = preg_replace('/<br\s*\/?>/i', "\n", $html) ?? $html;
        $html = preg_replace('/<\/(p|div|li|tr|td|section|article|header|footer|span)>/i', "\n", $html) ?? $html;

        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/[ \t]+/", ' ', $text) ?? $text;
        $text = preg_replace("/\n{2,}/", "\n", $text) ?? $text;

        return trim($text);
    }

    private function keywordPhoneLines(string $text): array
    {
        $keywords = ['phone', 'tel', 'telephone', 'mobile', 'call', 'whatsapp', 'contact'];
        $lines = preg_split('/\R+/', $text) ?: [];
        $matches = [];

        foreach ($lines as $index => $line) {
            $line = trim($line);

            if ($line === '' || !Str::contains(Str::lower($line), $keywords)) {
                continue;
            }

            $matches[] = trim(implode(' ', array_filter([
                $lines[$index - 1] ?? null,
                $line,
                $lines[$index + 1] ?? null,
            ])));
        }

        return collect($matches)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function matchPhoneLikeText(string $text): array
    {
        $patterns = [
            '/(?<!\d)(?:\+?\s*\(?966\)?|00966)?[\s\-().]*(?:\(?0?5\d\)?)[\s\-().]*\d{3}[\s\-().]*\d{4}(?!\d)/',
            '/(?<!\d)(?:\+?\s*\(?966\)?|00966)?[\s\-().]*(?:\(?0?1\d\)?)[\s\-().]*\d{3}[\s\-().]*\d{4}(?!\d)/',
            '/(?<!\d)9200[\s\-().]*\d{5}(?!\d)/',
        ];

        $phones = [];

        foreach ($patterns as $pattern) {
            preg_match_all($pattern, $text, $matches);
            $phones = array_merge($phones, $matches[0] ?? []);
        }

        return $phones;
    }

    private function phoneCandidate(string $phone, int $score, string $source): ?array
    {
        $normalized = $this->normalizePhone($phone);

        if (!$normalized) {
            return null;
        }

        return [
            'phone' => $normalized,
            'score' => $score,
            'source' => $source,
        ];
    }

    private function normalizePhone(string $phone): ?string
    {
        $phone = preg_replace('/(?:ext|extension|x)\.?\s*\d+$/i', '', trim($phone)) ?? $phone;
        $digits = preg_replace('/\D+/', '', $phone);

        if (!$digits) {
            return null;
        }

        if (Str::startsWith($digits, '00966')) {
            $digits = '966' . substr($digits, 5);
        }

        if (preg_match('/^9200\d{5}$/', $digits)) {
            return substr($digits, 0, 4) . ' ' . substr($digits, 4);
        }

        if (preg_match('/^05\d{8}$/', $digits)) {
            return $this->formatSaudiPhone(substr($digits, 1));
        }

        if (preg_match('/^01\d{8}$/', $digits)) {
            return $this->formatSaudiPhone(substr($digits, 1));
        }

        if (preg_match('/^966(5\d{8})$/', $digits, $matches)) {
            return $this->formatSaudiPhone($matches[1]);
        }

        if (preg_match('/^966(1\d{8})$/', $digits, $matches)) {
            return $this->formatSaudiPhone($matches[1]);
        }

        if (preg_match('/^9660(5\d{8})$/', $digits, $matches)) {
            return $this->formatSaudiPhone($matches[1]);
        }

        if (preg_match('/^9660(1\d{8})$/', $digits, $matches)) {
            return $this->formatSaudiPhone($matches[1]);
        }

        return null;
    }

    private function formatSaudiPhone(string $nationalNumber): string
    {
        return '+966 ' . substr($nationalNumber, 0, 2) . ' ' . substr($nationalNumber, 2, 3) . ' ' . substr($nationalNumber, 5);
    }

    private function uniqueEmails(array $emails, ?School $school = null): array
    {
        $blockedDomains = [
            'kingsteruni.edu',
            'example.com',
            'test.com',
            'domain.com',
        ];

        $schoolKeywords = collect([
            $school?->name,
            $school?->websiteUrl,
        ])
            ->filter()
            ->flatMap(function ($value) {
                $value = strtolower((string) $value);

                return preg_split('/[^a-z0-9]+/', $value) ?: [];
            })
            ->filter(fn ($part) => strlen($part) >= 4)
            ->unique()
            ->values()
            ->all();

        return collect($emails)
            ->map(fn ($email) => strtolower(trim($email)))
            ->filter()
            ->filter(function ($email) use ($blockedDomains) {
                foreach ($blockedDomains as $blocked) {
                    if (str_ends_with($email, '@' . $blocked)) {
                        return false;
                    }
                }

                return true;
            })
            ->unique()
            ->sortByDesc(function ($email) use ($schoolKeywords) {
                foreach ($schoolKeywords as $keyword) {
                    if (str_contains($email, $keyword)) {
                        return 2;
                    }
                }

                if (
                    str_starts_with($email, 'info@') ||
                    str_starts_with($email, 'contact@') ||
                    str_starts_with($email, 'admission@') ||
                    str_starts_with($email, 'admissions@') ||
                    str_starts_with($email, 'admin@') ||
                    str_starts_with($email, 'principal@')
                ) {
                    return 1;
                }

                return 0;
            })
            ->values()
            ->all();
    }

    private function uniquePhones(array $phones): array
    {
        return collect($phones)
            ->map(function ($phone) {
                if (is_array($phone)) {
                    return $phone;
                }

                $normalized = $this->normalizePhone((string) $phone);

                return $normalized ? [
                    'phone' => $normalized,
                    'score' => 0,
                    'source' => 'legacy',
                ] : null;
            })
            ->filter(fn ($phone) => is_array($phone) && filled($phone['phone'] ?? null))
            ->groupBy('phone')
            ->map(fn ($group) => $group->sortByDesc('score')->first())
            ->sortByDesc('score')
            ->pluck('phone')
            ->values()
            ->all();
    }

    private function normalizeUrl(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return null;
        }

        if (!Str::startsWith($url, ['http://', 'https://'])) {
            $url = 'https://' . $url;
        }

        return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }

    private function extractDomain(string $url): ?string
    {
        return parse_url($url, PHP_URL_HOST) ?: null;
    }

    private function looksLikeContactPage(string $url): bool
    {
        $url = Str::lower($url);

        return Str::contains($url, ['contact', 'about', 'admission']);
    }

    private function makeAbsoluteUrl(string $href, string $currentUrl): ?string
    {
        if (Str::startsWith($href, ['http://', 'https://'])) {
            return $href;
        }

        $parts = parse_url($currentUrl);

        if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
            return null;
        }

        $base = $parts['scheme'] . '://' . $parts['host'];

        if (Str::startsWith($href, '/')) {
            return $base . $href;
        }

        $path = $parts['path'] ?? '/';
        $dir = rtrim(str_replace('\\', '/', dirname($path)), '/');

        return $base . ($dir ? '/' . ltrim($dir, '/') : '') . '/' . ltrim($href, '/');
    }

    private function mapSourceStatus(int $status): string
    {
        if ($status === 403 || $status === 429) {
            return 'blocked';
        }

        if ($status >= 200 && $status < 300) {
            return 'success';
        }

        return 'failed';
    }
}
