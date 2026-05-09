<?php

/*SchoolDocumentBuilderService
= prepares the school text

GeminiEmbeddingService
= sends text to Gemini and gets vector*/

namespace App\Services;

use App\Models\School;

class SchoolDocumentBuilderService
{
    public function build(School $school): string
    {
        $school->loadMissing([
            'curricula',
            'activities',
            'languages',
            'feeBands',
        ]);

        $curricula = $school->curricula->pluck('name')->implode(', ') ?: 'Not specified';
        $activities = $school->activities->pluck('name')->implode(', ') ?: 'Not specified';
        $languages = $school->languages->pluck('name')->implode(', ') ?: 'Not specified';

        $feeRange = $this->feeRange($school);

        return trim(implode("\n", [
            "School Name: {$school->name}",
            "Country: {$school->country}",
            "City: {$school->city}",
            "Address: " . ($school->address ?: 'Not specified'),
            "Website: " . ($school->websiteUrl ?: 'Not specified'),
            "Contact Email: " . ($school->contactEmail ?: 'Not specified'),
            "Contact Phone: " . ($school->contactPhone ?: 'Not specified'),
            "Description: " . ($school->description ?: 'Not specified'),
            "Curricula: {$curricula}",
            "Activities: {$activities}",
            "Languages: {$languages}",
            "Fees: {$feeRange}",
        ]));
    }

    public function hash(string $content): string
    {
        return hash('sha256', $content);
    }

    private function feeRange(School $school): string
    {
        if ($school->feesMin && $school->feesMax) {
            return "{$school->feesMin} - {$school->feesMax} {$school->currency} per {$school->feePeriod}";
        }

        if ($school->feesMin) {
            return "From {$school->feesMin} {$school->currency} per {$school->feePeriod}";
        }

        if ($school->feesMax) {
            return "Up to {$school->feesMax} {$school->currency} per {$school->feePeriod}";
        }

        return 'Not specified';
    }
}
